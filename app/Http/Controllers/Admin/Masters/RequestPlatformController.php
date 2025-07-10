<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\RequestPlatformRequest;
use App\Services\Masters\RequestPlatformService;
use Illuminate\Http\Request;

class RequestPlatformController extends Controller
{
    protected $service;

    public function __construct(RequestPlatformService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(RequestPlatformRequest $request)
    {
        $platform = $this->service->store($request->validated());
        return response()->json(['data' => $platform]);
    }

    public function show($id)
    {
        $platform = $this->service->show($id);
        return response()->json(['data' => $platform]);
    }


    public function update(RequestPlatformRequest $request, $id)
    {
        $platform = $this->service->update($id, $request->validated());
        return response()->json(['data' => $platform]);
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
