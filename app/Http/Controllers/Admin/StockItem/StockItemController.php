<?php

namespace App\Http\Controllers\Admin\StockItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockItemRequest;
use App\Services\StockItemService;
use Illuminate\Http\Request;

class StockItemController extends Controller
{
    protected StockItemService $service;

    public function __construct(StockItemService $service)
    {
        $this->service = $service;
    }


    public function store(StockItemRequest $request){
         return $this->service->store($request);
    }

    public function getByBarcode($barcode){
        return $this->service->getByBarcode($barcode);
    }
}
