<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\JsonResponse;

class AdminContributionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'contributions' => Contribution::with(['user', 'pensionPlan'])->latest()->get(),
        ]);
    }
}
