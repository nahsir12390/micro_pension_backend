<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWithdrawalController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'withdrawals' => Withdrawal::with(['user', 'reviewer'])->latest()->get(),
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending withdrawal requests can be approved.',
            ], 422);
        }

        $withdrawal->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Withdrawal approved successfully.',
            'withdrawal' => $withdrawal->fresh(['user', 'reviewer']),
        ]);
    }

    public function reject(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending withdrawal requests can be rejected.',
            ], 422);
        }

        $withdrawal->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Withdrawal rejected successfully.',
            'withdrawal' => $withdrawal->fresh(['user', 'reviewer']),
        ]);
    }
}
