<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\GstRegistrationTypeRequest;
use App\Services\Masters\GstRegistrationTypeService;
use Illuminate\Http\Request;

class GstRegistrationTypeController extends Controller
{
    protected $service;

    public function __construct(GstRegistrationTypeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
        // return response()->json(['data' => Group::with('parent')->get()]);
    }

    public function store(GstRegistrationTypeRequest $request)
    {
        $registerType = $this->service->store($request->validated());
        return response()->json(['data' => $registerType]);
    }

    public function show($id)
    {
        $registerType = $this->service->show($id);
        return response()->json(['data' => $registerType]);
    }


    public function update(GstRegistrationTypeRequest $request, $id)
    {
        $registerType = $this->service->update($id, $request->validated());
        return response()->json(['data' => $registerType]);
    }

    public function destroy($id)
    {
       return $this->service->destroy($id);
    }


    public function list(){
        return $this->service->list();
    }
}
