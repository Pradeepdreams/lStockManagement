<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\AgentRequest;
use App\Services\Masters\AgentService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected $service;

    public function __construct(AgentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return response()->json($this->service->index($request));
    }

    public function store(AgentRequest $request)
    {
        $agent = $this->service->store($request->validated());
        return response()->json($agent, 201);
    }

    public function show(string $id)
    {
        $agent = $this->service->show($id);
        return response()->json($agent);
    }

    public function update(AgentRequest $request, string $id)
    {
        $agent = $this->service->update($request->validated(), $id);
        return response()->json($agent);
    }

    public function destroy(string $id)
    {
        $response =$this->service->delete($id);
        return $response;
    }

    public function list()
    {
        return $this->service->list();
    }
}
