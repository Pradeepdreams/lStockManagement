<?php

namespace App\Services\Masters;

use App\Models\PaymentTerm;
use Illuminate\Support\Facades\DB;
use Exception;
use Faker\Provider\ar_EG\Payment;
use Illuminate\Support\Facades\Crypt;

class PaymentTermService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_payment_terms'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $paymentQuery = PaymentTerm::query();

        if ($search) {
            $paymentQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $paymentTerms = $paymentQuery->latest()->paginate(10);
        $getLinks = $paymentTerms->jsonSerialize();

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
            'payment_terms' => $getLinks
        ]);
    }



    public function store(array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_payment_terms'), 403, 'Unauthorized');

        try {
            DB::beginTransaction();

            $term = PaymentTerm::create($data);
            logActivity('Created', $term, [$term]);
            DB::commit();
            return $term;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_payment_terms'), 403, 'Unauthorized');

        $id = Crypt::decryptString($id);
        return PaymentTerm::findOrFail($id);
    }

    public function update($id, array $data)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_payment_terms'), 403, 'Unauthorized');

        try {

            DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $term = PaymentTerm::findOrFail($id);
            $changes = $term->getChangedAttributesFromRequest($data);
            $term->update($data);
            logActivity('Updated', $term, $changes);
            DB::commit();
            return $term;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('message: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_payment_terms'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            $term = PaymentTerm::findOrFail($id);
            if ($term->vendors()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete payment term. It is assigned to one or more vendors.',
                ], 400);
            }
            $term->delete();

            logActivity('Deleted', $term, [$term]);
            return response()->json(['message' => 'Payment term deleted successfully.']);
        } catch (Exception $e) {

            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function list()
    {
        return PaymentTerm::get();
    }
}
