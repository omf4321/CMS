<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;

class fee_scheme extends Model
{
    public $timestamps = true;

    protected $casts = [
        'batch_ids' => 'array'
    ];

    protected $guarded = [
        'id'
    ];
}
