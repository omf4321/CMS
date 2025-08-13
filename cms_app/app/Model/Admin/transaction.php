<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class transaction extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function categories()
    {
    	return $this->belongsTo(transaction_category::class,'category_id');
    }

    public function bills()
    {
    	return $this->belongsTo(bill::class,'bill_id');
    }

    public function users()
    {
        return $this->belongsTo('\App\Admin', 'inputer_id');
    }
    
}
