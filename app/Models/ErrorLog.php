<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'input',
    ];
}
