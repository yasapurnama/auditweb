<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'web_domain', 'host_ip', 'asn_info', 'ssl_info', 'ssl_expired', 'ssl_heartbleed', 'dns_info', 'cname_info', 'txt_info', 'whois_info', 'whois_registrant', 'whois_domain_owner', 'whois_domain_email', 'openresolver_info', 'openresolver_vuln', 'mx_info', 'smtp_info', 'smtp_openrelay', 'dmarc_info', 'dmarc_needed', 'spf_info', 'spf_needed', 'risk_info', 'risk_low', 'risk_medium', 'risk_high', 'created_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function download()
    {
        return $this->hasOne(Download::class);
    }

    public function email_sent()
    {
        return $this->hasMany(EmailSent::class);
    }
}
