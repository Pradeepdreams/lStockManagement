<?php

namespace App\Services\Masters;

use App\Models\Group;
use App\Models\LedgerGroup;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_group'), 403, 'Unauthorized');

        try {
            $search = $request->search ?? null;

            $groupQuery = Group::query();

            if ($search) {
                $groupQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }
            $groups = $groupQuery->latest()->paginate(10);

            $getLinks = $groups->jsonSerialize();

            foreach ($getLinks['links'] as &$row) {

                if ($row['label'] == "Next &raquo;") {

                    $row['label'] = 'Next';
                }

                if ($row['label'] == "&laquo; Previous") {

                    $row['label'] = 'Previous';
                }
            }
            return response([
                'success' => true,
                'groups' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list ledger group: ' . $e->getMessage());
        }
    }

    public function store($data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_group'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($data) {
                $group = Group::create($data);
                logActivity('Created', $group, [$group]);
                return $group;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to create ledger group: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_group'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return Group::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Group: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_group'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $data) {
                $id = Crypt::decryptString($id);
                $group = Group::findOrFail($id);
                if (isset($data['is_active']) && !$data['is_active']) {

                    if ($group->vendors()->exists()) {
                        throw new Exception('Cannot deactivate the group because it is linked with other modules.');
                    }
                }
                $changes = $group->getChangedAttributesFromRequest($data);
                $group->update($data);
                logActivity('Updated', $group, [$changes]);
                return $group;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to update ledger group: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_group'), 403, 'Unauthorized');

        try {
            // return DB::transaction(function () use ($id) {
            $id = Crypt::decryptString($id);
            $group = Group::findOrFail($id);
            if ($group->vendors()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete group. It is assigned to one or more vendors.',
                ], 400);
            }
            $group->delete();
            logActivity('Deleted', $group, [$group]);
            return true;
            // });
        } catch (Exception $e) {
            throw new Exception('Failed to delete ledger group: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return Group::get();
    }
}
