<?php

namespace App\Services\Masters;

use App\Http\Requests\Masters\AttributeValueRequest;
use App\Models\AttributeValue;
use Attribute;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeValueService
{

    public function index($request)
    {

        abort_unless(auth()->user()->hasBranchPermission('view_attribute_values'), 403, 'Unauthorized');

        if ($request->has('attribute_id')) {
            $values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->latest()->paginate(10);
        } else {

            $search = $request->search ?? null;

            $attributeQuery = AttributeValue::query();

            if ($search) {
                $attributeQuery->where(function ($query) use ($search) {
                    $query->where('values', 'like', '%' . $search . '%');
                });
            }
            // $values = $attributeQuery->with('attribute')->orderBy('attribute_id', 'desc')->paginate(10);
            $values = $attributeQuery
                ->leftJoin('attributes', 'attribute_values.attribute_id', '=', 'attributes.id')
                ->select('attribute_values.*')
                ->orderBy('attributes.name', 'asc')
                ->with('attribute')
                ->paginate(10);
        }
        $getLinks = $values->jsonSerialize();

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
            'attribute_values' => $getLinks
        ]);
    }


    public function store(AttributeValueRequest $request)
    {

        abort_unless(auth()->user()->hasBranchPermission('create_attribute_values'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($request) {

                $attribute = AttributeValue::create($request->validated());
                logActivity('Created', $attribute, [$attribute]);
                return $attribute;
            });
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to create attribute value: " . $e->getMessage());
        }
    }

    public function show($encryptedId)
    {

        abort_unless(auth()->user()->hasBranchPermission('view_attribute_values'), 403, 'Unauthorized');

        try {

            $id = Crypt::decryptString($encryptedId);
            return AttributeValue::with('attribute')->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Attribute: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update(AttributeValueRequest $request, string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_attribute_values'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($request, $encryptedId) {
                $id = Crypt::decryptString($encryptedId);
                $attributeValue = AttributeValue::findOrFail($id);
                $attributeValue->update($request->validated());
                logActivity('Updated', $attributeValue, [$attributeValue]);
                return $attributeValue;
            });
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to update attribute value: " . $e->getMessage());
        }
    }

    public function destroy(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_attribute_values'), 403, 'Unauthorized');
        try {
            // return DB::transaction(function () use ($encryptedId) {
            $id = Crypt::decryptString($encryptedId);
            $attributeValue = AttributeValue::findOrFail($id);
            if ($attributeValue->attribute()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete sub attribute. It is assigned to one or more attributes.',
                ], 400);
            }
            $attributeValue->delete();
            logActivity('Deleted', $attributeValue, [$attributeValue]);
            return response()->json(['message' => 'Attribute Value Deleted successfully.']);
            // });
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to delete attribute value: " . $e->getMessage());
        }
    }



    public function list()
    {
        return AttributeValue::orderBy('values', 'asc')->get();
    }
}
