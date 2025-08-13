<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class exam_question_type extends Model
{
    protected $gaurded = [
        'id'
    ];
    protected $casts = [
        'relative_type_data' => 'array',
        'chapters' => 'array',
    ];
    public $timestamps = true;

    public function exams()
    {
    	// return $this->belongsTo(question::class, 'question_relative_text_id');
    }
    public function relatives()
    {
        return $this->belongsTo(question_relative_text::class,'relative_id');
    }
    public function relative_chapters()
    {
        return $this->belongsTo(chapter::class,'relative_chapter_id');
    }
    public function questions() //$method, $relative_question_type
    {
        return $this->belongsToMany(question::class, 'exam_questions', 'exam_question_type_id', 'question_id')->withPivot('set');
        // ->where('method', $method)->where('exam_questions.relative_question_type', $relative_question_type);
    }
    
}
