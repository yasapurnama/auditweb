@component('mail::message')
<h2><b>{{ $isSendOwner ? 'Warning!' : 'Hello!' }}</b></h2>

{!! $isSendOwner ? '<font color="red">Your website is in danger!</font><br>' : '' !!}

This is report for website audit result {{ $web_domain }}!

@component('mail::button', ['url' => $url])
Download Result
@endcomponent
Website audit: {{ $app_url }}<br/><br/>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
