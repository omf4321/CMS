<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class online_exam extends Model
{
    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'exam_rule' => 'array',
        'subjects' => 'array'
    ];

    public $timestamps = true;

    public function questions()
    {
        // return $this->hasMany(question::class, 'online_exam_id');
    }
    
}
