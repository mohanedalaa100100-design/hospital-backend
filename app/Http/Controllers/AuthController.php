<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ================= Register =================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // ================= Login =================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    // ================= Logout =================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    // ================= Forgot Password =================
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = rand(100000, 999999); // 6-digit code
        $hashedToken = Hash::make($token); // hash it before storing
        $expiresAt = Carbon::now()->addMinutes(15); // 15 minutes expiration

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $hashedToken,
                'created_at' => Carbon::now(),
                'expires_at' => $expiresAt
            ]
        );

        // هنا بدل ما نرجع الكود في الـ response، يبقى المفروض نبعته في email أو SMS
        // للعرض دلوقتي ممكن نرسل رسالة تجريبية:
        // Mail::to($request->email)->send(new ResetCodeMail($token));

        return response()->json([
            'message' => 'Reset code sent to your email'
        ]);
    }

    // ================= Reset Password =================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid reset request'], 400);
        }

        // تحقق من صلاحية الكود (15 دقيقة)
        if (Carbon::now()->gt(Carbon::parse($record->expires_at))) {
            return response()->json(['message' => 'Reset code expired'], 400);
        }

        // تحقق من صحة الكود
        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Invalid reset code'], 400);
        }

        // غيّر الباسورد
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // احذف السجل بعد الاستخدام
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }
}