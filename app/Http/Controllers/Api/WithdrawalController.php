<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'withdrawals' => $request->user()->withdrawals()->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:30'],
            'account_name' => ['required', 'string', 'max:150'],
        ]);

        $user = $request->user();
        $totalContributions = $user->contributions()->where('status', 'successful')->sum('amount');
        $approvedWithdrawals = $user->withdrawals()->where('status', 'approved')->sum('amount');
        $pendingWithdrawals = $user->withdrawals()->where('status', 'pending')->sum('amount');
        $availableBalance = $totalContributions - $approvedWithdrawals - $pendingWithdrawals;

        if ((float) $data['amount'] > (float) $availableBalance) {
            return response()->json([
                'message' => 'Withdrawal amount cannot exceed your available balance.',
            ], 422);
        }

        $withdrawal = Withdrawal::create([
            ...$data,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Withdrawal request submitted successfully.',
            'withdrawal' => $withdrawal,
        ], 201);
    }
}
