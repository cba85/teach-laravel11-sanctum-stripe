<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthTokenController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            "name" => "required",
            "email" => "required|email",
            "password" => "required|min:8",
        ]);

        $userExists = User::where('email', $request->email)->exists();

        if ($userExists) {
            return response()->json(['error' => "User exists"], 409);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken("client");
        $user->token = $token->plainTextToken;

        return response()->json($user);
    }

    public function login(Request $request) {
        $request->validate([
            "email" => "required|email",
            "password" => "required|min:8",
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => "Invalid credentials"], 401);
        }

        $user->tokens()->where("name", "client")->delete();
        $token = $user->createToken("client");

        $user->token = $token->plainTextToken;

        return response()->json($user);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response(null, 204);
    }
}
