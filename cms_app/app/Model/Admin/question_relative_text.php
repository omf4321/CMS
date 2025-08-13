<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class question_relative_text extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = true;

    public function questions()
    {
    	return $this->hasMany(question::class, 'question_relative_text_id');
    }
}
