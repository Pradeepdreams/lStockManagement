<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\GroupRequest;
use App\Models\Group;
use App\Services\Masters\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $service;

    public function __construct(GroupService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
        // return response()->json(['data' => Group::with('parent')->get()]);
    }

    public function store(GroupRequest $request)
    {
        $group = $this->service->store($request->validated());
        return response()->json(['data' => $group]);
    }

    public function show($id)
    {
        $group = $this->service->show($id);
        return response()->json(['data' => $group]);
    }


    public function update(GroupRequest $request, $id)
    {
        $group = $this->service->update($id, $request->validated());
        return response()->json(['data' => $group]);
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
