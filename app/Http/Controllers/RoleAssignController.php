<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class RoleAssignController extends Controller
{
    /**
     * Assign multiple roles to a user for a specific branch (using role_id)
     */
    // public function assignRolesToUser(Request $request)
    // {
    //     $request->validate([
    //         'user_id'   => 'required|exists:users,id',
    //         'branch_id' => 'required|exists:branches,id',
    //         'role_ids'  => 'required|array',
    //         'role_ids.*' => 'exists:roles,id',
    //     ]);

    //     try {

    //         $user = User::findOrFail($request->user_id);

    //         $existingRoles = $user->branchRoles()
    //             ->wherePivot('branch_id', $request->branch_id)
    //             ->get();

    //         foreach ($existingRoles as $role) {
    //             $user->removeBranchRoleById($role->id, $request->branch_id);
    //         }

    //         foreach ($request->role_ids as $roleId) {
    //             $user->assignBranchRoleById($roleId, $request->branch_id);
    //         }

    //         return response()->json([
    //             'message' => 'Roles assigned to user for branch successfully.',
    //         ]);
    //     } catch (Exception $e) {

    //         return response()->json($e);
    //     }
    // }

    public static function assignRolesToUser(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'branches'  => 'required|array',
            'branches.*.branch_id' => 'required|exists:branches,id',
            'branches.*.role_ids'  => 'required|array',
            'branches.*.role_ids.*' => 'exists:roles,id',
        ]);

        try {
            $user = User::findOrFail($request->user_id);

            foreach ($request->branches as $branchData) {
                $branchId = $branchData['branch_id'];
                $roleIds = $branchData['role_ids'];

                $existingRoles = $user->branchRoles()
                    ->wherePivot('branch_id', $branchId)
                    ->get();

                foreach ($existingRoles as $role) {
                    $user->removeBranchRoleById($role->id, $branchId);
                }

                foreach ($roleIds as $roleId) {
                    $user->assignBranchRoleById($roleId, $branchId);
                }
            }

            return response()->json([
                'message' => 'Roles assigned to user for selected branches successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error assigning roles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get all role IDs of a user for a specific branch
     */
    public function getUserRolesForBranch($userId, $branchId)
    {
        $user = User::findOrFail($userId);
        $roles = $user->branchRoles()
            ->wherePivot('branch_id', $branchId)
            ->pluck('id');

        return response()->json([
            'role_ids' => $roles,
        ]);
    }

    /**
     * Remove all roles of a user for a specific branch
     */
    public function removeUserRolesFromBranch(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user = User::findOrFail($request->user_id);

        $roles = $user->branchRoles()->wherePivot('branch_id', $request->branch_id)->get();

        foreach ($roles as $role) {
            $user->removeBranchRoleById($role->id, $request->branch_id);
        }

        return response()->json([
            'message' => 'All roles removed from user for branch.',
        ]);
    }
}
