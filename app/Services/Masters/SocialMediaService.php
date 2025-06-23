<?php

namespace App\Services\Masters;

use App\Models\SocialMedia;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SocialMediaService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_social_media'), 403, 'Unauthorized');

        $search = $request->search ?? false;
        $socialMediaQuery = SocialMedia::query();
        if ($search) {
            $socialMediaQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $socialMedia = $socialMediaQuery->latest()->paginate(10);
        $getLinks = $socialMedia->jsonSerialize();
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
            'social_media' => $getLinks
        ]);
    }

    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_social_media'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $socialMedia = SocialMedia::create($data);
            logActivity('Created', $socialMedia, [$socialMedia]);

            DB::commit();
            return $socialMedia;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while creating Social Media: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_social_media'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return SocialMedia::findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error while fetching SocialMedia: ' . $e->getMessage());
            // return response()->json(['message' => 'SocialMedia not found.'], 404);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_social_media'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $socialMedia = SocialMedia::findOrFail($id);
            $changes = $socialMedia->getChangedAttributesFromRequest($data);
            $socialMedia->update($data);
            logActivity('Updated', $socialMedia, $changes);

            DB::commit();
            return $socialMedia;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error while updating SocialMedia: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_social_media'), 403, 'Unauthorized');

        try {

            // DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $socialMedia = SocialMedia::findOrFail($id);
            $socialMedia->delete();
            logActivity('Deleted', $socialMedia, [$socialMedia]);

            // DB::commit();
            return response()->json(['message' => 'SocialMedia deleted successfully']);
        } catch (Exception $e) {
            // DB::rollBack();
            Log::error('Error while deleting SocialMedia: ' . $e->getMessage());
            // return response()->json(['message' => $e->getMessage()]);
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return SocialMedia::get();
    }
}
