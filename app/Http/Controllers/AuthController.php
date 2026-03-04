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
            'phone' => 'required|string|min:11|unique:users,phone', 
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone, 
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // ================= Login (Smart Logic) =================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string', 
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->orWhere('phone', $request->email)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user->tokens()->delete(); 

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'is_admin' => (bool) $user->is_admin,
            'user' => $user
        ], 200);
    }

    // ================= User Profile =================
    public function userProfile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ], 200);
    }

    // ================= Forgot Password (Phone Only) =================
    public function forgotPassword(Request $request)
    {
        // بنطلب رقم التليفون فقط وبنتأكد إنه موجود في قاعدة البيانات
        $request->validate([
            'phone' => 'required|string|exists:users,phone'
        ]);

        $user = User::where('phone', $request->phone)->first();

        $otp = rand(100000, 999999);
        $hashedOtp = Hash::make($otp);
        $expiresAt = Carbon::now()->addMinutes(15);

        // بنربط الـ OTP بإيميل اليوزر في جدول الـ OTPs كمرجع ثابت وفريد
        DB::table('otps')->updateOrInsert(
            ['email' => $user->email],
            [
                'otp' => $hashedOtp,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        return response()->json([
            'message' => 'OTP sent to your phone number successfully',
            'otp_test' => $otp 
        ]);
    }

    // ================= Reset Password (Phone Only) =================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('phone', $request->phone)->first();

        // بنجيب السجل المربوط بإيميل الشخص صاحب رقم التليفون ده
        $record = DB::table('otps')->where('email', $user->email)->first();

        if (!$record || !Hash::check($request->otp, $record->otp) || Carbon::now()->gt($record->expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('otps')->where('email', $user->email)->delete();
        $user->tokens()->delete(); // تسجيل خروج من كل الأجهزة للأمان

        return response()->json([
            'message' => 'Password has been reset successfully'
        ]);
    }

    // ================= Logout =================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}