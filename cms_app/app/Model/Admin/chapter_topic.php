<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;

class chapter_topic extends Model
{
    protected $fillable = [
        'name', 'topic_no', 'chapter_id'
    ];
    public $timestamps = true;    

    public function chapters()
    {
    	return $this->belongsTo(chapter::class,'chapter_id');
    }
}
