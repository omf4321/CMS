<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class user_message extends Model
{
    protected $table = 'user_messages';
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function teachers()
    {
    	return $this->belongsTo(teacher::class,'teacher_id');
    }    
}
