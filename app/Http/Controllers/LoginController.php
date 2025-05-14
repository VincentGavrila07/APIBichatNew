<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Login endpoint ter-hit', ['email' => $request->email]);
        Log::info('Login endpoint ter-hit', ['password' => $request->password]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Simpan user ID di session
        $request->session()->put('user_id', $user->id);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user_id');
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $user = User::find($userId);
        return response()->json(['user' => $user]);
    }
}
