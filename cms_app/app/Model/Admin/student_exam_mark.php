<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class student_exam_mark extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;

    public function subjects()
    {
        return $this->belongsTo(subject::class,'subject_id');
    }

    public function students()
    {
        return $this->belongsTo(student::class,'student_id');
    }
}
