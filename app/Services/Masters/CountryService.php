<?php

namespace App\Services\Masters;

use App\Models\Country;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountryService
{
    public function index()
    {
        abort_unless(auth()->user()->hasBranchPermission('view_country'), 403, 'Unauthorized');

        $countries = Country::latest()->paginate(10);

        $getLinks = $countries->jsonSerialize();

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
            'countries' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_country'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $country = Country::create($data);
            logActivity('Created', $country, [$country]);

            DB::commit();
            return $country;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating Country: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_country'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return Country::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Country: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_country'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $id = Crypt::decryptString($id);
            $country = Country::findOrFail($id);
            $changes = $country->getChangedAttributesFromRequest($data);
            $country->update($data);
            logActivity('Updated', $country, $changes);

            DB::commit();
            return $country;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating Country: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_country'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();

            $id = Crypt::decryptString($id);
            $country = Country::findOrFail($id);
            $country->delete();
            logActivity('Deleted', $country, [$country]);

            // DB::commit();
            return response()->json(['message' => 'Deleted successfully']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting Country: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }



    public function list(){
        return Country::get();
    }
}
