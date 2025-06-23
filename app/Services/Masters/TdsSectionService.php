<?php

namespace App\Services\Masters;

use App\Models\TdsSection;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TdsSectionService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_tds_sections'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $tdsQuery = TdsSection::query();

        if ($search) {
            $tdsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $tdsSections = $tdsQuery->with('tdsDetails')->latest()->paginate(10);

        $getLinks = $tdsSections->jsonSerialize();

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
            'tds_sections' => $getLinks
        ]);
    }


    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_tds_sections'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $tdsSection = TdsSection::create($data);
            logActivity('Created', $tdsSection, [$tdsSection]);

            DB::commit();

            return $tdsSection;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating TDS Section: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function show(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_tds_sections'), 403, 'Unauthorized');

        try {

            $id = Crypt::decryptString($encryptedId);
            return TdsSection::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching TDS Section: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }



    public function update(string $encryptedId, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_tds_sections'), 403, 'Unauthorized');

        try {

            DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $tdsSection = TdsSection::findOrFail($id);
            $changes = $tdsSection->getChangedAttributesFromRequest($data);
            $tdsSection->update($data);
            logActivity('Updated', $tdsSection, $changes);

            DB::commit();

            return $tdsSection;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating TDS Section: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function destroy(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_tds_sections'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $tdsSection = TdsSection::findOrFail($id);
            if ($tdsSection->tdsDetails()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete tds sections. It is assigned to one or more tds details.',
                ], 400);
            }
            $tdsSection->delete();
            logActivity('Deleted', $tdsSection, [$tdsSection]);

            // DB::commit();

            return response()->json(['message' => 'Tds Section deleted successfully.']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting Tds Section: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return TdsSection::get();
    }
}
