<?php

namespace App\Services\Masters;

use App\Models\Area;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_area'), 403, 'Unauthorized');

        $search = $request->search ?? null;
        $areaQuery = Area::query();

        if ($search) {
            $areaQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')->orWhere('area_code', 'like', '%' . $search . '%');
            });
        }
        $areas = $areaQuery->latest()->paginate(10);
        $getLinks = $areas->jsonSerialize();

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
            'areas' => $getLinks
        ]);
    }

    public function store(array $data)
    {

        abort_unless(auth()->user()->hasBranchPermission('create_area'), 403, 'Unauthorized');
        try {
            DB::beginTransaction();

            $data['area_code'] = $this->generateAreaCode();
            $area = Area::create($data);
            logActivity('Created', $area, [$area]);

            DB::commit();

            return $area;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating Area: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($request, string $encryptedId)
    {

        if (!$request->po_flag) {
            abort_unless(auth()->user()->hasBranchPermission('view_area'), 403, 'Unauthorized');
        }

        try {

            $id = Crypt::decryptString($encryptedId);
            return Area::with('vendors')->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Area: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update(string $encryptedId, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_area'), 403, 'Unauthorized');

        try {

            DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $area = Area::findOrFail($id);
            $changes = $area->getChangedAttributesFromRequest($data);
            $area->update($data);
            logActivity('Updated', $area, $changes);

            DB::commit();

            return $area;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating Area: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy(string $encryptedId)
    {

        abort_unless(auth()->user()->hasBranchPermission('delete_area'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $area = Area::findOrFail($id);
            if ($area->vendors()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete area. It is assigned to one or more vendors.',
                ], 400);
            }
            $area->delete();
            logActivity('Deleted', $area, [$area]);

            // DB::commit();

            return response()->json(['message' => 'Area deleted successfully.']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting Area: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public static function generateAreaCode()
    {

        $lastCode = Area::where('area_code', 'LIKE', 'AR-%')
            ->orderBy('area_code', 'desc')
            ->value('area_code');
        if ($lastCode) {
            $lastNumber = (int) str_replace('AR-', '', $lastCode);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'AR-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }



    public function list()
    {
        return Area::get();
    }
}
