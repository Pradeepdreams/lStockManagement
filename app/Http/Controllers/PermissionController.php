<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        $grouped = $permissions->groupBy(function ($permission) {
            $name = $permission->name;
            $prefixes = ['view_', 'create_', 'update_', 'delete_'];

            foreach ($prefixes as $prefix) {
                if (Str::startsWith($name, $prefix)) {

                    $category = Str::after($name, $prefix);
                    return str_replace('_', ' ', $category);
                }
            }

            return 'others';
        });

        $orderedPermissions = $grouped->sortKeys();

        return response()->json($orderedPermissions);
    }

    // Store multiple permissions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.name' => 'required|string|distinct|unique:permissions,name',
        ]);

        $created = [];
        foreach ($validated['permissions'] as $permData) {
            $created[] = Permission::create([
                'name' => $permData['name'],
                'guard_name' => 'sanctum',
            ]);
        }

        return response()->json($created, 201);
    }

    // Show single permission
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    // Update multiple permissions
    public function update(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.id' => 'required|exists:permissions,id',
            'permissions.*.name' => 'required|string',
        ]);

        $updated = [];
        foreach ($validated['permissions'] as $permData) {
            $permission = Permission::find($permData['id']);

            if ($permission->name !== $permData['name']) {
                $permission->update([
                    'name' => $permData['name'],
                ]);
            }

            $updated[] = $permission;
        }

        return response()->json($updated);
    }

    // Delete single permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
