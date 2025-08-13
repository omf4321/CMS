<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class batch_monthly_fee extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    // Each fee belongs to a batch
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    // Each fee belongs to a session
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    
}
