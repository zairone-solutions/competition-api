@component('mail::message')
### Hello,

<br>
<b>{{$content['code']}}</b> is your one-time passcode (OTP) for your foget password request.
<br>

<br>
Thanks,<br>
{{ config('app.name') }}'s Team
@endcomponent
