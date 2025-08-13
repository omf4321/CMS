<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class invigilator_payment extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = false;

    public function getCreatedAtAttribute( $value ) {
      	return Carbon::parse($value)->format('d M y');
    }
    
}
