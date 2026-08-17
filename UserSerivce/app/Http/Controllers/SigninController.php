<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SigninController extends Controller
{
    public function signin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'message' => 'User signed in successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
