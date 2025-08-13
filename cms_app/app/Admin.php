<?php

namespace App;

use App\Notifications\AdminResetPassword;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    protected $guard_name = 'admin';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */

    public function getCreatedAtAttribute( $value ) {
        return Carbon::parse($value)->format('d M y');
    } 

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPassword($token));
    }

    public function branches()
    {
        return $this->belongsTo('\App\Model\Admin\branch','branch_id');
    }

    public function salaries()
    {
        return $this->hasMany('\App\Model\Admin\salary','admin_id');
    }

    // public function roles()
    // {
    //     return $this->hasMany('\App\Model\Admin\Role','model_id')->where('model_type', 'App\Admin');
    // }
}
