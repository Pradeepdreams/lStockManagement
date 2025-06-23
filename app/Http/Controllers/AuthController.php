<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    // Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        // $user->sendEmailVerificationNotification();

        return response()->json([
            "success" => true,
            'message' => 'User registered Successfully'
        ]);
    }

    // Login
    public function login(Request $request)
    {

        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Employee::where('email', $request->email)->exists()) {


            $employee = Employee::where('email', $request->email)->first();

            if ($employee->is_active == 0) {
                return response()->json(['message' => 'Your account is not active contact Admin.'], 403);
            }
        }

        // Attempt login using credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Optionally check for email verification
        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json(['message' => 'Email not verified.'], 403);
        // }

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        logActivity('Login', null, ['email' => $user->email]);

        // Fetch roles, permissions, etc.
        $user = $this->getUserBranchRolesPermissions($user->id);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }


    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        logActivity('Logout', null, ['email' => auth()->user()?->email]);

        return response()->json(['message' => 'Logged out']);
    }


    // Email Verification
    public function emailVerify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return response()->json(['message' => 'Email verified']);
    }

    // resend verification mail
    public function resendVerificationMail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent']);
    }

    // forget password
    public function forgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink(
            $request->only('email')
        );

        return response()->json(['message' => 'Reset link sent']);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['Your current password is incorrect.'],
            ]);
        }

        if (Hash::check($request->new_password, $user->password)) {
            throw ValidationException::withMessages([
                'new_password' => ['The new password cannot be the same as the old password.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->new_password)
        ])->save();

        $user->tokens()->delete();

        return response()->json(['message' => 'Password changed successfully.']);
    }


    public function getUserBranchRolesPermissions($userId)
    {
        $user = User::findOrFail($userId);

        $branchRoles = $user->branchRoles()->with('permissions')->get();

        $grouped = $branchRoles->groupBy(function ($role) {
            return $role->pivot->branch_id;
        });

        $branches = [];

        foreach ($grouped as $branchId => $roles) {
            $branch = \App\Models\Branch::find($branchId);
            $branches[] = [
                'branch'   => $branch,
                'roles' => $roles->map(function ($role) {
                    return [
                        'id'          => $role->id,
                        'name'        => $role->name,
                        'permissions' => $role->permissions->pluck('name'),
                    ];
                }),
            ];
        }

        return response()->json([
            'user'  => $user,
            'branches' => $branches,
        ]);
    }


    // switch branch
    public function switchBranch(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user = auth()->user();
        $branchId = $validated['branch_id'];

        $branchRoles = $user->branchRoles()
            ->wherePivot('branch_id', $branchId)
            ->with('permissions')
            ->get();

        if ($branchRoles->isEmpty()) {
            return response()->json(['message' => 'You are not assigned to this branch.'], 403);
        }

        app()->instance('currentBranchId', $branchId);

        $branch = Branch::find($branchId);

        return response()->json([
            'user' => $user,
            'branch' => [
                'branch_id'   => $branch->id_crypt,
                'branch_name' => $branch->name ?? 'Unknown',
                'roles' => $branchRoles->map(function ($role) {
                    return [
                        'id'          => $role->id,
                        'name'        => $role->name,
                        'permissions' => $role->permissions->pluck('name'),
                    ];
                }),
            ]
        ]);
    }
}
