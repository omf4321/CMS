<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class schedule_batch extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;         
}
