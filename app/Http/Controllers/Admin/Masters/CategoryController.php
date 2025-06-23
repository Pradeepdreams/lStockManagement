<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\CategoryRequest;
use App\Services\Masters\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(CategoryRequest $request)
    {
        // return $request;
        return $this->service->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->service->show($id);
    }

    public function update(CategoryRequest $request, string $id)
    {
        return $this->service->update($request->validated(), $id);
    }

    public function destroy(string $id)
    {
       $response = $this->service->destroy($id);
        return $response;
    }


    public function list(){
        return $this->service->list();
    }


    public function getGstHistory(Request $request){
        return $this->service->getGstHistory($request);
    }


     public function getHsnHistory(Request $request){
        return $this->service->getHsnHistory($request);
    }
}
