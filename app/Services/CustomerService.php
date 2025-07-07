<?php

namespace App\Services;

use App\Models\Customer;
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
    public function store($data)
    {
        return DB::transaction(function () use ($data) {
            return Customer::create($data);
        });
    }


    public function show($id)
    {
        $id = Crypt::decryptString($id);
        $customer = Customer::findOrFail($id);
        return $customer;
    }

    // Update an existing customer
    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($id, $data) {
            $id = Crypt::decryptString($id);
            $customer = Customer::findOrFail($id);
            $customer->update($data);
            return $customer;
        });
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
