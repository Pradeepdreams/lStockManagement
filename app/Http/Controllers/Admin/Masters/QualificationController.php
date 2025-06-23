<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\QualificationRequest;
use App\Services\Masters\QualificationService;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    protected $service;

    public function __construct(QualificationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(QualificationRequest $request)
    {
        $qualification = $this->service->store($request->validated());
        return response()->json(['data' => $qualification]);
    }

    public function show($id)
    {
        $qualification = $this->service->show($id);
        return response()->json(['data' => $qualification]);
    }


    public function update(QualificationRequest $request, $id)
    {
        $qualification = $this->service->update($id, $request->validated());
        return response()->json(['data' => $qualification]);
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
