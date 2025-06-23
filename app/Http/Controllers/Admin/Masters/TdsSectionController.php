<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\TdsSectionRequest;
use App\Services\Masters\TdsSectionService;
use Illuminate\Http\Request;

class TdsSectionController extends Controller
{
    protected $tdsSectionService;

    public function __construct(TdsSectionService $tdsSectionService)
    {
        $this->tdsSectionService = $tdsSectionService;
    }

    public function index(Request $request)
    {
        return $this->tdsSectionService->index($request);
    }

    public function store(TdsSectionRequest $request)
    {
        return $this->tdsSectionService->store($request->validated());
    }

    public function show($id)
    {
        return $this->tdsSectionService->show($id);
    }

    public function update(TdsSectionRequest $request, $id)
    {
        return $this->tdsSectionService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->tdsSectionService->destroy($id);
    }

    public function list()
    {
        return $this->tdsSectionService->list();
    }
}
