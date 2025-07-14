<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class TokenController extends Controller
{
    public function createToken(Request $request)
    {
        $user = User::find(1); // Thay thế bằng ID người dùng thực tế

        if ($user) {
            $token = $user->createToken('TestToken')->plainTextToken;
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'User not found'], 404);
    }
}

