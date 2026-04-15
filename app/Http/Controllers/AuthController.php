<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthController extends Controller
{
    // ================= Register (تسجيل مستخدم جديد مع الملف الطبي) =================
    public function register(Request $request)
    {
        // 1. التحقق من بيانات المستخدم
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'phone' => 'required|string|min:11|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();

        try {
            // الخطوة أ: إنشاء اليوزر في جدول users
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'user', 
            ]);

            
            if (method_exists($user, 'medicalProfile')) {
                $user->medicalProfile()->create([
                    'full_name' => $user->name,
                ]);
            }

            // لو الخطوتين نجحوا، نثبت البيانات
            DB::commit();

            // إصدار توكن الدخول (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);

        } catch (Exception $e) {
            // لو حصل أي Error، تراجع عن كل شيء (Rollback)
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التسجيل، تم إلغاء العملية لتجنب تكرار البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================= Login (الدخول بالإيميل أو الهاتف) =================
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
            return response()->json([
                'status' => false,
                'message' => 'بيانات الدخول غير صحيحة'
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    // ================= باقي الميثودز (Profile, ForgotPassword, الخ) زي ما هي بدون تغيير =================
    public function userProfile(Request $request)
    {
        $user = User::with('medicalProfile')->find(Auth::id());
        return response()->json(['status' => true, 'user' => $user], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone']);
        $user = User::where('phone', $request->phone)->first();
        $otp = rand(100000, 999999);
        DB::table('otps')->updateOrInsert(
            ['email' => $user->email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(15),
                'updated_at' => Carbon::now()
            ]
        );
        return response()->json(['status' => true, 'message' => 'تم إرسال كود التحقق بنجاح', 'otp_test' => $otp]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone', 'otp' => 'required']);
        $user = User::where('phone', $request->phone)->first();
        $record = DB::table('otps')->where('email', $user->email)->first();
        if (!$record || !Hash::check($request->otp, $record->otp) || Carbon::now()->gt($record->expires_at)) {
            return response()->json(['status' => false, 'message' => 'كود التحقق غير صحيح أو انتهت صلاحيته'], 400);
        }
        return response()->json(['status' => true, 'message' => 'تم التحقق من الكود بنجاح'], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone', 'password' => 'required|min:6|confirmed']);
        $user = User::where('phone', $request->phone)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        DB::table('otps')->where('email', $user->email)->delete();
        $user->tokens()->delete();
        return response()->json(['status' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'تم تسجيل الخروج بنجاح'], 200);
    }
}