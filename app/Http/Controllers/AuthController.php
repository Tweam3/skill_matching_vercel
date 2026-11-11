<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $user = User::where('Email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->Password_Hash)) {
            Log::warning('Failed login attempt', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $status = strtolower($user->Account_Status ?? 'active');
        if ($status === 'suspended') {
            $suspendedAt = $user->Suspended_At;
            if ($suspendedAt && $suspendedAt->copy()->addDays(3)->isPast()) {
                $user->update([
                    'Account_Status' => 'Active',
                    'Suspended_At' => null,
                ]);
            } else {
                return back()->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
            }
        }
        if ($status === 'banned') {
            return back()->withErrors(['email' => 'Your account has been banned. Please contact support.']);
        }
        if ($status === 'deleted') {
            return back()->withErrors(['email' => 'Your account has been deleted. Please contact support.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        if ($user->Is_Verified) {
            return $user->Role === 'Admin'
                ? redirect()->route('admin.index')
                : ($user->skills()->exists() ? redirect()->route('dashboard') : redirect()->route('skills.index')->with('first_visit', true));
        }

        return redirect()->route('pending.verification');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,Email'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users,Student_ID', 'regex:/^\d{4}-\d{4}-[A-Z]$/i'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
            'role' => ['nullable', 'string', 'in:Student,Faculty,Staff,Admin'],
            'council' => ['nullable', 'string', 'in:HBM,CSC,BIT,EDUC,Unaffiliated', 'required_if:role,Student,Faculty'],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'student_id.unique' => 'This Student ID is already registered. Please use a different one.',
            'student_id.regex' => 'Student ID must follow the format: YYYY-XXXX-X.',
        ]);

        // Normalize Student ID to uppercase so lowercase variants cannot bypass uniqueness
        $validated['student_id'] = strtoupper($validated['student_id']);

        $approvedStudentIds = [
            '2023-7317-M', '2023-6293-M', '2023-8104-M', '2023-1281-M', '2023-1302-M',
            '2023-6360-M', '2023-4875-M', '2023-6126-M', '2023-7485-M', '2023-4111-M',
            '2023-2197-M', '2023-1098-M', '2023-7180-M', '2023-2341-M', '2023-7986-M',
            '2023-0377-M', '2023-1757-M', '2023-7823-M', '2023-2433-M', '2023-6965-M',
            '2023-5966-M', '2009-0136-M', '2023-7177-M', '2023-0038-M', '2023-3806-M',
            '2023-2760-M', '2023-0642-M', '2023-7872-M', '2023-0278-M', '2023-0168-M',
        ];

        $role = 'Student';
        $verified = false;
        $accountStatus = 'Active';

        if (Auth::check() && Auth::user()->Role === 'Admin') {
            $role = $request->input('role', 'Student');
            $verified = $request->has('as_admin');
        } elseif (in_array(strtoupper($validated['student_id']), array_map('strtoupper', $approvedStudentIds))) {
            $verified = true;
            $role = 'Student';
        }

        $council = in_array($role, ['Student', 'Faculty'])
            ? $validated['council']
            : null;

        $user = User::create([
            'Full_Name' => $validated['name'],
            'Email' => $validated['email'],
            'Student_ID' => $validated['student_id'],
            'Password_Hash' => Hash::make($validated['password']),
            'Role' => $role,
            'Is_Verified' => $verified,
            'Account_Status' => $verified ? 'Active' : 'Pending',
            'Council' => $council,
        ]);

        if (! $verified) {
            $token = \Illuminate\Support\Str::random(64);
            $user->update(['Verification_Token' => $token]);
            try {
                Mail::to($validated['email'])->send(new \App\Mail\VerifyEmail($user, $token));
            } catch (\Exception $e) {
                \Log::warning('Verification email failed: ' . $e->getMessage());
            }
        }

        Auth::login($user);

        if ($verified) {
            return redirect()->route('skills.index')->with('first_visit', true);
        }

        return redirect()->route('pending.verification');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('Email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No account found with that email address.']);
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(60);

        $user->update([
            'Password_Reset_Token' => $token,
            'Password_Reset_Expires_At' => $expiresAt,
        ]);

        try {
            \Log::info('Attempting to send password reset email', [
                'mailer' => config('mail.default'),
                'to' => $user->Email,
                'from' => config('mail.from.address'),
            ]);
            Mail::to($user->Email)->send(new \App\Mail\PasswordReset($user, $token));
            \Log::info('Password reset email sent successfully', ['to' => $user->Email]);
        } catch (\Exception $e) {
            \Log::error('Password reset email failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mailer' => config('mail.default'),
                'to' => $user->Email,
            ]);
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again later.']);
        }

        return back()->with('success', 'Password reset link has been sent to your email.');
    }

    public function showResetPassword(string $token, int $userId)
    {
        $user = User::where('User_ID', $userId)
            ->where('Password_Reset_Token', $token)
            ->where('Password_Reset_Expires_At', '>', now())
            ->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        return view('auth.reset-password', ['token' => $token, 'userId' => $userId]);
    }

    public function resetPassword(Request $request, string $token, int $userId)
    {
        $user = User::where('User_ID', $userId)
            ->where('Password_Reset_Token', $token)
            ->where('Password_Reset_Expires_At', '>', now())
            ->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user->update([
            'Password_Hash' => Hash::make($request->password),
            'Password_Reset_Token' => null,
            'Password_Reset_Expires_At' => null,
        ]);

        return redirect()->route('login')->with('success', 'Your password has been reset. You can now log in.');
    }
}
