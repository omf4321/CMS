<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class schedule_list extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      	return Carbon::parse($value)->format('d M y');
    }

    public function echelons()
    {
    	return $this->belongsTo(echelon::class,'echelon_id');
    }

    public function batches()
    {
    	return $this->belongsTo(batch::class,'batch_id');
    }
    public function schedule_contents()
    {
        return $this->hasMany(schedule_list_content::class,'schedule_list_id');
    }
}
