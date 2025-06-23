<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
// use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function index()
    {

        $roles = Role::paginate(10);

        return response()->json($roles);
    }

    public function store(Request $request)
    {

        $request->validate([
            'role_name' => 'required|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->role_name, 'guard_name' => 'sanctum']);
        logActivity('Created', $role, [$role]);
        if ($request->permission_ids) {

            $data = [
                "role_id" => $role->id,
                "permission_ids" => $request->permission_ids
            ];
            $permissions = $this->assignPermissionsToRole(new Request($data));
        }


        return response()->json($role, 201);
    }


    public function assignPermissionsToRole(Request $request)
    {

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::where('id', $request->role_id)
            ->where('guard_name', 'sanctum')
            ->firstOrFail();


        $permissions = Permission::whereIn('id', $request->permission_ids)
            ->where('guard_name', 'sanctum')
            ->get();
        // $permissionChanges = $permissions->getChangedAttributesFromRequest($request->permission_ids);
        $role->syncPermissions($permissions);
        logActivity('Assigned Permission to Role', $role, [$permissions]);
        return response()->json([
            'message' => 'Permissions assigned to role successfully',
        ]);
    }


    public function show($id)
    {
        $id = Crypt::decryptString($id);
        $role = Role::where('id', $id)->with('permissions', 'branchUsers')->get();
        return response()->json($role);
    }


    public function update(Request $request, $id)
    {
        $id = Crypt::decryptString($id);
        $role = Role::findOrFail($id);
        // $changes = $role->getChangedAttributesFromRequest($request->role_name);

        $role->update(['name' => $request->role_name, 'guard_name' => 'sanctum']);
        logActivity('Updated', $role, [$role]);
        if ($request->permission_ids) {

            $data = [
                "role_id" => $role->id,
                "permission_ids" => $request->permission_ids
            ];
            $permissions = $this->assignPermissionsToRole(new Request($data));
        }
        return response()->json($role);
    }


    public function destroy($id)
    {
        $id = Crypt::decryptString($id);
        $role = Role::findOrFail($id);
        if ($role->branchUsers()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role. It is assigned to one or more users.',
            ], 400);
        }
        $role->delete();
        logActivity('Deleted', $role, [$role]);
        return response()->json("Role Deleted Successfully");
    }


    public function list(){
        $roles = Role::get();
        return response()->json($roles);
    }
}
