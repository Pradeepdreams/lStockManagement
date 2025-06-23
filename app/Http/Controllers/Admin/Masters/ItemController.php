<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\ItemRequest;
use App\Services\Masters\ItemService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected $service;

    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return response()->json($this->service->index($request));
    }

    public function store(ItemRequest $request)
    {
        return response()->json($this->service->store($request), 201);
    }

    public function show(string $id, Request $request)
    {
        return response()->json($this->service->show($id, $request));
    }

    public function update(ItemRequest $request, string $id)
    {
        return response()->json($this->service->update($request, $id));
    }

    public function destroy(string $id)
    {
        return $this->service->destroy($id);
    }


    public function getItemAttributeValues(Request $request)
    {

        return $this->service->getItemAttributeValues($request);
    }

    public function list(Request $request)
    {
        return $this->service->list($request);
    }

    public function poList(string $id, Request $request)
    {
        return $this->service->poList($id, $request);
    }
}
