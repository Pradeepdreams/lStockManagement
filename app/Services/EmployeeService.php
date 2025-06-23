<?php

namespace App\Services;

use App\Http\Controllers\RoleAssignController;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_employees'), 403, 'Unauthorized');

        $branchId = $request->get('branch_id');
        $roleId = $request->get('role_id');
        $perPage = $request->get('per_page', 15);
        $search  = $request->get('search');

        $employeeQuery = Employee::query();

        if ($search) {
            $employeeQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $employees = $employeeQuery->whereHas('user.branchRoles', function ($query) use ($branchId, $roleId) {
            if ($branchId) {
                $query->where('branch_user_role.branch_id', $branchId);
            }
            if ($roleId) {
                $query->where('branch_user_role.role_id', $roleId);
            }
        })
            ->with(['user.branchRoles'])
            ->paginate($perPage);

        // Optional: group roles by branch as before
        $employees->getCollection()->transform(function ($employee) {
            if ($employee->user) {
                $grouped = $employee->user->branchRoles
                    ->groupBy('pivot.branch_id')
                    ->map(function ($roles, $branchId) {
                        return [
                            'branch_id' => (int) $branchId,
                            'branch_name' => \App\Models\Branch::find($branchId)?->name,
                            'roles' => $roles->map(fn($role) => [
                                'id' => $role->id,
                                'name' => $role->name,
                            ])->values(),
                        ];
                    })->values();

                $employee->user->branch_data = $grouped;
                unset($employee->user->branchRoles);
            }

            return $employee;
        });

        return response()->json($employees);
    }

    public function store($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_employees'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($request) {

                $data = $request->validated();

                // Create user
                $user = User::create([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // Assign roles to user based on branch
                $roleData = [
                    'user_id' => $user->id,
                    'branches' => $request->branches
                ];

                $roleAssign = new RoleAssignController();
                $roleAssign->assignRolesToUser(new Request($roleData));

                // Add user_id to employee data
                $employeeData = $data;
                $employeeData['user_id'] = $user->id;

                // Create employee
                $employee = Employee::create($employeeData);
                logActivity('Created', $employee, [$employee]);

                return response()->json([
                    'message' => 'Employee created successfully.',
                    'data' => [
                        'user' => $user,
                        'employee' => $employee,
                    ]
                ], 201);
            });
        } catch (Exception $e) {
            throw new Exception("Failed to create employee: " . $e->getMessage());
        }
    }

    public function show($encryptedId)
    {

        abort_unless(auth()->user()->hasBranchPermission('view_employees'), 403, 'Unauthorized');

        $id = Crypt::decryptString($encryptedId);

        $employee = Employee::where('id', $id)
            ->with(['user.branchRoles'])
            ->first();

        if ($employee->user) {
            $grouped = $employee->user->branchRoles
                ->groupBy('pivot.branch_id')
                ->map(function ($roles, $branchId) {
                    return [
                        'branch_id' => (int) $branchId,
                        'branch_name' => \App\Models\Branch::find($branchId)?->name,
                        'roles' => $roles->map(fn($role) => [
                            'id' => $role->id,
                            'name' => $role->name,
                        ])->values(),
                    ];
                })->values();

            $employee->user->branch_data = $grouped;
            unset($employee->user->branchRoles);
        }

        return $employee;
    }


    public function update($request, $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('edit_employees'), 403, 'Unauthorized');


        try {
            return DB::transaction(function () use ($request, $encryptedId) {
                $id = Crypt::decryptString($encryptedId);

                $employee = Employee::where('id', $id)->first();

                $user = User::where('id', $employee->user_id)->first();
                $roleData = [
                    'user_id' => $user->id,
                    'branches' => $request->branches
                ];

                $roleAssign = new RoleAssignController();
                $roleAssign->assignRolesToUser(new Request($roleData));
                $employee->update($request->validated());
                logActivity('Updated', $employee, [$employee]);


                if ($employee->user) {
                    $grouped = $employee->user->branchRoles
                        ->groupBy('pivot.branch_id')
                        ->map(function ($roles, $branchId) {
                            return [
                                'branch_id' => (int) $branchId,
                                'branch_name' => \App\Models\Branch::find($branchId)?->name,
                                'roles' => $roles->map(fn($role) => [
                                    'id' => $role->id,
                                    'name' => $role->name,
                                ])->values(),
                            ];
                        })->values();

                    $employee->user->branch_data = $grouped;
                    unset($employee->user->branchRoles);
                }
            });

            return response()->json([
                "message" => "Employee updated successfully.",
                "employee" => $employee
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to update employee: " . $e->getMessage());
        }
    }

    public function destroy($encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_employees'), 403, 'Unauthorized');

        $id = Crypt::decryptString($encryptedId);
        $employee = Employee::where('id', $id)->first();
        $usedInPurchaseOrders = PurchaseOrder::where('created_by', $employee->user_id)->exists();

        if ($usedInPurchaseOrders) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee cannot be deleted because they have created purchase orders.'
            ], 403);
        }
        // $user = User::where('id', $employee->user_id)->first();
        // $user->delete();
        $employee->delete();
        logActivity('Deleted', $employee, [$employee]);
        return;
    }

    public function list()
    {
        $users = User::whereHas('employee', function ($query) {
            $query->where('is_active', true);
        })
            ->orWhere('id', 1)
            ->with('employee')
            ->get();
        return $users;
        // return Employee::where('is_active', 1)->get();
    }
}
