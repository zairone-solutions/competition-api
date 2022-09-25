@component('mail::message')
### Hello,

<br>
<br>You have a new participation <b>#{{ $data['slug'] }}</b>. Please visit the application for further details.
<br>
<br>
Thanks,<br>
{{ config('app.name') }}'s Team
@endcomponent
