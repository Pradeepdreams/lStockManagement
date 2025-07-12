<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes, HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'name',
        'group_id',
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
        'pincode',
        'payment_term_id',
        'credit_days',
        'credit_limit',
        'gst_applicable',
        'gst_registration_type_id',
        'tds_detail_id',
        'created_by',
        'updated_by'
    ];

    public function customerContactDetails()
    {
        return $this->hasMany(CustomerContactDetail::class);
    }

    public function customerUpi()
    {
        return $this->hasMany(CustomerUPI::class);
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
