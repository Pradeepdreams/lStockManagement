<?php

namespace App\Services\Masters;

use App\Models\Attribute;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_attributes'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $attributeQuery = Attribute::query();

        if ($search) {
            $attributeQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $attributes = $attributeQuery->with('categories')->latest()->paginate(10);

        $getLinks = $attributes->jsonSerialize();

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
            'attributes' => $getLinks
        ]);
    }


    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_attributes'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $attribute = Attribute::create($data);
            logActivity('Created', $attribute, [$attribute]);

            DB::commit();

            return $attribute;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating Attribute: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function show(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_attributes'), 403, 'Unauthorized');

        try {

            $id = Crypt::decryptString($encryptedId);
            return Attribute::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Attribute: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }



    public function update(string $encryptedId, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_attributes'), 403, 'Unauthorized');

        try {

            DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $attribute = Attribute::findOrFail($id);
            $changes = $attribute->getChangedAttributesFromRequest($data);
            $attribute->update($data);
            logActivity('Updated', $attribute, $changes);

            DB::commit();

            return $attribute;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating Attribute: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function destroy(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_attributes'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $attribute = Attribute::findOrFail($id);
            if ($attribute->categories()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete attribute. It is assigned to one or more categories.',
                ], 400);
            }
            $attribute->delete();
            logActivity('Deleted', $attribute, [$attribute]);

            // DB::commit();

            return response()->json(['message' => 'Attribute deleted successfully.']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting Attribute: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return Attribute::orderBy('name')->get();
    }
}
