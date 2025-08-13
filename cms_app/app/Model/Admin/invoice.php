<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class invoice extends Model
{    
    protected $guarded = [
        'id'
    ];

    public $timestamps = true;

    // public function getCreatedAtAttribute( $value ) {
    //     return Carbon::parse($value)->format('d M y');
    // }
    // public function getDateFromAttribute( $value ) {
    //     return Carbon::parse($value)->format('d M y');
    // }
    // public function getDateToAttribute( $value ) {
    //     return Carbon::parse($value)->format('d M y');
    // }
    
}
