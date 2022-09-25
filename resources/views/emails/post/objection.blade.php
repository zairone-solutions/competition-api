@component('mail::message')
### Hello,
<br>

Your post has been objected by the organizer. Kindly revisit your post and make the required changes before the voting starts.
<br>
@component('mail::table')
| | |
| :---------------------- |:------------------------------------------------------------- |
| <b>Competition</b>       | #{{ $data['competition']['slug'] }} |
| <b>Reason for objection</b>            | {{ $data['objection']['description'] }} |
| <b>Objection date</b>    | {{ date(config("constants.date.format"), strtotime($data['objection']['created_at'])) }} |
| <b>Voting date</b>       | {{ date(config("constants.date.format"), strtotime($data['competition']['voting_start_at'])) }} |
@endcomponent

<br>
<br>
Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
