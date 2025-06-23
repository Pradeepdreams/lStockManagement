<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\TdsDetailRequest;
use App\Services\Masters\TdsDetailService;
use Illuminate\Http\Request;

class TdsDetailController extends Controller
{
    protected $service;

    public function __construct(TdsDetailService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return response()->json($this->service->index($request));
    }

    public function store(TdsDetailRequest $request)
    {
        $tds = $this->service->store($request->validated());
        return response()->json($tds, 201);
    }

    public function show($id)
    {
        return response()->json($this->service->show($id));
    }

    public function update(TdsDetailRequest $request, $id)
    {
        $tds = $this->service->update($id, $request->validated());
        return response()->json($tds);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);

    }


    public function list(){
        return $this->service->list();
    }
}
