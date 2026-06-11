<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContributionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'contributions' => $request->user()->contributions()->with('pensionPlan')->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pension_plan_id' => ['nullable', 'exists:pension_plans,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'max:100'],
        ]);

        $contribution = Contribution::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'successful',
            'reference' => 'CON-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
            'contributed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Contribution recorded successfully.',
            'contribution' => $contribution->load('pensionPlan'),
        ], 201);
    }
}
