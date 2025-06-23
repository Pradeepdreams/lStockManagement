<?php

namespace App\Services\Masters;

use App\Models\VendorGroup;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorGroupService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_vendor_groups'), 403, 'Unauthorized');

        try {
            $search = $request->search ?? null;

            $vendorQuery = VendorGroup::query();

            if ($search) {
                $vendorQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }
            $vendorGroups = $vendorQuery->latest()->paginate(10);

            $getLinks = $vendorGroups->jsonSerialize();

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
                'vendor_groups' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list Vendor Group: ' . $e->getMessage());
        }
    }

    public function store($data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_vendor_groups'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($data) {
                $vendorGroup = VendorGroup::create($data);
                logActivity('Created', $vendorGroup, [$vendorGroup]);
                return $vendorGroup;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to create Vendor Group: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_vendor_groups'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return VendorGroup::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Vendor Group: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_vendor_groups'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $data) {
                $id = Crypt::decryptString($id);
                $vendorGroup = VendorGroup::findOrFail($id);
                $changes = $vendorGroup->getChangedAttributesFromRequest($data);
                $vendorGroup->update($data);
                logActivity('Updated', $vendorGroup, [$changes]);
                return $vendorGroup;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to update Vendor Group: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_vendor_groups'), 403, 'Unauthorized');

        try {
            // return DB::transaction(function () use ($id) {
                $id = Crypt::decryptString($id);
                $vendorGroup = VendorGroup::findOrFail($id);
                if ($vendorGroup->vendors()->count() > 0) {
                    return response()->json([
                        'message' => 'Cannot delete Vendor Group. It is assigned to one or more vendors.',
                    ], 400);
                }
                $vendorGroup->delete();
                logActivity('Deleted', $vendorGroup, [$vendorGroup]);
                return response()->json(["message" => "Vendor Group Deleted successfully"]);
            // });
        } catch (Exception $e) {
            throw new Exception('Failed to delete Vendor Group: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return VendorGroup::get();
    }
}
