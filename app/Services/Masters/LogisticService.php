<?php

namespace App\Services\Masters;

use App\Models\Logistic;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Crypt;

class LogisticService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_logistics'), 403, 'Unauthorized');

        $search = $request->search ?? null;
        $logisticQuery = Logistic::query();
        if($search){
            $logisticQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }
        $logistics = $logisticQuery->latest()->paginate(10);
        $getLinks = $logistics->jsonSerialize();

        foreach ($getLinks['links'] as &$row) {

            if ($row['label'] == "Next &raquo;") {

                $row['label'] = 'Next';
            }

            if ($row['label'] == "&laquo; Previous") {

                $row['label'] = 'Previous';
            }
        }
        return response([
            'success' => true,
            'logistics' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_logistics'), 403, 'Unauthorized');
        try {
            return DB::transaction(function () use ($data) {
                $logistic = Logistic::create($data);
                logActivity('Created', $logistic, [$logistic]);
                return $logistic;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to create logistic: " . $e->getMessage());
        }
    }

    public function show(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_logistics'), 403, 'Unauthorized');

        $id = Crypt::decryptString($encryptedId);
        return Logistic::findOrFail($id);
    }

    public function update(array $data, string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_logistics'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($encryptedId);
            return DB::transaction(function () use ($data, $id) {
                $logistic = Logistic::findOrFail($id);
                $logistic->update($data);
                logActivity('Updated', $logistic, [$logistic]);

                return $logistic;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to update logistic: " . $e->getMessage());
        }
    }

    public function destroy(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_logistics'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($encryptedId);
            $logistic = Logistic::findOrFail($id);
            if ($logistic->purchaseOrders()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete logistic. It is assigned to one or more Purchase Orders.',
                ], 400);
            }
            $logistic->delete();
            logActivity('Deleted', $logistic, [$logistic]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete logistic: " . $e->getMessage());
        }
    }

    public function list()
    {
        return Logistic::orderby('name')->get();
    }
}
