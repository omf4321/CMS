<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class organization extends Model
{    
    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'admission_field' => 'array',
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }
    
}
