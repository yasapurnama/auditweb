<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'audit_result_id', 'token', 'created_at'
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
