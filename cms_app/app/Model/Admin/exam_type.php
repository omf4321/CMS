<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class exam_type extends Model
{
    protected $table = 'exam_types';
    protected $guarded = [
        'id'
    ];
    
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }
}
