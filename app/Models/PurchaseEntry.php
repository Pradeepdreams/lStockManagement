<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseEntry extends Model
{
    use SoftDeletes, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'purchase_entry_number',
        'against_po',
        'purchase_order_id',
        'vendor_id',
        'vendor_invoice_no',
        'vendor_invoice_date',
        'sub_total_amount',
        'gst_amount',
        'total_amount',
        'purchase_person_id',
        'mode_of_delivery',
        'logistic_id',
        'vendor_invoice_image',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];


    public function items()
    {
        return $this->hasMany(PurchaseEntryItem::class);
    }

    public function gstDetails()
    {
        return $this->hasMany(PurchaseEntryGstDetail::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchasePerson()
    {
        return $this->belongsTo(User::class, 'purchase_person_id');
    }

    public function logistic()
    {
        return $this->belongsTo(Logistic::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
