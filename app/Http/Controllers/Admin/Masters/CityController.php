<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\CityRequest;
use App\Services\Masters\CityService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    protected $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function index(Request $request)
    {
        return $this->cityService->index($request);
    }

    public function store(CityRequest $request)
    {
        return $this->cityService->store($request->validated());
    }

    public function show($id)
    {
        return $this->cityService->show($id);
    }

    public function update(CityRequest $request, $id)
    {
        return $this->cityService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->cityService->destroy($id);
    }


    public function list(Request $request){
        return $this->cityService->list($request);
    }
}
