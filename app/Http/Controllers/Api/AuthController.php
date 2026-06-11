<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $data['role'] = 'worker';
        $data['api_token'] = Str::random(80);

        $user = User::create($data);

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $user->api_token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        return $this->attemptLogin($request, 'worker');
    }

    public function adminLogin(Request $request): JsonResponse
    {
        return $this->attemptLogin($request, 'admin');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->forceFill(['api_token' => null])->save();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function attemptLogin(Request $request, string $role): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid login details.'],
            ]);
        }

        if ($user->role !== $role) {
            return response()->json(['message' => 'This account cannot access this area.'], 403);
        }

        $user->forceFill(['api_token' => Str::random(80)])->save();

        return response()->json([
            'message' => 'Login successful.',
            'token' => $user->api_token,
            'user' => $user->fresh(),
        ]);
    }
}
