<?php

namespace App\Services\Masters;

use App\Models\TdsDetail;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TdsDetailService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_tds_details'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $tdsQuery = TdsDetail::query();

        if ($search) {
            $tdsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }
        $tds =  $tdsQuery->with('tdsSection')->latest()->paginate(10);
        $getLinks = $tds->jsonSerialize();
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
            'tds_details' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_tds_details'), 403, 'Unauthorized');
        try {
            return DB::transaction(function () use ($data) {
                $tdsDetail = TdsDetail::create($data);
                logActivity('Created', $tdsDetail, [$tdsDetail]);
                return $tdsDetail;
            });
        } catch (Exception $e) {
            DB::rollBack();
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_tds_details'), 403, 'Unauthorized');

        $id = Crypt::decryptString($id);
        return TdsDetail::with('tdsSection')->findOrFail($id);
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_tds_details'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $data) {
                $id = Crypt::decryptString($id);
                $tds = TdsDetail::findOrFail($id);
                if (isset($data['active_status']) && !$data['active_status']) {

                    if ($tds->vendors()->exists()) {
                        throw new Exception('Can\'t deactivate the TDS Detail because it is linked with other modules.');
                    }
                }
                $changes = $tds->getChangedAttributesFromRequest($data);
                $tds->update($data);
                logActivity('Updated', $tds, $changes);
                return $tds;
            });
        } catch (Exception $e) {
            DB::rollBack();
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_tds_details'), 403, 'Unauthorized');

        try {
            // DB::transaction(function () use ($id) {
            $id = Crypt::decryptString($id);
            $tds = TdsDetail::findOrFail($id);
            if ($tds->vendors()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete tds details. It is assigned to one or more vendors.',
                ], 400);
            }
            $tds->delete();
            logActivity('Deleted', $tds, [$tds]);
            // });
        } catch (Exception $e) {
            DB::rollBack();
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return TdsDetail::with('tdsSection')->get();
    }
}
