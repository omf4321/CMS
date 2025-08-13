<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class combine_subject extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;   

    public function combine_subject_rules()
    {
    	return $this->belongsTo(combine_subject_rule::class,'combine_subject_rule_id');
    } 
}
