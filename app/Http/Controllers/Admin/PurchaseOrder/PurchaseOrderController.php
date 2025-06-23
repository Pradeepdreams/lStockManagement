<?php

namespace App\Http\Controllers\Admin\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use Exception;
use Illuminate\Http\Request;


class PurchaseOrderController extends Controller
{
    protected PurchaseOrderService $service;

    public function __construct(PurchaseOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $po = $this->service->index($request);
            return $po;
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }


    public function store(PurchaseOrderRequest $request)
    {
        try {

            return $this->service->store($request);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to create Purchase Order. ' . $e->getMessage()], 500);
        }
    }


    public function show(string $id)
    {
        try {
            // $decryptedId = Crypt::decryptString($id);
            $po = $this->service->show($id);
            return response()->json($po);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch Purchase Order. ' . $e->getMessage()], 500);
        }
    }



     public function update(PurchaseOrderRequest $request, string $id)
    {
        try {
            // $decryptedId = Crypt::decryptString($id);
            $po = $this->service->update($request, $id);
            return response()->json($po);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update Purchase Order. ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            return $this->service->destroy($id);

        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete Purchase Order. ' . $e->getMessage()], 500);
        }
    }


    public function list(){
        return response()->json($this->service->list());
    }


    public function latestPo(){
        return response()->json($this->service->latestPo());
    }

    public function pendingPo(){
        return response()->json($this->service->pendingPo());
    }

    public function vendorPendingPo($id){
        return response()->json($this->service->vendorPendingPo($id));
    }


}
