<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRequest;
use App\Services\VendorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class VendorController extends Controller
{
    protected VendorService $service;

    public function __construct(VendorService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $vendors = $this->service->index($request);
            return $vendors;
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function store(VendorRequest $request)
    {
        try {

            return $this->service->store($request);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to create vendor. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id, Request $request)
    {
        try {
            // $decryptedId = Crypt::decryptString($id);
            $vendor = $this->service->show($id, $request);
            return response()->json($vendor);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch vendor. ' . $e->getMessage()], 500);
        }
    }

    public function update(VendorRequest $request, string $id)
    {
        try {
            // $decryptedId = Crypt::decryptString($id);
            $vendor = $this->service->update($id, $request);
            return response()->json(['message' => 'Vendor updated successfully.', 'data' => $vendor]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update vendor. ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            return $this->service->destroy($id);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete vendor. ' . $e->getMessage()], 500);
        }
    }


    public function list()
    {
        return response()->json($this->service->list());
    }
}
