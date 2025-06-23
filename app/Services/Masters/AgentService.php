<?php

namespace App\Services\Masters;

use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AgentService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_agent'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $agentQuery = Agent::query();

        if($search){
            $agentQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $agents = $agentQuery->latest()->paginate(10);
        $getLinks = $agents->jsonSerialize();

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
            'agents' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_agent'), 403, 'Unauthorized');
        try {
            return DB::transaction(function () use ($data) {
                $agent = Agent::create($data);
                logActivity('Created', $agent, [$agent]);
                return $agent;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to create agent: " . $e->getMessage());
        }
    }

    public function show(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_agent'), 403, 'Unauthorized');

        $id = Crypt::decryptString($encryptedId);
        return Agent::findOrFail($id);
    }

    public function update(array $data, string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_agent'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($encryptedId);
            return DB::transaction(function () use ($data, $id) {
                $agent = Agent::findOrFail($id);
                $agent->update($data);
                logActivity('Updated', $agent, [$agent]);

                return $agent;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to update agent: " . $e->getMessage());
        }
    }

    public function delete(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_agent'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($encryptedId);
            $agent = Agent::findOrFail($id);
            if ($agent->referredSource()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete agent. It is assigned to one or more vendors.',
                ], 400);
            }
            $agent->delete();
            logActivity('Deleted', $agent, [$agent]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete agent: " . $e->getMessage());
        }
    }


    public function list()
    {
        return Agent::get();
    }
}
