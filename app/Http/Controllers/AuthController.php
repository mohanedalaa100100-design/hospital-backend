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
            'role' => 'user', // تم التعديل هنا ليتطابق مع الـ Migration الجديدة
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role,
            'user' => $user
        ], 201);
    }

    // ================= Login =================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // بيسمح لليوزر يدخل بالإيميل أو رقم التليفون
        $user = User::where('email', $request->email)
                    ->orWhere('phone', $request->email)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        // مسح التوكنز القديمة عشان يفضل عنده توكن واحد فعال بس
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role, // تم التعديل لإرسال الـ role (admin, user, doctor)
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

    // ================= Forgot Password (OTP) =================
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone'
        ]);

        $user = User::where('phone', $request->phone)->first();

        // إنشاء كود OTP عشوائي
        $otp = rand(100000, 999999);

        // تخزين الكود في جدول الـ otps
        DB::table('otps')->updateOrInsert(
            ['email' => $user->email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(15),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'otp_test' => $otp // بنرجعه هنا عشان التجربة في Postman بس
        ]);
    }

    // ================= Verify OTP =================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'otp' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();
        $record = DB::table('otps')->where('email', $user->email)->first();

        if (!$record || !Hash::check($request->otp, $record->otp) || Carbon::now()->gt($record->expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'كود التحقق غير صحيح أو انتهت صلاحيته'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم التحقق من الكود بنجاح'
        ], 200);
    }

    // ================= Reset Password =================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('phone', $request->phone)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        // مسح الكود بعد الاستخدام وتسجيل الخروج من كل الأجهزة للأمان
        DB::table('otps')->where('email', $user->email)->delete();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }

    // ================= Logout =================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ], 200);
    }
}