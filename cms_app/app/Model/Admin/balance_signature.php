<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class balance_signature extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    protected $casts = [
        'admin_id' => 'array'
    ];

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }    
    
}
