<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{

    public function index()
    {
        // return response()->json(Branch::all());
        abort_unless(auth()->user()->hasBranchPermission('view_branches'), 403, 'Unauthorized');

        try {
            $branches = Branch::latest()->paginate(10);

            $getLinks = $branches->jsonSerialize();

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
                'branches' => $getLinks
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to list ledger group: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_branches'), 403, 'Unauthorized');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $branch = Branch::create($validated);
        logActivity('Created', $branch, [$branch]);
        return response()->json($branch, 201);
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_branches'), 403, 'Unauthorized');

        $id = Crypt::decryptString($id);
        $branch = Branch::findOrFail($id);
        return response()->json($branch);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->hasBranchPermission('edit_branches'), 403, 'Unauthorized');
        $id = Crypt::decryptString($id);
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $branch->update($validated);
        logActivity('Updated', $branch, [$branch]);
        return response()->json($branch);
    }

    public function destroy($id)
    {

        abort_unless(auth()->user()->hasBranchPermission('delete_branches'), 403, 'Unauthorized');
        $id = Crypt::decryptString($id);

        $branch =DB::table('branch_user_role')->where('branch_id', $id)->first();

        if($branch){
            return response()->json(['message' => 'Can\'t delete this branch it\'s assigned to a user.'], 403);
        }

        $branch = Branch::findOrFail($id);
        $branch->delete();
        logActivity('Deleted', $branch, [$branch]);
        return response()->json(['message' => 'Branch deleted successfully.']);
    }

    public function list()
    {
        return Branch::get();
    }
}
