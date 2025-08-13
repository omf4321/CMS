<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class salary_transaction extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;    

    public function users()
    {
    	return $this->belongsTo('\App\Admin', 'admin_id');
    }
    
}
