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

    // ================= User Profile (جديد) =================
    public function userProfile(Request $request)
    {
        // الدالة دي بترجع بيانات المستخدم صاحب التوكن الحالي
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ], 200);
    }

    // ================= Update Profile (جديد) =================
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|unique:users,email,' . $user->id,
        ]);

        // تحديث البيانات اللي اتبعتت بس
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

    // ================= Forgot Password (OTP) =================
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $otp = rand(100000, 999999);
        $hashedOtp = Hash::make($otp);
        $expiresAt = Carbon::now()->addMinutes(15);

        DB::table('otps')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => $hashedOtp,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );

        return response()->json([
            'message' => 'OTP generated successfully',
            'otp_test' => $otp // للتيست فقط في Postman
        ]);
    }

    // ================= Reset Password (Verify OTP) =================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $record = DB::table('otps')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->otp, $record->otp) || Carbon::now()->gt($record->expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('otps')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }
}