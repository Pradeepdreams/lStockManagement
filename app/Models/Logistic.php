<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class Logistic extends Model
{
     use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['name'];


    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
