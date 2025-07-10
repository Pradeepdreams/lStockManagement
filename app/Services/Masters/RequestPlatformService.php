<?php


namespace App\Services\Masters;

use App\Models\RequestPlatform;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestPlatformService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_request_platform'), 403, 'Unauthorized');

        try {
            $search = $request->search ?? null;

            $request_platformQuery = RequestPlatform::query();

            if ($search) {
                $request_platformQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }
            $request_platform = $request_platformQuery->latest()->paginate(10);

            $getLinks = $request_platform->jsonSerialize();

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
                'request_platforms' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list Platforms: ' . $e->getMessage());
        }
    }

    public function store($data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_request_platform'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($data) {
                $request_platform = RequestPlatform::create($data);
                logActivity('Created', $request_platform, [$request_platform]);
                return $request_platform;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to create Platform: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_request_platform'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return RequestPlatform::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Platform: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_request_platform'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $data) {
                $id = Crypt::decryptString($id);
                $request_platform = RequestPlatform::findOrFail($id);
                $changes = $request_platform->getChangedAttributesFromRequest($data);
                $request_platform->update($data);
                logActivity('Updated', $request_platform, [$changes]);
                return $request_platform;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to update Platform: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_request_platform'), 403, 'Unauthorized');

        try {
            // return DB::transaction(function () use ($id) {
            $id = Crypt::decryptString($id);
            $request_platform = RequestPlatform::findOrFail($id);
            if ($request_platform->employees()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete Platform. It is assigned to one or more employees.',
                ], 400);
            }
            $request_platform->delete();
            logActivity('Deleted', $request_platform, [$request_platform]);
            return true;
            // });
        } catch (Exception $e) {
            throw new Exception('Failed to delete Platform: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return RequestPlatform::get();
    }
}
