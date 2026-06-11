<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\PensionPlan;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_users' => User::where('role', 'worker')->count(),
            'total_contributions' => Contribution::where('status', 'successful')->sum('amount'),
            'active_plans' => PensionPlan::where('is_active', true)->count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
        ]);
    }

    public function reports(): JsonResponse
    {
        return response()->json([
            'total_pension_balance' => Contribution::where('status', 'successful')->sum('amount') - Withdrawal::where('status', 'approved')->sum('amount'),
            'monthly_contributions' => Contribution::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'active_workers' => User::where('role', 'worker')->count(),
            'most_used_plan' => PensionPlan::withCount('contributions')->orderByDesc('contributions_count')->first(),
        ]);
    }
}
