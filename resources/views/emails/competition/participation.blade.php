@component('mail::message')
### Hello,

<br>
<br>You have participated in #{{ $data['slug'] }}. Now you can post, so the voters can vote you.
<br>

<br>
Thanks,<br>
{{ config('app.name') }}'s Team
@endcomponent
