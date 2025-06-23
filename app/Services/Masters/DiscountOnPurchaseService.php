<?php

namespace App\Services\Masters;

use App\Models\DiscountOnPurchase;
use App\Models\FeatureFlags;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;

class DiscountOnPurchaseService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_purchase_discount'), 403, 'Unauthorized');

        try {
            $search = $request->search ?? null;

            $discount = DiscountOnPurchase::query();

            if ($search) {

                $discount->where(function ($query) use ($search) {
                    $query->where('discount_type', 'like', '%' . $search . '%')
                        ->orWhere('discount_percent', 'like', '%' . $search . '%');
                });
            }
            $discounts = $discount->latest()->paginate(10);

            $getLinks = $discounts->jsonSerialize();

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
                'discounts' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list ledger group: ' . $e->getMessage());
        }
    }


    public function store($request)
    {

        abort_unless(auth()->user()->hasBranchPermission('create_purchase_discount'), 403, 'Unauthorized');

        $discount = DiscountOnPurchase::create($request);
        logActivity('Created', $discount, [$discount]);
        return response()->json([
            'success' => true,
            'data' => $discount
        ]);
    }


    public function show($id)
    {

        abort_unless(auth()->user()->hasBranchPermission('view_purchase_discount'), 403, 'Unauthorized');

        $id = Crypt::decryptString($id);
        return DiscountOnPurchase::findOrFail($id);
    }


    public function update($id, $request)
    {

        abort_unless(auth()->user()->hasBranchPermission('update_purchase_discount'), 403, 'Unauthorized');

        $id = Crypt::decryptString($id);
        $discount = DiscountOnPurchase::findOrFail($id);
        $changes = $discount->getChangedAttributesFromRequest($request);
        $discount->update($request);
        logActivity('Updated', $discount, $changes);
        return response()->json([
            'success' => true,
            'data' => $discount
        ]);
    }


    public function destroy($id)
    {

        abort_unless(auth()->user()->hasBranchPermission('delete_purchase_discount'), 403, 'Unauthorized');

        $id = Crypt::decryptString($id);
        $discount = DiscountOnPurchase::findOrFail($id);
        $discount->delete();
        logActivity('Deleted', $discount, [$discount]);
        return response()->json([
            'message' => "Discount Deleted Successfully",
        ]);
    }


    public function changeDiscountType()
    {
        $specialDiscount = FeatureFlags::where('name', 'purchase_discount')->first();
        if ($specialDiscount->active_status) {
            $specialDiscount->active_status = false;
            $specialDiscount->save();
        } else {
            $specialDiscount->active_status = true;
            $specialDiscount->save();
        }
        return $specialDiscount;
    }

    public function discountPercent()
    {
        $specialDiscount = FeatureFlags::where('name', 'purchase_discount')->first();

        if ($specialDiscount->active_status) {
            return DiscountOnPurchase::where('discount_type', 'Special')
                ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
                ->orderByDesc('applicable_date')->first();
        } else {
            //  return $specialDiscount;
            return DiscountOnPurchase::where('discount_type', 'Normal')
                ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
                ->orderByDesc('applicable_date')->first();
        }
    }


    public function getDiscountType(){
        $specialDiscount = FeatureFlags::where('name', 'purchase_discount')->first();
        return $specialDiscount;
    }
}
