<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class weekly_schedule_list extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;   

    public function echelons()
    {
    	return $this->belongsTo(echelon::class,'echelon_id');
    } 
}
