<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;

class CustomerService
{
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return $customers;
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
