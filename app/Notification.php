<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'email_sent_id', 'notif_message', 'readed', 'owner_report'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
