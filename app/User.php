<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'username', 'password', 'status',
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
     * Check if user is an Admin
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role == 2 ? true : false;
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

    public function audit_result()
    {
        return $this->hasMany(AuditResult::class);
    }

    public function download()
    {
        return $this->hasMany(Download::class);
    }

    public function email_sent()
    {
        return $this->hasMany(EmailSent::class);
    }

    public function notification()
    {
        return $this->hasMany(Notification::class);
    }
}
