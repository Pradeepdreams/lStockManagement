<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\VendorGroupRequest;
use App\Models\Vendor;
use App\Services\Masters\VendorGroupService;
use Illuminate\Http\Request;

class VendorGroupController extends Controller
{
    protected $service;

    public function __construct(VendorGroupService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
        // return response()->json(['data' => Group::with('parent')->get()]);
    }

    public function store(VendorGroupRequest $request)
    {
        $vendorGroup = $this->service->store($request->validated());
        return response()->json(['data' => $vendorGroup]);
    }

    public function show($id)
    {
        $vendorGroup = $this->service->show($id);
        return response()->json(['data' => $vendorGroup]);
    }


    public function update(VendorGroupRequest $request, $id)
    {
        $vendorGroup = $this->service->update($id, $request->validated());
        return response()->json(['data' => $vendorGroup]);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }


    public function list()
    {
        return $this->service->list();
    }
}
