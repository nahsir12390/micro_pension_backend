<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    public function initializePayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pension_plan_id' => ['nullable', 'exists:pension_plans,id'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $secretKey = config('services.paystack.secret_key');
        if (blank($secretKey)) {
            return response()->json([
                'message' => 'Paystack is not configured on the server.',
            ], 500);
        }

        $user = $request->user();
        $reference = 'MP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
        $amountInKobo = (int) round(((float) $data['amount']) * 100);

        $contribution = Contribution::create([
            'user_id' => $user->id,
            'pension_plan_id' => $data['pension_plan_id'] ?? null,
            'amount' => $data['amount'],
            'payment_method' => 'Paystack',
            'status' => 'pending',
            'reference' => $reference,
        ]);

        $payload = [
            'email' => $user->email,
            'amount' => $amountInKobo,
            'currency' => 'NGN',
            'reference' => $reference,
            'metadata' => [
                'contribution_id' => $contribution->id,
                'user_id' => $user->id,
                'pension_plan_id' => $data['pension_plan_id'] ?? null,
            ],
        ];

        if (filled(config('services.paystack.callback_url'))) {
            $payload['callback_url'] = config('services.paystack.callback_url');
        }

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->post('https://api.paystack.co/transaction/initialize', $payload);
        } catch (ConnectionException) {
            $contribution->update(['status' => 'failed']);

            return response()->json([
                'message' => 'Could not connect to Paystack. Please try again.',
            ], 503);
        }

        if (! $response->successful() || ! $response->json('status')) {
            $contribution->update(['status' => 'failed']);

            return response()->json([
                'message' => $response->json('message') ?? 'Could not initialize Paystack payment.',
            ], 422);
        }

        return response()->json([
            'message' => 'Payment initialized successfully.',
            'reference' => $reference,
            'authorization_url' => $response->json('data.authorization_url'),
            'access_code' => $response->json('data.access_code'),
            'contribution' => $contribution->load('pensionPlan'),
        ], 201);
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'exists:contributions,reference'],
        ]);

        $secretKey = config('services.paystack.secret_key');
        if (blank($secretKey)) {
            return response()->json([
                'message' => 'Paystack is not configured on the server.',
            ], 500);
        }

        $contribution = $request->user()
            ->contributions()
            ->where('reference', $data['reference'])
            ->firstOrFail();

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->get("https://api.paystack.co/transaction/verify/{$data['reference']}");
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Could not verify payment with Paystack. Please try again.',
            ], 503);
        }

        if (! $response->successful() || ! $response->json('status')) {
            return response()->json([
                'message' => $response->json('message') ?? 'Could not verify Paystack payment.',
            ], 422);
        }

        $transaction = $response->json('data');
        $paidAmount = ((int) ($transaction['amount'] ?? 0)) / 100;
        $status = $transaction['status'] ?? 'failed';

        if ($status !== 'success') {
            $contribution->update(['status' => $status]);

            return response()->json([
                'message' => 'Payment is not successful yet.',
                'status' => $status,
                'contribution' => $contribution->fresh('pensionPlan'),
            ], 422);
        }

        if (round((float) $contribution->amount, 2) !== round($paidAmount, 2)) {
            $contribution->update(['status' => 'failed']);

            return response()->json([
                'message' => 'Payment amount does not match the contribution amount.',
            ], 422);
        }

        $contribution->update([
            'status' => 'successful',
            'payment_method' => $transaction['channel'] ?? 'Paystack',
            'contributed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payment verified and contribution saved successfully.',
            'contribution' => $contribution->fresh('pensionPlan'),
        ]);
    }
}
