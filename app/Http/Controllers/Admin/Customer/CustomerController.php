<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }

    // GET /api/customers
    public function index()
    {
        return $customers = $this->service->index();
    }

    // POST /api/customers
    public function store(CustomerRequest $request)
    {
        return $this->service->create($request->validated());
    }

    // GET /api/customers/{customer}
    public function show($id)
    {
        return  $this->service->show($id);
    }

    // PUT/PATCH /api/customers/{customer}
    public function update(CustomerRequest $request, $id)
    {
        return $this->service->update($request->validated(), $id);
    }

    // DELETE /api/customers/{customer}
    public function destroy(Customer $customer)
    {
        return $this->service->destroy($customer);
    }
}
