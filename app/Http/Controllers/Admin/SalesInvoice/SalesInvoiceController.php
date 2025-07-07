<?php

namespace App\Http\Controllers\Admin\SalesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesInvoiceRequest;
use App\Services\SalesInvoiceService;
use Illuminate\Http\Request;

class SalesInvoiceController extends Controller
{
    protected $service;

    public function __construct(SalesInvoiceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(SalesInvoiceRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function update(SalesInvoiceRequest $request, $id)
    {
        return $this->service->update($request->validated(), $id);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    public function latestEntry()
    {
        return $this->service->latestEntry();
    }
}
