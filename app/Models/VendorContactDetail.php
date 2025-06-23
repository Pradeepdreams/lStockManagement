<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorContactDetail extends Model
{

    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['vendor_id', 'name', 'designation', 'phone_no', 'email'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
