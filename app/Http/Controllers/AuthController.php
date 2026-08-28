<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // kayıt ol
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Kayıt başarılı!',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // giriş yap
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // email ile kullanıcıyı bul
        $user = User::where('email', $request->email)->first();

        // kullanıcı bulunamadı veya şifre yanlış
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email veya şifre hatalı.'], 401);
        }

        // eski tokenları sil, yeni token oluştur
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Giriş başarılı!',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    // çıkış yap
    public function logout(Request $request)
    {
        // mevcut tokenı sil
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    // giriş yapan kullanıcının bilgileri
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
