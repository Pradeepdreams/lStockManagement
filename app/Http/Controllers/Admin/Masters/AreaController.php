<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\AreaRequest;
use App\Services\Masters\AreaService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    protected $areaService;

    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    public function index(Request $request)
    {
        return $this->areaService->index($request);
    }

    public function store(AreaRequest $request)
    {
        return $this->areaService->store($request->validated());
    }

    public function show(Request $request, $id)
    {

        return $this->areaService->show($request, $id);
    }

    public function update(AreaRequest $request, $id)
    {
        return $this->areaService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->areaService->destroy($id);
    }

    public function list()
    {
        return $this->areaService->list();
    }
}
