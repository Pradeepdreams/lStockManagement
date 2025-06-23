<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch()
    {
        static::creating(function ($model) {
            Log::info('Branch Middleware:', [
                'bound' => app()->bound('currentBranchId'),
                'value' => app('currentBranchId')
            ]);

            if (app()->bound('currentBranchId')) {
                $model->branch_id = app('currentBranchId');
            }
        });

        static::addGlobalScope('branch', function (Builder $builder) {
            if (app()->bound('currentBranchId')) {
                $builder->where('branch_id', app('currentBranchId'));
            }
        });
    }
}
