<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\Http\Resources\ApiLoginResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    public function login(ApiLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::where('email', $request->email)->first();
            $user->tokens()->delete();
            $token = $user->createToken('token')->plainTextToken;
            
            return new ApiLoginResource([
                'token' => $token,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 404);
        }
    }

    public function register(ApiRegisterRequest $request) {
        $user = User::create([
            'name' => $request->name, 
            'email' => $request->email, 
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('token')->plainTextToken;
            
        return new ApiLoginResource([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }
}
