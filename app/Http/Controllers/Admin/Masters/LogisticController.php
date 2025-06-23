<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\LogisticRequest;
use App\Services\Masters\LogisticService;
use Illuminate\Http\Request;

class LogisticController extends Controller
{
     protected $logisticService;

    public function __construct(LogisticService $logisticService)
    {
        $this->logisticService = $logisticService;
    }

    public function index(Request $request)
    {
        return $this->logisticService->index($request);
    }

    public function store(LogisticRequest $request)
    {
        return $this->logisticService->store($request->validated());
    }

    public function show($id)
    {
        return $this->logisticService->show($id);
    }

    public function update(LogisticRequest $request, $id)
    {
        return $this->logisticService->update($request->validated(),$id);
    }

    public function destroy($id)
    {
        return $this->logisticService->destroy($id);
    }


    public function list(){
        return $this->logisticService->list();
    }
}
