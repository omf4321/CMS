<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class random_number extends Model
{
    protected $table = 'random_numbers';
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;
    
}
