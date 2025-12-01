<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller{

    //Registrar usuario
    public function register(Request $request){
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'user successfully created']);
    }

    //loguear usuario con expiración de token
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Incorrect credentials'],
            ]);
        }

        // Token de acceso de 1 hora
        $accessToken = $user->createToken(
            name: 'access',
            abilities: ['*'],
            expiresAt: now()->addHour()
        )->plainTextToken;

        // Refresh token 1 semana
        $refreshToken = $user->createToken(
            name: 'refresh',
            abilities: ['token:refresh'],
            expiresAt: now()->addDays(7)
        )->plainTextToken;

        return response()->json([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600
        ]);
    }

    //regeneración de token
    public function refresh(Request $request)
    {
        $refreshToken = $request->bearerToken();

        if (! $refreshToken) {
            return response()->json(['message' => 'Invalid Token'], 401);
        }

        // Extraer ID
        $tokenId = explode('|', $refreshToken)[0];

        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);

        // Verificar que exista y tenga la ability correcta
        if (! $token || ! $token->can('token:refresh')) {
            return response()->json(['message' => 'Invalid Token'], 401);
        }

        $user = $token->tokenable; 

        // Invalidar refresh token anterior
        $token->delete();

        // Crear nuevo access token
        $accessToken = $user->createToken(
            name: 'access',
            abilities: ['*'],
            expiresAt: now()->addHour()
        )->plainTextToken;

        // Crear nuevo refresh token
        $newRefreshToken = $user->createToken(
            name: 'refresh',
            abilities: ['token:refresh'],
            expiresAt: now()->addDays(30)
        )->plainTextToken;

        return response()->json([
            'access_token'  => $accessToken,
            'refresh_token' => $newRefreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600
        ]);
    }

    //info de usuario
    public function me(Request $request)
    {
        return $request->user();
    }

    //cerrar sesión, borra el tokjen
    public function logout(Request $request){
    $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

    return response()->json(['message' => 'Session successfully closed']);
    }

}
