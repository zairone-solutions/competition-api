@component('mail::message')
### Hello,
<br>

A post in your competition has been reported by a user. Kindly visit the post and take a suitable action.

<br>
@component('mail::table')
| | |
| :---------------------- |:------------------------------------------------------------- |
| <b>Competition</b>      | #{{ $data['competition']['slug'] }} |
| <b>Reported by</b>      | {{ $data['user']['username'] }} |
| <b>Reported Description</b>      | {{ $data['report']['description'] }} |
| <b>Report date</b>      | {{ date(config("constants.date.format"), strtotime($data['report']['created_at'])) }} |
@endcomponent

<br>
<br>
Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
