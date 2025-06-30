<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'pincode',
        'gst_number',
        'pan_number',
        'credit_limit',
        'customer_group',
    ];
}
