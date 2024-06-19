@component('mail::message')
### Hello,
<br>

A post has been published for approval.
<br>
@component('mail::table')
| | |
| :----------------------- |:------------------------------------------------------------- |
| <b>Organizer</b>         | {{ $data['organizer']['username'] }} |
| <b>Competition</b>       | #{{ $data['competition']['slug'] }} |
| <b>Announcement Date</b> | {{ date(config("constants.date.format"), strtotime($data['competition']['announcement_at'])) }} |
| <b>Voting Date</b>       | {{ date(config("constants.date.format"), strtotime($data['competition']['voting_start_at'])) }} |
@endcomponent

<br>
<br>
Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
