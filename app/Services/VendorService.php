<?php

namespace App\Services;

use App\Http\Requests\VendorRequest;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\VendorContactDetail;
use App\Models\VendorReferredSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Exception;
use Illuminate\Http\Request;

class VendorService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_vendors'), 403, 'Unauthorized');

        try {

            $search = $request->search;
            $item = $request->item;
            $vendorCode = $request->vendor_code;
            $vendorName = $request->vendor_name;
            $areaId = $request->area_id;
            // $countryId = $request->country_id;
            // $stateId = $request->state_id;
            // $cityId = $request->city_id;
            $paymentTerm = $request->payment_term;
            $gstRegistrationType = $request->gst_registration_type_id;
            $groupId = $request->group_id;

            $vendors = Vendor::with('items');

            if ($search) {
                $vendors->where(function ($query) use ($search) {
                    $query->where('vendor_name', 'like', '%' . $search . '%')
                        ->orWhere('vendor_code', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone_no', 'like', '%' . $search . '%')
                        ->orWhere('gst_in', 'like', '%' . $search . '%');
                });
            }

            if ($vendorCode) {
                $vendors->where('vendor_code', $vendorCode);
            }

            if ($vendorName) {
                $vendors->where('vendor_name', 'like', "%$vendorName%");
            }

            if ($areaId) {
                $vendors->where('area_id', $areaId);
            }

            // if ($countryId) {
            //     $vendors->where('country_id', $countryId);
            // }

            // if ($stateId) {
            //     $vendors->where('state_id', $stateId);
            // }

            // if ($cityId) {
            //     $vendors->where('city_id', $cityId);
            // }

            if ($paymentTerm) {
                $vendors->where('payment_term_id', $paymentTerm);
            }

            if ($groupId) {
                $vendors->where('group_id', $groupId);
            }

            $vendors->with([
                'area',
                'vendorContactDetails',
                'vendorUpi',
                'referredSource',
                'referredSource.agent',
                'referredSource.user',
                'referredSource.vendor',
                'referredSource.socialMedia',
                'pincode',
                'gstRegistrationType',
                'tdsDetail',
                'group'
            ]);

            if ($item) {
                $vendors->whereHas('items', function ($query) use ($request) {
                    $query->where('items.id', $request->item);
                });
            }

            if ($gstRegistrationType) {
                $vendors->where('gst_registration_type_id', $gstRegistrationType);
            }

            $vendors = $vendors->latest()->paginate(10);

            $getLinks = $vendors->toArray();

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
                'vendors' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to fetch vendor: " . $e->getMessage());
        }
    }

    public function store(VendorRequest $request)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_vendors'), 403, 'Unauthorized');


        // return $request;
        // try {
        return DB::transaction(function () use ($request) {

            $userId = auth()->user()->id;
            $existingVendor = Vendor::onlyTrashed()
                ->where(function ($query) use ($request) {
                    $query->where('vendor_name', $request->vendor_name)
                        ->orWhere('gst_in', $request->gst_in)
                        ->orWhere('phone_no', $request->phone_no)
                        ->orWhere('email', $request->email);
                })
                ->first();

            // return $existingVendor;


            if ($existingVendor) {

                $existingVendor->restore();

                $changes = $existingVendor->getChangedAttributesFromRequest($request->all());

                $existingVendor['updated_by'] = $userId;

                $vendorData = array_map(function ($value) {
                    return $value === '' ? null : $value;
                }, $request->only([
                    'vendor_name',
                    'group_id',
                    'vendor_group_id',
                    'gst_in',
                    'pan_number',
                    'phone',
                    'email',
                    'address_line_1',
                    'address_line_2',
                    'area_id',
                    'city',
                    'state',
                    'country',
                    'pincode',
                    'payment_term_id',
                    'credit_days',
                    'credit_limit',
                    'gst_applicable',
                    'gst_applicable_from',
                    'gst_registration_type_id',
                    'tds_detail_id',
                    'bank_account_no',
                    'ifsc_code',
                    'bank_name',
                    'bank_branch_name',
                    'upi_id',
                    // 'transport_facility_provided',
                    // 'remarks'
                ]));

                // $vendorData['referred_source_id'] = $vendorReferredSource->id ?? null;
                $vendorData['created_by'] = $userId;
                $vendorData['gst_applicable'] = $request->gst_applicable == "yes" ? true : false;
                // $vendorData['transport_facility_provided'] = $request->gst_applicable == "yes" ? true : false;
                $vendorData['gst_applicable_from'] = $request->gst_applicable_from != ""
                    ? Carbon::parse($request->gst_applicable_from)->format('Y-m-d')
                    : null;

                $vendorData['group_id'] = $request->group_id == "" ? null : $request->group_id;
                $vendorData['phone_no'] = $request->phone == "" ? null : $request->phone;
                $vendorData['payment_term_id'] = $request->payment_term_id == "" ? null : $request->payment_term_id;
                $vendorData['pincode'] = $request->pincode == "" ? null : $request->pincode;
                $vendorData['tds_detail_id'] = $request->tds_detail_id == "" ? null : $request->tds_detail_id;
                $vendorData['credit_days'] = $request->credit_days == "" ? null : $request->credit_days;
                $vendorData['gst_registration_type_id'] = $request->gst_registration_type_id == "" ? null : $request->gst_registration_type_id;

                $existingVendor->update($vendorData);

                logActivity('Updated', $existingVendor, [$changes]);

                // $referredSourceType = $request->referred_source_type;
                // $referredSourceId = $request->referred_source_id;

                // if ($existingVendor->referred_source_type !== $referredSourceType || $existingVendor->referred_source_id != $referredSourceId) {

                //     $existingVendor->referredSource()->delete();
                //     logActivity('Deleted referred source', $existingVendor, [$existingVendor->referredSource]);

                //     $referredSourceData = [
                //         'agent_id' => null,
                //         'user_id' => null,
                //         'vendor_id' => null,
                //         'social_media_id' => null,
                //         'others' => null,
                //     ];

                //     switch ($referredSourceType) {
                //         case 'agent':
                //             $referredSourceData['agent_id'] = $referredSourceId;
                //             break;
                //         case 'user':
                //             $referredSourceData['user_id'] = $referredSourceId;
                //             break;
                //         case 'vendor':
                //             $referredSourceData['vendor_id'] = $referredSourceId;
                //             break;
                //         case 'social_media':
                //             $referredSourceData['social_media_id'] = $referredSourceId;
                //             break;
                //         case 'others':
                //             $referredSourceData['others'] = $request->others;
                //             break;
                //         default:
                //             // throw new Exception("Invalid referred_source_type provided.");
                //     }

                //     $vendorReferredSource = VendorReferredSource::create($referredSourceData);
                //     $existingVendor->update(['referred_source_id' => $vendorReferredSource->id]);

                //     logActivity('Created referred source', $existingVendor, [$vendorReferredSource]);
                // }


                if ($request->has('vendor_contact_details')) {

                    $existingVendor->vendorContactDetails()->delete();
                    foreach ($request->vendor_contact_details as $contact) {
                        $existingVendor->vendorContactDetails()->create($contact);
                    }
                }

                if ($request->has('vendor_upi')) {
                    $existingVendor->vendorUpi()->delete();

                    foreach ($request->vendor_upi as $upi) {
                        $existingVendor->vendorUpi()->create($upi);
                    }
                }

                // if ($request->has('items')) {
                //     $existingVendor->items()->sync($request->items);
                // }

                return $existingVendor->load(['vendorContactDetails', 'vendorUpi']);
            }

            $vendorData = array_map(function ($value) {
                return $value === '' ? null : $value;
            }, $request->only([
                'vendor_name',
                'group_id',
                'vendor_group_id',
                'gst_in',
                'pan_number',
                'phone',
                'email',
                'address_line_1',
                'address_line_2',
                'area_id',
                'city',
                'state',
                'country',
                'pincode',
                'payment_term_id',
                'credit_days',
                'credit_limit',
                'gst_applicable',
                'gst_applicable_from',
                'gst_registration_type_id',
                'tds_detail_id',
                'bank_account_no',
                'ifsc_code',
                'bank_name',
                'bank_branch_name',
                'upi_id',
                // 'transport_facility_provided',
                // 'remarks'
            ]));

            // $referredSourceType = $request->referred_source_type;
            // $vendorData['referred_source_type'] = $referredSourceType;

            // if ($referredSourceType != 'MD') {
            //     $referredSourceData = [
            //         'agent_id' => null,
            //         'user_id' => null,
            //         'vendor_id' => null,
            //         'social_media_id' => null,
            //         'others' => null,
            //     ];

            //     switch ($referredSourceType) {
            //         case 'agent':
            //             $referredSourceData['agent_id'] = $request->referred_source_id;
            //             break;
            //         case 'user':
            //             $referredSourceData['user_id'] = $request->referred_source_id;
            //             break;
            //         case 'vendor':
            //             $referredSourceData['vendor_id'] = $request->referred_source_id;
            //             break;
            //         case 'social_media':
            //             $referredSourceData['social_media_id'] = $request->referred_source_id;
            //             break;
            //         case 'others':
            //             $referredSourceData['others'] = $request->referred_source_id;
            //             break;
            //         default:
            //             // throw new Exception("Invalid referred_source_type provided.");
            //     }

            //     $vendorReferredSource = VendorReferredSource::create($referredSourceData);
            // }

            // $vendorData['referred_source_id'] = $vendorReferredSource->id ?? null;
            $vendorData['created_by'] = $userId;
            $vendorData['gst_applicable'] = $request->gst_applicable == "yes" ? true : false;
            // $vendorData['transport_facility_provided'] = $request->gst_applicable == "yes" ? true : false;
            $vendorData['gst_applicable_from'] = $request->gst_applicable_from != ""
                ? Carbon::parse($request->gst_applicable_from)->format('Y-m-d')
                : null;

            $vendorData['phone_no'] = $request->phone == "" ? null : $request->phone;
            $vendorData['group_id'] = $request->group_id == "" ? null : $request->group_id;
            $vendorData['payment_term_id'] = $request->payment_term_id == "" ? null : $request->payment_term_id;

            $vendorData['pincode'] = $request->pincode == "" ? null : $request->pincode;
            $vendorData['tds_detail_id'] = $request->tds_detail_id == "" ? null : $request->tds_detail_id;
            $vendorData['credit_days'] = $request->credit_days == "" ? null : $request->credit_days;
            $vendorData['gst_registration_type_id'] = $request->gst_registration_type_id == "" ? null : $request->gst_registration_type_id;

            $vendorData['vendor_code'] = $this->generateVendorCode();


            $vendor = Vendor::create($vendorData);

            logActivity('Created', $vendor, [$vendor]);

            if ($request->has('vendor_contact_details')) {
                foreach ($request->vendor_contact_details as $contact) {
                    $vendor->vendorContactDetails()->create($contact);
                }
            }

            if ($request->has('vendor_upi') && is_array($request->vendor_upi)) {
                foreach ($request->vendor_upi as $upi) {
                    $vendor->vendorUpi()->create($upi);
                }
            }

            // if ($request->has('items')) {
            //     $vendor->items()->sync($request->items);
            // }

            return $vendor->load(['vendorContactDetails', 'vendorUpi']);
        });
        // } catch (Exception $e) {
        //     throw new Exception("Failed to restore or create vendor: " . $e->getMessage());
        // }
    }




    public function show($encryptedId, $request)
    {
        if (!$request->po_flag) {
            abort_unless(auth()->user()->hasBranchPermission('view_vendors'), 403, 'Unauthorized');
        }

        $id = Crypt::decryptString($encryptedId);
        return Vendor::with(['vendorContactDetails', 'items.category.activeGstPercent', 'items.category.activeHsnCode', 'tdsDetail.tdsSection', 'pincode', 'vendorUpi'])->findOrFail($id);
    }


    public function update($id, VendorRequest $request)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_vendors'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $request) {

                $id = Crypt::decryptString($id);
                $vendor = Vendor::withTrashed()->findOrFail($id);

                if ($vendor->trashed()) {
                    $vendor->restore();
                    logActivity('Restored', $vendor, []);
                }

                $changes = $vendor->getChangedAttributesFromRequest($request->all());

                $vendorData = [
                    'vendor_name' => $request->vendor_name,
                    'group_id' => $request->group_id ?: null,
                    'vendor_group_id' => $request->vendor_group_id ?: null,
                    'gst_in' => $request->gst_in,
                    'pan_number' => $request->pan_number,
                    'phone_no' => $request->phone,
                    'email' => $request->email,
                    'address_line_1' => $request->address_line_1,
                    'address_line_2' => $request->address_line_2,
                    'area_id' => $request->area_id,
                    'city' => $request->city ?: null,
                    'state' => $request->state ?: null,
                    'country' => $request->country ?: null,
                    'pincode' => $request->pincode,
                    'payment_term_id' => $request->payment_term_id ?: null,
                    'credit_days' => $request->credit_days ?: null,
                    'credit_limit' => $request->credit_limit,
                    'gst_applicable' => $request->gst_applicable == "yes" ? true : false,
                    'tds_detail_id' => $request->tds_detail_id == "" ? null : $request->tds_detail_id,
                    'bank_account_no' => $request->bank_account_no,
                    'ifsc_code' => $request->ifsc_code,
                    'bank_name' => $request->bank_name,
                    'bank_branch_name' => $request->bank_branch_name,
                    // 'transport_facility_provided' => $request->gst_applicable == "yes" ? true : false,
                    // 'remarks' => $request->remarks,
                ];

                if ($vendorData['gst_applicable']) {
                    $vendorData['gst_applicable_from'] = $request->gst_applicable_from
                        ? Carbon::parse($request->gst_applicable_from)->format('Y-m-d')
                        : null;
                    $vendorData['gst_registration_type_id'] = $request->gst_registration_type_id ?: null;
                } else {
                    $vendorData['gst_applicable_from'] = null;
                    $vendorData['gst_registration_type_id'] = null;
                    $vendorData['gst_in'] = null;
                }

                $vendor->update($vendorData);

                // $referredSourceType = $request->referred_source_type;
                // $referredSourceId = $request->referred_source_id;

                // if ($vendor->referred_source_type !== $referredSourceType) {
                //     if ($vendor->referredSource) {
                //         $vendor->referredSource()->delete();
                //         logActivity('Deleted referred source', $vendor, [$vendor->referredSource]);
                //     }

                //     $referredSourceData = [
                //         'agent_id' => null,
                //         'user_id' => null,
                //         'vendor_id' => null,
                //         'social_media_id' => null,
                //         'others' => null,
                //     ];

                //     switch ($referredSourceType) {
                //         case 'agent':
                //             $referredSourceData['agent_id'] = $referredSourceId;
                //             break;
                //         case 'user':
                //             $referredSourceData['user_id'] = $referredSourceId;
                //             break;
                //         case 'vendor':
                //             $referredSourceData['vendor_id'] = $referredSourceId;
                //             break;
                //         case 'social_media':
                //             $referredSourceData['social_media_id'] = $referredSourceId;
                //             break;
                //         case 'others':
                //             $referredSourceData['others'] = $request->others;
                //             break;
                //         default:
                //             throw new Exception("Invalid referred_source_type provided.");
                //     }

                //     $vendorReferredSource = VendorReferredSource::create($referredSourceData);
                //     $vendor->update([
                //         'referred_source_type' => $referredSourceType,
                //         'referred_source_id' => $vendorReferredSource->id,
                //     ]);

                //     logActivity('Created referred source', $vendor, [$vendorReferredSource]);
                // }

                // Sync vendor contact details
                if ($request->has('vendor_contact_details')) {
                    $vendor->vendorContactDetails()->delete();
                    foreach ($request->vendor_contact_details as $contact) {
                        $vendor->vendorContactDetails()->create($contact);
                    }
                }

                // Sync vendor UPI
                if ($request->has('vendor_upi')) {
                    $vendor->vendorUpi()->delete();
                    foreach ($request->vendor_upi as $upi) {
                        $vendor->vendorUpi()->create($upi);
                    }
                }

                // Sync items
                // if ($request->has('items')) {
                //     $vendor->items()->sync($request->items);
                // }

                logActivity('Updated', $vendor, [$changes]);

                return response()->json([
                    "message" => "Vendor updated successfully.",
                    "vendor" => $vendor->load(['vendorContactDetails', 'items']),
                ]);
            });
        } catch (Exception $e) {
            throw new Exception("Failed to update vendor: " . $e->getMessage());
        }
    }


    public function destroy($encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_vendors'), 403, 'Unauthorized');

        try {
            // return DB::transaction(function () use ($encryptedId) {
            $id = Crypt::decryptString($encryptedId);
            $vendor = Vendor::findOrFail($id);
            $poExists = PurchaseOrder::where('vendor_id', $vendor->id)->exists();

            if ($poExists) {
                return response()->json([
                    'message' => 'Cannot delete vendor. It is assigned to one or more purchase orders.'
                ], 400);
            }
            $vendor->delete();
            logActivity('Deleted', $vendor, [$vendor]);
            return response()->json(['message' => 'Vendor deleted successfully.']);
            // });
        } catch (Exception $e) {
            throw new Exception("Failed to delete vendor: " . $e->getMessage());
        }
    }


    public static function generateVendorCode()
    {

        $lastCode = Vendor::where('vendor_code', 'LIKE', 'VN-%')
            ->orderBy('vendor_code', 'desc')
            ->value('vendor_code');
        if ($lastCode) {
            $lastNumber = (int) str_replace('VN-', '', $lastCode);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'VN-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }


    public function list()
    {
        return Vendor::get();
    }
}
