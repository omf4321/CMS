<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class exam_question_list extends Model
{
    public $timestamps = true;

    protected $casts = [
        'chapter' => 'array'
    ];

    public function exam_lists()
    {
        return $this->hasMany(exam_list::class);
    }

    public function exam_types()
    {
        return $this->belongsTo(exam_type::class,'exam_type_id');
    }

    public function exam_question_types()
    {
    	return $this->hasMany(exam_question_type::class,'exam_question_list_id');
    }

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }
	public function echelons()
    {
    	return $this->belongsTo(echelon::class,'echelon_id');
    }

    public function schedules()
    {
        return $this->hasMany(daily_schedule::class,'exam_question_list_id');
    }

    public function chapters()
    {
        return $this->belongsToMany(chapter::class, 'exam_question_chapters', 'exam_question_list_id', 'chapter_id');
    }

    public function assign_exams()
    {
        return $this->belongsToMany(exam_list::class, 'exam_question_assigns', 'exam_question_list_id', 'exam_list_id');
    }
}


