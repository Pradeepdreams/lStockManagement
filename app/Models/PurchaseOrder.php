<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'po_number',
        'date',
        'area_id',
        'vendor_id',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'order_amount',
        'gst_amount',
        'total_amount',
        'minimum_discount',
        'discount_percent',
        'discount_amount',
        'discounted_total',
        'payment_terms_id',
        'mode_of_delivery',
        'is_polished',
        'expected_delivery_date',
        'logistic_id',
        'po_status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function paymentTerms()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function purchaseOrderGst()
    {
        return $this->hasMany(PurchaseOrderGstEntry::class);
    }


    public function activeDiscountPercent()
    {
        $discountFlag = FeatureFlags::where('name', 'purchase_discount')->first();
        if ($discountFlag->active_status) {
            return DiscountOnPurchase::where('discount_type', 'special')
                ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
                ->orderByDesc('applicable_date');
        } else {
            return DiscountOnPurchase::where('discount_type', 'normal')
                ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
                ->orderByDesc('applicable_date');
        }
    }
}
