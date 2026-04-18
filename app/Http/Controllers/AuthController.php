<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        return view('auth-basic-signup');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'terms' => 'required|accepted',
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'terms.required' => 'You must agree to the Terms & Conditions.',
            'terms.accepted' => 'You must agree to the Terms & Conditions.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Redirect to login with success message
        return redirect()->route('login')
            ->with('success', 'Registration successful! Please login to continue.');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth-basic-signin');
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'The password field is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Attempt to log the user in
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Store user permissions in session for quick access
            $user = Auth::user();
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
            $roles = $user->getRoleNames()->toArray();
            
            session([
                'user_permissions' => $permissions,
                'user_roles' => $roles,
                'user_type' => $user->user_type,
            ]);
            
            // Always redirect to dashboard after login
            return redirect()->route('dashboard')
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        // Authentication failed
        return redirect()->back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the logged-in user's profile form.
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['staff', 'customer']);

        return view('user-profile', compact('user'));
    }

    /**
     * Update the logged-in user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user()->load(['staff', 'customer']);
        $staff = $user->staff;
        $customer = $user->customer;

        $emailRules = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];

        if ($staff) {
            $emailRules[] = Rule::unique('staff', 'email')->ignore($staff->id);
        }

        if ($customer) {
            $emailRules[] = Rule::unique('customers', 'email')->ignore($customer->id);
        }

        $validator = Validator::make($request->all(), [
            'profileImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'phone' => [$staff ? 'required' : 'nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->filled('password') && ! Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('profileImage') && ! $staff) {
                $user->profile_image = $this->uploadProfileImage(
                    $request->file('profileImage'),
                    $user->profile_image,
                    'profile'
                );
            }

            $user->save();

            if ($staff) {
                $nameParts = preg_split('/\s+/', trim($request->name), 2);

                $staff->first_name = $nameParts[0] ?? $request->name;
                $staff->last_name = $nameParts[1] ?? '';
                $staff->email = $request->email;
                $staff->phone = $request->phone;

                if ($request->filled('password')) {
                    $staff->password = $user->password;
                }

                if ($request->hasFile('profileImage')) {
                    $staff->profile_image = $this->uploadProfileImage(
                        $request->file('profileImage'),
                        $staff->profile_image,
                        'staff',
                        $staff->id
                    );
                }

                $staff->save();
            }

            if ($customer) {
                $customer->contact_person = $request->name;
                $customer->email = $request->email;
                $customer->phone = $request->phone;

                if ($request->filled('password')) {
                    $customer->password = $user->password;
                }

                $customer->save();
            }

            DB::commit();

            return redirect()->route('user-profile')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update profile. Please try again.')
                ->withInput();
        }
    }

    /**
     * Upload a profile image and remove the previous file when present.
     */
    private function uploadProfileImage($image, ?string $oldImage = null, string $folder = 'profile', ?int $recordId = null): string
    {
        $extension = $image->getClientOriginalExtension();
        $imageName = time() . ($recordId ? '_' . $recordId : '') . '.' . $extension;
        $uploadPath = public_path('uploads/' . $folder);

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $image->move($uploadPath, $imageName);

        if ($oldImage) {
            $oldImagePath = $uploadPath . DIRECTORY_SEPARATOR . $oldImage;

            if (file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }

        return $imageName;
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth-forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'We could not find a user with that email address.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate a password reset token
        $token = Str::random(64);

        // Store the token in the password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Send the password reset email
        $user = User::where('email', $request->email)->first();
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'token' => $token
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Reset Your Password - Technofra Admin');
            });

            return redirect()->back()
                ->with('success', 'We have sent a password reset link to your email address.');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Password reset email failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to send password reset email. Please try again.');
        }
    }

    /**
     * Show the reset password form
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        $email = $request->query('email');

        // Verify the token exists and is not expired
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired password reset token.');
        }

        // Check if token is expired (24 hours)
        if (Carbon::parse($passwordReset->created_at)->addHours(24)->isPast()) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect()->route('login')
                ->with('error', 'Password reset token has expired. Please request a new one.');
        }

        return view('auth-reset-password', compact('token', 'email'));
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'We could not find a user with that email address.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify the token
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired password reset token.');
        }

        // Check if token is expired (24 hours)
        if (Carbon::parse($passwordReset->created_at)->addHours(24)->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return redirect()->route('login')
                ->with('error', 'Password reset token has expired. Please request a new one.');
        }

        // Update the user's password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the password reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }

}

