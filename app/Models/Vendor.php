<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{

    use HasFactory, SoftDeletes, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'vendor_name',
        'vendor_code',
        'group_id',
        'vendor_group_id',
        'gst_in',
        'pan_number',
        'phone_no',
        'email',
        'address_line_1',
        'address_line_2',
        'area_id',
        'city',
        'state',
        'country',
        'pincode_id',
        'payment_term_id',
        'credit_days',
        'credit_limit',
        'gst_applicable',
        'gst_registration_type_id',
        'tds_detail_id',
        'bank_account_no',
        'ifsc_code',
        'bank_name',
        'bank_branch_name',
        'transport_facility_provided',
        'remarks',
        'referred_source_type',
        'referred_source_id',
        'created_by',
        'updated_by'
    ];

    public function vendorContactDetails()
    {
        return $this->hasMany(VendorContactDetail::class);
    }

    public function vendorUpi()
    {
        return $this->hasMany(VendorUPI::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'vendor_item', 'vendor_id', 'item_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function pincode()
    {
        return $this->belongsTo(Pincode::class);
    }

    // public function country()
    // {
    //     return $this->belongsTo(Country::class);
    // }

    // public function state()
    // {
    //     return $this->belongsTo(State::class);
    // }

    // public function city()
    // {
    //     return $this->belongsTo(City::class);
    // }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function referredSource()
    {
        return $this->belongsTo(VendorReferredSource::class);
    }

    public function gstRegistrationType()
    {
        return $this->belongsTo(GstRegistrationType::class);
    }

    public function tdsDetail()
    {
        return $this->belongsTo(TdsDetail::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }
}
