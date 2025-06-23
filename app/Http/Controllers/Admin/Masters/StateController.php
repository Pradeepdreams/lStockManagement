<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StateRequest;
use App\Services\Masters\StateService;
use Illuminate\Http\Request;

class StateController extends Controller
{
    protected $stateService;

    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;
    }

    public function index(Request $request)
    {
        return $this->stateService->index($request);
    }

    public function store(StateRequest $request)
    {
        return $this->stateService->store($request->validated());
    }

    public function show($id)
    {
        return $this->stateService->show($id);
    }

    public function update(StateRequest $request, $id)
    {
        return $this->stateService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->stateService->destroy($id);
    }

    public function list(Request $request){
        return $this->stateService->list($request);
    }
}
