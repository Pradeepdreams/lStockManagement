<?php


namespace App\Services\Masters;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Models\CategoryGstApplicable;
use App\Models\CategoryHsnApplicable;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_category'), 403, 'Unauthorized');

        $search = $request->search ?? null;
        $categoryQuery = Category::query();
        if ($search) {
            $categoryQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }
        $categories =  $categoryQuery->with('latestGstPercent', 'latestHsnCode')->latest()->paginate(10);
        // $categories =  Category::with('attributes.attribute_values','activeGstPercent', 'activeHsnCode')->latest()->paginate(10);
        $getLinks = $categories->jsonSerialize();

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
            'categories' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_category'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $category = Category::create($data);
            // $gstApplicable = [
            //     "category_id" => $category->id,
            //     "gst_percent" => $data['gst_percent'],
            //     "applicable_date" => isset($data['gst_applicable_date'])
            //         ? Carbon::parse($data['gst_applicable_date'])->format('Y-m-d')
            //         : null
            // ];

            // $gstCreate = CategoryGstApplicable::create($gstApplicable);

            // $hsnApplicable = [
            //     "category_id" => $category->id,
            //     "hsn_code" => $data['hsn_code'],
            //     "applicable_date" => isset($data['hsn_applicable_date'])
            //         ? Carbon::parse($data['hsn_applicable_date'])->format('Y-m-d')
            //         : null
            // ];

            // $hsnCreate = CategoryHsnApplicable::create($hsnApplicable);

            logActivity('Created', $category, [$category]);

            DB::commit();

            return $category;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating Category: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_category'), 403, 'Unauthorized');

        try {
            // return $encryptedId;
            $id = Crypt::decryptString($encryptedId);
            return Category::with('latestGstPercent', 'latestHsnCode')->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching Category: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update(array $data, string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_category'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $category = Category::findOrFail($id);

            $changes = $category->getChangedAttributesFromRequest($data);
            $category->update($data);


            logActivity('Updated', $category, $changes);

            DB::commit();
            return $category->load('attributes');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating Category: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_category'), 403, 'Unauthorized');

        try {
            // DB::beginTransaction();

            $id = Crypt::decryptString($encryptedId);
            $category = Category::findOrFail($id);
            if ($category->items()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete category. It is assigned to one or more items.',
                ], 400);
            }
            $category->delete();
            logActivity('Deleted', $category, [$category]);

            // DB::commit();
            return response()->json(['message' => 'Deleted successfully.']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting Category: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }



    public function list()
    {
        return Category::latest()->get();
    }


    public function getGstHistory($request)
    {

        $id = Crypt::decryptString($request->id);

        $categoriesGst = CategoryGstApplicable::where('item_id', $id)->paginate(10);

        $getLinks = $categoriesGst->jsonSerialize();

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
            'gst_history' => $getLinks
        ]);

        //  return $categoriesGst;

    }


    public function getHsnHistory($request)
    {

        $id = Crypt::decryptString($request->id);

        $categoriesHsn = CategoryHsnApplicable::where('item_id', $id)->paginate(10);

        $getLinks = $categoriesHsn->jsonSerialize();

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
            'hsn_history' => $getLinks
        ]);

        //  return $categoriesHsn;

    }
}
