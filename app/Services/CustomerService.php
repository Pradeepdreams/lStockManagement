<?php

namespace App\Services;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;

class CustomerService
{
    public function index($request)
    {

        $search = $request->search ?? null;
        $customers = Customer::query(); // latest()->paginate(10);

        if ($search) {
            $customers->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like',  $search . '%')
                    ->orWhere('phone', 'like',  $search . '%');
            });
        }

        $customers = $customers->latest()->paginate(10);

        $getLinks = $customers->toArray();

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
            'customers' => $getLinks
        ]);
        // return $customers;
    }


    // Create a new customer (wrapped in DB transaction)
    // public function store($data)
    // {
    //     return DB::transaction(function () use ($data) {
    //         return Customer::create($data);
    //     });
    // }

    public function store($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_customers'), 403, 'Unauthorized');

        return DB::transaction(function () use ($request) {
            $userId = auth()->user()->id;

            // Restore if soft-deleted customer matches any of the key fields
            $existingCustomer = Customer::onlyTrashed()
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name)
                        ->orWhere('gst_in', $request->gst_in)
                        ->orWhere('phone_no', $request->phone)
                        ->orWhere('email', $request->email);
                })->first();

            if ($existingCustomer) {
                $existingCustomer->restore();

                $changes = $existingCustomer->getChangedAttributesFromRequest($request->all());

                $existingCustomer['updated_by'] = $userId;

                $customerData = array_map(function ($value) {
                    return $value === '' ? null : $value;
                }, $request->only([
                    'name',
                    'group_id',
                    'gst_in',
                    'pan_number',
                    'phone',
                    'customer_type',
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
                    'upi_id',
                ]));

                $customerData['created_by'] = $userId;
                $customerData['gst_applicable'] = $request->gst_applicable === "yes";
                $customerData['gst_applicable_from'] = $request->gst_applicable_from !== ""
                    ? Carbon::parse($request->gst_applicable_from)->format('Y-m-d')
                    : null;

                $customerData['group_id'] = $request->group_id ?: null;
                $customerData['phone_no'] = $request->phone ?: null;
                $customerData['payment_term_id'] = $request->payment_term_id ?: null;
                $customerData['pincode'] = $request->pincode ?: null;
                $customerData['tds_detail_id'] = $request->tds_detail_id ?: null;
                $customerData['credit_days'] = $request->credit_days ?: null;
                $customerData['gst_registration_type_id'] = $request->gst_registration_type_id ?: null;

                $existingCustomer->update($customerData);

                logActivity('Updated', $existingCustomer, [$changes]);

                if ($request->has('customer_contact_details')) {
                    $existingCustomer->customerContactDetails()->delete();
                    foreach ($request['customer_contact_details'] as $contact) {
                        $existingCustomer->customerContactDetails()->create($contact);
                    }
                }

                // if ($request->has('customer_upi')) {
                //     $existingCustomer->customerUpi()->delete();
                //     foreach ($request->customer_upi as $upi) {
                //         $existingCustomer->customerUpi()->create($upi);
                //     }
                // }

                return $existingCustomer->load(['customerContactDetails', 'customerUpi']);
            }

            // If not existing, create new
            $customerData = array_map(function ($value) {
                return $value === '' ? null : $value;
            }, $request->only([
                'name',
                'group_id',
                'gst_in',
                'pan_number',
                'phone',
                'customer_type',
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
                'upi_id',
            ]));

            $customerData['created_by'] = $userId;
            $customerData['gst_applicable'] = $request->gst_applicable === "yes" ? true : false;
            $customerData['gst_applicable_from'] = $request->gst_applicable_from !== ""
                ? Carbon::parse($request->gst_applicable_from)->format('Y-m-d')
                : null;

            $customerData['phone_no'] = $request->phone ?: null;
            $customerData['group_id'] = $request->group_id ?: null;
            $customerData['payment_term_id'] = $request->payment_term_id ?: null;
            $customerData['pincode'] = isset($request->pincode) ? (string) $request->pincode : null;
            $customerData['tds_detail_id'] = $request->tds_detail_id ?: null;
            $customerData['credit_days'] = $request->credit_days ?: null;
            $customerData['gst_registration_type_id'] = $request->gst_registration_type_id ?: null;

            $customer = Customer::create($customerData);



            if ($request->has('customer_contact_details')) {
                foreach ($request['customer_contact_details'] as $contact) {
                    $customer->customerContactDetails()->create($contact);
                }
            }

            // if ($request->has('customer_upi') && is_array($request->customer_upi)) {
            //     foreach ($request->customer_upi as $upi) {
            //         $customer->customerUpi()->create($upi);
            //     }
            // }

            logActivity('Created', $customer, [$customer]);

            return $customer->load(['customerContactDetails', 'customerUpi']);
        });
    }


    public function show($id)
    {
        $id = Crypt::decryptString($id);
        $customer = Customer::with('customerContactDetails', 'customerUpi')->findOrFail($id);
        return $customer;
    }

    // Update an existing customer
    // public function update(array $data, $id)
    // {
    //     return DB::transaction(function () use ($id, $data) {
    //         $id = Crypt::decryptString($id);
    //         $customer = Customer::findOrFail($id);
    //         $customer->update($data);
    //         return $customer;
    //     });
    // }


    public function update(CustomerRequest $request, $id)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_customers'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($id, $request) {

                $id = Crypt::decryptString($id);
                $customer = Customer::withTrashed()->findOrFail($id);

                if ($customer->trashed()) {
                    $customer->restore();
                    logActivity('Restored', $customer, []);
                }

                $changes = $customer->getChangedAttributesFromRequest($request->all());

                $customerData = [
                    'name' => $request->name,
                    'group_id' => $request->group_id ?: null,
                    'gst_in' => $request->gst_in,
                    'pan_number' => $request->pan_number,
                    'phone_no' => $request->phone,
                    'customer_type' => $request->customer_type,
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
                ];

                if ($customerData['gst_applicable']) {
                    $customerData['gst_applicable_from'] = $request->gst_applicable_from
                        ? Carbon::parse($request->gst_applicable_from)->format('Y-m-d')
                        : null;
                    $customerData['gst_registration_type_id'] = $request->gst_registration_type_id ?: null;
                } else {
                    $customerData['gst_applicable_from'] = null;
                    $customerData['gst_registration_type_id'] = null;
                    $customerData['gst_in'] = null;
                }

                $customer->update($customerData);

                // Sync customer contact details
                if ($request->has('customer_contact_details')) {
                    $customer->customerContactDetails()->delete();
                    foreach ($request['customer_contact_details'] as $contact) {
                        $customer->customerContactDetails()->create($contact);
                    }
                }

                // Sync customer UPI
                if ($request->has('customer_upi')) {
                    $customer->customerUpi()->delete();
                    foreach ($request->customer_upi as $upi) {
                        $customer->customerUpi()->create($upi);
                    }
                }

                logActivity('Updated', $customer, [$changes]);

                return response()->json([
                    "message" => "Customer updated successfully.",
                    "customer" => $customer->load(['customerContactDetails']),
                ]);
            });
        } catch (Exception $e) {
            throw new Exception("Failed to update customer: " . $e->getMessage());
        }
    }



    // Delete a customer
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $id = Crypt::decryptString($id);
            $customer = Customer::findOrFail($id);
            $customer->delete();
        });
    }
}
