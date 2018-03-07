<?php

namespace App\Listeners;

use Exception;
use App\Mail\AuditReport;
use App\Events\AuditResultCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmails
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AuditResultCreated  $event
     * @return void
     */
    public function handle(AuditResultCreated $event)
    {
        try {
            if($event->isSendOwner){
                Mail::to(Auth::user()->email)
                    ->send(new AuditReport($event));
                Mail::to($event->auditresults->whois_domain_email)
                    ->send(new AuditReport($event));
            }
            else{
                Mail::to(Auth::user()->email)
                    ->send(new AuditReport($event));
            }
            
        } catch (Exception $e) {
            report($e);
        }
        
    }
}
