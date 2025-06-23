<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\AttributeRequest;
use App\Services\Masters\AttributeService;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function index(Request $request)
    {
        return $this->attributeService->index($request);
    }

    public function store(AttributeRequest $request)
    {
        return $this->attributeService->store($request->validated());
    }

    public function show($id)
    {
        return $this->attributeService->show($id);
    }

    public function update(AttributeRequest $request, $id)
    {
        return $this->attributeService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->attributeService->destroy($id);
    }


    public function list(){
        return $this->attributeService->list();
    }
}
