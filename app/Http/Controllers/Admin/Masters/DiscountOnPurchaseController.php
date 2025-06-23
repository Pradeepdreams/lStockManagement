<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\DiscountOnPurchaseRequest;
use App\Services\Masters\DiscountOnPurchaseService;
use Illuminate\Http\Request;

class DiscountOnPurchaseController extends Controller
{
     protected $service;

    public function __construct(DiscountOnPurchaseService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
        // return response()->json(['data' => Group::with('parent')->get()]);
    }

    public function store(DiscountOnPurchaseRequest $request)
    {
        return $this->service->store($request->validated());
        // return response()->json(['data' => $group]);
    }

    public function show($id)
    {
        return $this->service->show($id);
        // return response()->json(['data' => $group]);
    }


    public function update(DiscountOnPurchaseRequest $request, $id)
    {
       return $this->service->update($id, $request->validated());
        // return response()->json(['data' => $group]);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    public function changeDiscountType(){
        return $this->service->changeDiscountType();
    }

    public function discountPercent(){
        return $this->service->discountPercent();
    }

    public function getDiscountType(){
        return $this->service->getDiscountType();
    }
}
