<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class combine_subject_rule extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;   

    public function echelons()
    {
    	return $this->belongsTo(echelon::class,'echelon_id');
    } 

    public function combine_subjects()
    {
        return $this->hasMany(combine_subject::class,'combine_subject_rule_id');
    } 
}
