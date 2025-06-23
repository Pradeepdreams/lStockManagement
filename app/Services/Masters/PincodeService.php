<?php

namespace App\Services\Masters;

use App\Models\Pincode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PincodeService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_pincode'), 403, 'Unauthorized');

        $search = $request->search;

        $pincodeQuery = Pincode::query();

        if ($search) {
            $pincodeQuery->where(function ($query) use ($search) {
                $query->where('pincode', 'like', '%' . $search . '%');
            });
        }

        $pincodes = $pincodeQuery->latest()->paginate(10);

        $getLinks = $pincodes->jsonSerialize();

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
            'pincodes' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_pincode'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $pincode = Pincode::create($data);
            logActivity('Created', $pincode, [$pincode]);

            DB::commit();
            return $pincode;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating pincode: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_pincode'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return Pincode::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Pincode: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_pincode'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $id = Crypt::decryptString($id);
            $pincode = Pincode::findOrFail($id);
            $changes = $pincode->getChangedAttributesFromRequest($data);
            $pincode->update($data);
            logActivity('Updated', $pincode, $changes);

            DB::commit();
            return $pincode;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating pincode: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_pincode'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();

            $id = Crypt::decryptString($id);
            $pincode = Pincode::findOrFail($id);
            if ($pincode->vendors()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete pincode. It is assigned to one or more vendors.',
                ], 400);
            }
            $pincode->delete();
            logActivity('Deleted', $pincode, [$pincode]);

            // DB::commit();
            return response()->json(['message' => 'Deleted successfully']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting pincode: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }



    public function list()
    {
        return Pincode::get();
    }
}
