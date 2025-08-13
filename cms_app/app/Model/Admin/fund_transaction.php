<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class fund_transaction extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function funds()
    {
        return $this->belongsTo(fund::class,'fund_id');
    }
    
}
