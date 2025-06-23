<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\AttributeValueRequest;
use App\Models\AttributeValue;
use App\Services\Masters\AttributeValueService;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function __construct(private AttributeValueService $service) {}

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(AttributeValueRequest $request)
    {
        $attributeValue = $this->service->store($request);
        return response()->json($attributeValue, 201);
    }

    public function show(string $encryptedId)
    {
        return $this->service->show($encryptedId);
    }

    public function update(AttributeValueRequest $request, string $encryptedId)
    {
        $attributeValue = $this->service->update($request, $encryptedId);
        return response()->json($attributeValue);
    }

    public function destroy(string $encryptedId)
    {
        return $this->service->destroy($encryptedId);
    }

    public function list()
    {
        return $this->service->list();
    }
}
