<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staff;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        $remember = $request->has('remember');

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
            ->withInput($request->only('email'));
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

    // ==================== API AUTHENTICATION METHODS ====================

    /**
     * API: Login and return token with user data
     */
    public function apiLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Revoke existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get user permissions and roles
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        // Get user profile data
        $profile = null;
        $userType = 'admin';

        if ($user->staff) {
            $profile = $user->staff;
            $userType = 'staff';
        } elseif ($user->customer) {
            $profile = $user->customer;
            $userType = 'customer';
        }

        // Get menu items based on permissions
        $menuItems = $this->getMenuItemsForUser($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $userType,
                ],
                'profile' => $profile,
                'roles' => $roles,
                'permissions' => $permissions,
                'menu_items' => $menuItems,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * API: Register a new user
     */
    public function apiRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * API: Logout and revoke token
     */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * API: Get current user data with permissions
     */
    public function apiUser(Request $request)
    {
        $user = $request->user();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        $profile = null;
        $userType = 'admin';

        if ($user->staff) {
            $profile = $user->staff;
            $userType = 'staff';
        } elseif ($user->customer) {
            $profile = $user->customer;
            $userType = 'customer';
        }

        $menuItems = $this->getMenuItemsForUser($permissions);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $userType,
                ],
                'profile' => $profile,
                'roles' => $roles,
                'permissions' => $permissions,
                'menu_items' => $menuItems,
            ],
        ]);
    }

    /**
     * API: Refresh token
     */
    public function apiRefreshToken(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Get menu items based on user permissions
     */
    private function getMenuItemsForUser(array $permissions)
    {
        $menuItems = [
            [
                'name' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'bx-home-alt',
                'permission' => null, // Always visible
            ],
        ];

        // Access Control menu
        if (in_array('view_staff', $permissions) || in_array('view_roles', $permissions)) {
            $accessControlItems = [];
            
            if (in_array('view_staff', $permissions)) {
                $accessControlItems[] = [
                    'name' => 'Staff',
                    'route' => 'staff',
                    'permission' => 'view_staff',
                ];
            }
            
            if (in_array('view_roles', $permissions)) {
                $accessControlItems[] = [
                    'name' => 'Roles',
                    'route' => 'roles',
                    'permission' => 'view_roles',
                ];
            }

            $menuItems[] = [
                'name' => 'Access Control',
                'icon' => 'bx-user-circle',
                'children' => $accessControlItems,
            ];
        }

        // Renewal Master menu
        if (in_array('view_renewals', $permissions)) {
            $menuItems[] = [
                'name' => 'Renewal Master',
                'icon' => 'bx-category',
                'children' => [
                    ['name' => 'Client Renewal', 'route' => 'servies', 'permission' => 'view_renewals'],
                    ['name' => 'Vendor Renewal', 'route' => 'vendor-services.index', 'permission' => 'view_renewals'],
                    ['name' => 'Client', 'route' => 'client', 'permission' => 'view_renewals'],
                    ['name' => 'Vendor', 'route' => 'vendor1', 'permission' => 'view_renewals'],
                ],
            ];
        }

        // Leads
        if (in_array('view_leads', $permissions)) {
            $menuItems[] = [
                'name' => 'Leads',
                'route' => 'leads',
                'icon' => 'bx-user-voice',
                'permission' => 'view_leads',
            ];
        }

        // Projects
        if (in_array('view_projects', $permissions)) {
            $menuItems[] = [
                'name' => 'Projects',
                'route' => 'project',
                'icon' => 'bx-bar-chart',
                'permission' => 'view_projects',
            ];
        }

        // Tasks
        if (in_array('view_tasks', $permissions)) {
            $menuItems[] = [
                'name' => 'Tasks',
                'route' => 'task',
                'icon' => 'bx-task',
                'permission' => 'view_tasks',
            ];
        }

        // Raise Issue
        $menuItems[] = [
            'name' => 'Raise Issue',
            'route' => 'client-issue',
            'icon' => 'bx-error',
            'permission' => null, // Always visible
        ];

        // Clients
        if (in_array('view_clients', $permissions)) {
            $menuItems[] = [
                'name' => 'Client',
                'route' => 'clients',
                'icon' => 'bx-user-check',
                'permission' => 'view_clients',
            ];
        }

        return $menuItems;
    }
}
