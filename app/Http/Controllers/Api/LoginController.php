<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'success' => false,
                'message' => 'These credentials do not match our records.'
            ], 404);
        }

        if($user->role == 1) {
            $ability = "admin";
        } elseif($user->role == 2) {
            $ability = "user";
        }

        $token = $user->createToken('ApiToken', [$ability])->plainTextToken;

        $response = [
            'success' => true,
            'user'    => $user,
            'token'   => $token,
            'role'    => $ability
        ];

        return response($response, 201);
    }

    public function logout()
    {
        auth()->logout();
        
        return response()->json([
            'success' => true
        ], 200);
    }
}
