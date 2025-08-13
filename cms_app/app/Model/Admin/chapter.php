<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;

class chapter extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = true;

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }
    public function topics()
    {
        return $this->hasMany(chapter_topic::class,'chapter_id');
    }

    public function questions()
    {
    	return $this->belongsToMany(question::class, 'question_chapters', 'chapter_id', 'question_id');
    }
    public function relatives()
    {
        return $this->hasMany(question_relative_text::class, 'chapter_id');
    }
    
}
