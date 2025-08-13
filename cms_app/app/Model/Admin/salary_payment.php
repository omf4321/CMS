<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class salary_payment extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }
    
}
