<?php

// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;

// class AuthController extends Controller
// {
//     public function register(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'name'     => 'required|string|max:255',
//             'email'    => 'required|string|email|max:255|unique:users',
//             'password' => 'required|string|min:6|confirmed',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['error' => $validator->errors()], 422);
//         }

//         $user = User::create([
//             'name'     => $request->name,
//             'email'    => $request->email,
//             'password' => Hash::make($request->password),
//         ]);

//         $token = JWTAuth::fromUser($user);

//         return response()->json([
//             'user'  => $user,
//             'token' => $token
//         ], 201);
//     }

//     public function login(Request $request)
//     {
//         $credentials = $request->only('email', 'password');

//         if (!$token = JWTAuth::attempt($credentials)) {
//             return response()->json(['error' => 'Invalid credentials'], 401);
//         }

//         return response()->json([
//             'user'  => auth()->user(),
//             'token' => $token
//         ]);
//     }
// }



namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|min:6']);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('token-key')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:6',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 403);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Optionally: revoke all tokens (force logout from other devices)
        $user->tokens()->delete();

        return response()->json(['message' => 'Password changed successfully']);
    }


    public function allUser(){
        $users = User::all();
        return response()->json($users);
    }
}
