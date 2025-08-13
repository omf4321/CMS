<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;

class student_fine extends Model
{
    protected $guarded = [
        'id'
    ];

    protected $table = 'student_fines';
    public $timestamps = true;
    
    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function branches()
    {
    	return $this->belongsTo(branch::class,'branch_id');
    }
}
