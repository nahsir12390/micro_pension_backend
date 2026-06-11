<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PensionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPensionPlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['plans' => PensionPlan::latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $plan = PensionPlan::create($this->validatedData($request));

        return response()->json([
            'message' => 'Pension plan created successfully.',
            'plan' => $plan,
        ], 201);
    }

    public function show(PensionPlan $pensionPlan): JsonResponse
    {
        return response()->json(['plan' => $pensionPlan]);
    }

    public function update(Request $request, PensionPlan $pensionPlan): JsonResponse
    {
        $pensionPlan->update($this->validatedData($request, true));

        return response()->json([
            'message' => 'Pension plan updated successfully.',
            'plan' => $pensionPlan->fresh(),
        ]);
    }

    public function destroy(PensionPlan $pensionPlan): JsonResponse
    {
        $pensionPlan->update(['is_active' => false]);

        return response()->json(['message' => 'Pension plan disabled successfully.']);
    }

    private function validatedData(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'minimum_amount' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'frequency' => [$updating ? 'sometimes' : 'required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
