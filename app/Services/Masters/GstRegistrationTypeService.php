<?php

namespace App\Services\Masters;

use App\Models\GstRegistrationType;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GstRegistrationTypeService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_gst_registration_type'), 403, 'Unauthorized');

        try {
            $search = $request->search ?? null;

            $registerTypeQuery = GstRegistrationType::query();

            if ($search) {
                $registerTypeQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')->orWhere('code', 'like', '%' . $search . '%');
                });
            }
            $registerTypes = $registerTypeQuery->latest()->paginate(10);

            $getLinks = $registerTypes->jsonSerialize();

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
                'gst_registration_types' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list Gst Registration Type: ' . $e->getMessage());
        }
    }

    public function store($data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_gst_registration_type'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($data) {
                $registerType = GstRegistrationType::create($data);
                logActivity('Created', $registerType, [$registerType]);
                return $registerType;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to create Gst Registration Type: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_gst_registration_type'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return GstRegistrationType::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Gst Registration Type: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_gst_registration_type'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $data) {
                $id = Crypt::decryptString($id);
                $registerType = GstRegistrationType::findOrFail($id);
                $changes = $registerType->getChangedAttributesFromRequest($data);
                $registerType->update($data);
                logActivity('Updated', $registerType, [$changes]);
                return $registerType;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to update Gst Registration Type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_gst_registration_type'), 403, 'Unauthorized');

        try {
            // return DB::transaction(function () use ($id) {
                $id = Crypt::decryptString($id);
                $registerType = GstRegistrationType::findOrFail($id);
                if ($registerType->vendors()->count() > 0) {
                    return response()->json([
                        'message' => 'Cannot delete gst registration type. It is assigned to one or more vendors.',
                    ], 400);
                }
                $registerType->delete();
                logActivity('Deleted', $registerType, [$registerType]);
                return true;
            // });
        } catch (Exception $e) {
            throw new Exception('Failed to delete Gst Registration Type: ' . $e->getMessage());
        }
    }


    public function list(){
        return GstRegistrationType::get();
    }
}
