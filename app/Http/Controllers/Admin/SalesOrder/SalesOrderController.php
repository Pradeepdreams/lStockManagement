<?php

namespace App\Http\Controllers\Admin\SalesOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Services\SalesOrderService;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    protected $service;

    public function __construct(SalesOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(SalesOrderRequest  $request)
    {
        return $this->service->store($request->validate());
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function update(SalesOrderRequest  $request, $id)
    {
        return $this->service->update($request->validate(), $id);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    public function list()
    {
        return $this->service->list();
    }

    public function salesInvoiceList()
    {
        return $this->service->salesInvoiceList();
    }

    public function latestPo()
    {
        return $this->service->latestPo();
    }
}
