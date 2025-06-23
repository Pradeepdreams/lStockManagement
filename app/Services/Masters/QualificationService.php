<?php

namespace App\Services\Masters;

use App\Models\Qualification;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualificationService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_qualification'), 403, 'Unauthorized');

        try {
            $search = $request->search ?? null;

            $qualificationQuery = Qualification::query();

            if ($search) {
                $qualificationQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            }
            $qualification = $qualificationQuery->latest()->paginate(10);

            $getLinks = $qualification->jsonSerialize();

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
                'qualifications' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list qualifications: ' . $e->getMessage());
        }
    }

    public function store($data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_qualification'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($data) {
                $qualification = Qualification::create($data);
                logActivity('Created', $qualification, [$qualification]);
                return $qualification;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to create qualification: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_qualification'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return Qualification::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching qualification: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_qualification'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $data) {
                $id = Crypt::decryptString($id);
                $qualification = Qualification::findOrFail($id);
                $changes = $qualification->getChangedAttributesFromRequest($data);
                $qualification->update($data);
                logActivity('Updated', $qualification, [$changes]);
                return $qualification;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to update qualification: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_qualification'), 403, 'Unauthorized');

        try {
            // return DB::transaction(function () use ($id) {
                $id = Crypt::decryptString($id);
                $qualification = Qualification::findOrFail($id);
                if ($qualification->employees()->count() > 0) {
                    return response()->json([
                        'message' => 'Cannot delete qualification. It is assigned to one or more employees.',
                    ], 400);
                }
                $qualification->delete();
                logActivity('Deleted', $qualification, [$qualification]);
                return true;
            // });
        } catch (Exception $e) {
            throw new Exception('Failed to delete qualification: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return Qualification::get();
    }
}
