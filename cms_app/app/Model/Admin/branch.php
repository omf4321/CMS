<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class branch extends Model
{
    protected $table = 'branches';
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function organizations()
    {
        return $this->belongsTo('\App\Model\Admin\organization','organization_id');
    }
    
}
