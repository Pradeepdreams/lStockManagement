<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\PaymentTermRequest;
use App\Services\Masters\PaymentTermService;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function __construct(protected PaymentTermService $service) {}

    public function index(Request $request)
    {
        return response()->json($this->service->index($request));
    }

    public function store(PaymentTermRequest $request)
    {

        $term = $this->service->store($request->validated());
        return response()->json($term, 201);
    }

    public function show(string $id)
    {
        return response()->json($this->service->show($id));
    }

    public function update(PaymentTermRequest $request, string $id)
    {
        $term = $this->service->update($id, $request->validated());
        return response()->json($term);
    }

    public function destroy(string $id)
    {
        return $this->service->destroy($id);
    }

    public function list(){
        return $this->service->list();
    }
}
