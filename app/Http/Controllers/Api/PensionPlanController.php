<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PensionPlan;
use Illuminate\Http\JsonResponse;

class PensionPlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'plans' => PensionPlan::where('is_active', true)->latest()->get(),
        ]);
    }
}
