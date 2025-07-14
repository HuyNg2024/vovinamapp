<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\table_atg_members;


class PasswordResetController extends Controller
{
    public function sendOtp(Request $request)
    {


        $request->validate(['email' => 'required|email|exists:table_atg_members,email']);


        $otp = rand(100000, 999999);
        $email = $request->email;

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $otp, 'created_at' => Carbon::now()]
        );

        Mail::to($email)->send(new ResetPasswordOTP($otp));



        return response()->json(['success' => 'OTP sent to your email.']);

    }

    public function verifyOtp(Request $request)
    {
        $request->validate([

            'email' => 'required|email|exists:table_atg_members,email',
            'otp' => 'required',

            //'password' => 'required|string|min:6|confirmed'



        ]);

        $otpRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$otpRecord || Carbon::parse($otpRecord->created_at)->addMinutes(30)->isPast()) {


            return response()->json(['error' => 'Không đúng or hết thời gian OTP.'], 400);
        }

        return response()->json(['success' => 'thành công']);
    }
    
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:table_atg_members,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $otpRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$otpRecord || Carbon::parse($otpRecord->created_at)->addMinutes(30)->isPast()) {
            return response()->json(['error' => 'ko hợp lệ or OTP đã hết hạn.'], 400);
        }

        $user = table_atg_members::where('email', $request->email)->first();


        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();


        return response()->json(['success' => 'Password đã được reset thành công.']);

    }
}
