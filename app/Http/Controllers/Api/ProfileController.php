<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
        ]);

        $request->user()->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $request->user()->fresh(),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $totalContributions = $user->contributions()->where('status', 'successful')->sum('amount');
        $approvedWithdrawals = $user->withdrawals()->where('status', 'approved')->sum('amount');
        $pendingWithdrawals = $user->withdrawals()->where('status', 'pending')->sum('amount');
        $balance = $totalContributions - $approvedWithdrawals;

        return response()->json([
            'balance' => $balance,
            'available_balance' => $balance - $pendingWithdrawals,
            'total_contributions' => $totalContributions,
            'approved_withdrawals' => $approvedWithdrawals,
            'pending_withdrawal_amount' => $pendingWithdrawals,
            'pending_withdrawals' => $user->withdrawals()->where('status', 'pending')->count(),
            'recent_contributions' => $user->contributions()->with('pensionPlan')->latest()->limit(5)->get(),
        ]);
    }
}
