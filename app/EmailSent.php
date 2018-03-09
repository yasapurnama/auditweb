<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailSent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'email', 'owner'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audit_result()
    {
        return $this->belongsTo(AuditResult::class);
    }
}
