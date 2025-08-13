<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class per_class_payment extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      	return Carbon::parse($value)->format('d M y');
    }   
}
