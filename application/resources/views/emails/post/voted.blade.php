@component('mail::message')
### Hello,
<br>

Your vote has been casted successfully.
<br>
@component('mail::table')
| | |
| :----------------------- |:------------------------------------------------------------- |
| <b>Organizer</b>         | {{ $data['organizer']['username'] }} |
| <b>Competition</b>       | #{{ $data['competition']['slug'] }} |
| <b>You Voted At</b>      | {{ date(config("constants.date.format"), strtotime($data['vote']['created_at'])) }} |
| <b>Announcement Date</b> | {{ date(config("constants.date.format"), strtotime($data['competition']['announcement_at'])) }} |
@endcomponent

<br>
<br>
Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
