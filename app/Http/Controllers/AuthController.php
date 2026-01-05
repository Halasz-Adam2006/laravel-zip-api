<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login user.
     *
     * Authenticates a user and returns an access token.
     *
     * @group Authentication
     * @unauthenticated
     * @bodyParam email string required User email address. Example: user@example.com
     * @bodyParam password string required User password. Example: password123
     * @response 200 {
     *   "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token,'user' => $user]);
    }

    /**
     * Register a new user.
     *
     * Creates a new user account and returns an access token.
     *
     * @group Authentication
     * @unauthenticated
     * @bodyParam name string required User's full name. Example: John Doe
     * @bodyParam email string required User email address. Example: user@example.com
     * @bodyParam password string required User password. Example: password123
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "created_at": "2025-01-01T00:00:00.000000Z",
     *     "updated_at": "2025-01-01T00:00:00.000000Z"
     *   },
     *   "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
     * }
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Logout user.
     *
     * Revokes the current access token.
     *
     * @group Authentication
     * @authenticated
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
