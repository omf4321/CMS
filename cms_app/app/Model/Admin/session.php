<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class session extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    // A session has many students
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // A session has many monthly fees (via batch)
    public function batch_monthly_fees()
    {
        return $this->hasMany(batch_monthly_fee::class);
    }
    
}
