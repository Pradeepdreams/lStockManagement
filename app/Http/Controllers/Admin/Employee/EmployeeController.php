<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Services\EmployeeService;
use Exception;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $employee = $this->service->index($request);
            return $employee;
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function store(EmployeeRequest $request)
    {
        try {

            return $this->service->store($request);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to create employee. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            // $decryptedId = Crypt::decryptString($id);
            $employee = $this->service->show($id);
            return response()->json($employee);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch employee. ' . $e->getMessage()], 500);
        }
    }

    public function update(EmployeeRequest $request, string $id)
    {
        try {
            // $decryptedId = Crypt::decryptString($id);
            $employee = $this->service->update($request, $id);
            return response()->json($employee);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update Employee. ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->destroy($id);
            return response()->json(['message' => 'Employee deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete Employee. ' . $e->getMessage()], 500);
        }
    }


    public function list(){
        return response()->json($this->service->list());
    }
}
