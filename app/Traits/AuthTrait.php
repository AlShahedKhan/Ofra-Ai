<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;



trait AuthTrait
{
    use HandlesApiResponse;
    use AdminAuthGetTrait;

    public function profile()
    {
        return $this->safeCall(function () {
            $user = Auth::user();

            return $this->successResponse(
                'User profile data',
                ['user' => $user],
            );
        });
    }

    // Refresh Token API (GET) (Auth Token - Header)
    public function refreshToken()
    {
        return $this->safeCall(function () {
            $user = request()->user(); //user data
            $token = $user->createToken("newToken");

            $refreshToken = $token->accessToken;

            return $this->successResponse(
                'Token refreshed successfully',
                ['token' => $refreshToken],
            );
        });
    }

    public function forgotPassword(Request $request)
    {
        return $this->safeCall(function () use ($request) {

            $request->validate([
                'email' => 'required|email',
            ]);

            // Check if user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse('Email not found.', 404);
            }

            // Generate a numeric OTP (6 digits)
            $otp = new Otp();
            $generatedOtp = $otp->generate($request->email, 'numeric', 6); // Numeric OTP, 6 digits

            // Check if OTP generation was successful (the returned object should have 'status' key)
            if ($generatedOtp->status) {
                // Send OTP via email
                Mail::to($request->email)->send(new OtpMail($generatedOtp->token));

                return $this->successResponse('OTP sent to your email for password reset.');
            }
        });

        return $this->errorResponse('Failed to generate OTP.', 500);
    }

    /**
     * Reset password with OTP validation.
     */
    public function resetPassword(Request $request)
    {
        return $this->safeCall(function () use ($request) {

            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|numeric|digits:6', // Numeric OTP validation
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Validate the OTP
            $otp = new Otp();
            $validation = $otp->validate($request->email, $request->otp);

            if (!$validation->status) {
                return $this->errorResponse($validation->message, 400);
            }

            // Reset the user's password
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse('User not found.', 404);
            }

            $user->password = bcrypt($request->password);
            $user->save();

            return $this->successResponse(
                'Password reset successfully.',
                ['user' => $user],
            );
        });
    }
}
