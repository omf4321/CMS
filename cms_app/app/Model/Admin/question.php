<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class question extends Model
{
    protected $gaurded = [
        'id'
    ];
    protected $casts = [
        'subtag' => 'array'
    ];
    public $timestamps = true;

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }
    public function options()
    {
        return $this->hasMany(question_option::class,'question_id');
    }
    public function chapters()
    {
    	return $this->belongsToMany(chapter::class, 'question_chapters', 'question_id', 'chapter_id');
    }
    public function topics()
    {
    	return $this->belongsToMany(chapter_topic::class, 'question_topics', 'question_id', 'topic_id');
    }
    public function relatives()
    {
        return $this->belongsTo(question_relative_text::class, 'question_relative_text_id');
    }
}
