<?php

namespace App\Services\Masters;

use App\Models\State;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StateService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_state'), 403, 'Unauthorized');

        $country_id = $request->input('country_id');

        if ($country_id) {
            return State::with('country')->where('country_id', $country_id)->latest()->get();
        }

       $state = State::with('country')->latest()->paginate(10);
        $getLinks = $state->jsonSerialize();

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
            'states' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_state'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $state = State::create($data);
            logActivity('Created', $state, [$state]);

            DB::commit();
            return $state;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating State: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_state'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return State::with('country')->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching State: ' . $e->getMessage());
            // return response()->json(['message' => 'State not found.'], 404);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_state'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $state = State::findOrFail($id);
            $changes = $state->getChangedAttributesFromRequest($data);
            $state->update($data);
            logActivity('Updated', $state, $changes);

            DB::commit();
            return $state;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating State: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_state'), 403, 'Unauthorized');

        try {

            // DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $state = State::findOrFail($id);
            $state->delete();
            logActivity('Deleted', $state, [$state]);

            // DB::commit();
            return response()->json(['message' => 'State deleted successfully']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting State: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list($request){
        $country_id = $request->input('country_id');

        if ($country_id) {
            return State::with('country')->where('country_id', $country_id)->get();
        }
        return State::get();
    }
}
