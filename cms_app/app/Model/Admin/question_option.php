<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class question_option extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;
    
}
