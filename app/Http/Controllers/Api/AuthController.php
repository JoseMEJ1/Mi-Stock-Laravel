<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['api_token'] = $this->generateToken();

        $user = User::create($data);

        return $this->success([
            'user' => $user,
            'token' => $user->api_token,
        ], 'User registered.', 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        $user->api_token = $this->generateToken();
        $user->save();

        return $this->success([
            'user' => $user,
            'token' => $user->api_token,
        ], 'Login successful.');
    }

    public function logout(Request $request)
    {
        $user = $this->authorize($request);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $user->api_token = null;
        $user->save();

        return $this->success(null, 'Logout successful.');
    }

    public function me(Request $request)
    {
        $user = $this->authorize($request);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        return $this->success($user);
    }
}
