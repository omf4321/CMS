<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class student_attendance extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;

    public function students()
    {
    	return $this->belongsTo(student::class, 'student_id');
    }
}
