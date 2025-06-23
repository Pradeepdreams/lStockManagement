<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\PincodeRequest;
use App\Services\Masters\PincodeService;
use Illuminate\Http\Request;

class PincodeController extends Controller
{
    protected $pincodeService;

    public function __construct(PincodeService $pincodeService)
    {
        $this->pincodeService = $pincodeService;
    }

    public function index(Request $request)
    {
        return $this->pincodeService->index($request);
    }

    public function store(PincodeRequest $request)
    {
        return $this->pincodeService->store($request->validated());
    }

    public function show($id)
    {
        return $this->pincodeService->show($id);
    }

    public function update(PincodeRequest $request, $id)
    {
        return $this->pincodeService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->pincodeService->destroy($id);
    }

    public function list(Request $request){
        return $this->pincodeService->list($request);
    }

}
