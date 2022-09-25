@component('mail::message')
    ### Hello,

    <br>
    <b>{{ $content['code'] }}</b> is your one-time passcode (OTP) for the {{ config('app.name') }}'s app email verification.
    <br>

    <br>
    Enjoy Our App!
    <br>

    <br>
    Thanks,<br>
    {{ config('app.name') }}'s Team
@endcomponent
