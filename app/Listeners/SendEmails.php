<?php

namespace App\Listeners;

use Exception;
use App\EmailSent;
use App\Notification;
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
        $notifiction = $sendEmail = Auth::user()->setting->notify;
        try {
            if($event->isSendOwner){
                Mail::to(Auth::user()->email)
                    ->send(new AuditReport($event));
                if($notifiction){
                    $email_sent_user = EmailSent::create([
                        'user_id' => Auth::user()->id,
                        'audit_result_id' => $event->auditresults->id,
                        'email' => Auth::user()->email,
                        'owner' => Auth::user()->name
                    ]);
                    $notification = Notification::create([
                        'user_id' => Auth::user()->id,
                        'email_sent_id' => $email_sent_user->id,
                        'notif_message' => 'Email report sent to '.$email_sent_user->email,
                        'readed' => false,
                        'owner_report' => false
                    ]);
                }

                Mail::to($event->auditresults->whois_domain_email)
                    ->send(new AuditReport($event));
                if($notifiction){
                    $email_sent_owner = EmailSent::create([
                        'user_id' => Auth::user()->id,
                        'audit_result_id' => $event->auditresults->id,
                        'email' => $event->auditresults->whois_domain_email,
                        'owner' => $event->auditresults->whois_domain_owner
                    ]);
                    $notification = Notification::create([
                        'user_id' => Auth::user()->id,
                        'email_sent_id' => $email_sent_owner->id,
                        'notif_message' => 'Email report sent to '.$email_sent_owner->email,
                        'readed' => false,
                        'owner_report' => true
                    ]);
                }
            }
            else{
                Mail::to(Auth::user()->email)
                    ->send(new AuditReport($event));
                if($notifiction){
                    $email_sent_user = EmailSent::create([
                        'user_id' => Auth::user()->id,
                        'audit_result_id' => $event->auditresults->id,
                        'email' => Auth::user()->email,
                        'owner' => Auth::user()->name
                    ]);
                    $notification = Notification::create([
                        'user_id' => Auth::user()->id,
                        'email_sent_id' => $email_sent_user->id,
                        'notif_message' => 'Email report sent to '.$email_sent_user->email,
                        'readed' => false,
                        'owner_report' => false
                    ]);
                }
            }

        } catch (Exception $e) {
            report($e);
        }
        
    }
}
