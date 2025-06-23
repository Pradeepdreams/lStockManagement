<?php

namespace App\Services\Masters;


use App\Models\City;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CityService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_city'), 403, 'Unauthorized');

        $state_id = $request->input('state_id');

        if ($state_id) {
            return City::with('state')->where('state_id', $state_id)->latest()->get();
        }

        $cities = City::with('state')->latest()->paginate(10);
        $getLinks = $cities->jsonSerialize();

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
            'cities' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_city'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $city = City::create($data);
            logActivity('Created', $city, [$city]);

            DB::commit();
            return $city;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating City: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_city'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return City::with('state')->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching City: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_city'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $city = City::findOrFail($id);
            $changes = $city->getChangedAttributesFromRequest($data);
            $city->update($data);
            logActivity('Updated', $city, $changes);

            DB::commit();
            return $city;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating City: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_city'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $city = City::findOrFail($id);
            $city->delete();
            logActivity('Deleted', $city, [$city]);

            // DB::commit();
            return response()->json(['message' => 'City deleted successfully']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting City: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list($request)
    {

        $state_id = $request->input('state_id');

        if ($state_id) {
            return City::with('state')->where('state_id', $state_id)->get();
        }
        return City::get();
    }
}
