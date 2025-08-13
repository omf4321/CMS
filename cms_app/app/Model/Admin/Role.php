<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Role extends Spatie\Permission\Contracts\Role
{    
    public function getCreatedAtAttribute( $value ) {
      	return Carbon::parse($value)->format('d M y');
    }  
}
