<?php

namespace App\Mail;

use App\Download;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Events\AuditResultCreated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuditReport extends Mailable
{
    use Queueable, SerializesModels;

    public $event;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(AuditResultCreated $event)
    {
        $this->event = $event;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $audit_results = $this->event->auditresults;
        $download = Download::where('audit_result_id',$audit_results->id)->first();

        $isSendOwner = $this->event->isSendOwner;
        $url = Request::root().'/download/'.$audit_results->id.'?token='.$download->token;
        $web_domain = $audit_results->web_domain;
        return $this->markdown('emails.audit.report')
            ->with([
                'isSendOwner' => $isSendOwner,
                'url' => $url,
                'web_domain' => $web_domain,
                'app_url' => Request::root(),
            ]);
    }
}
