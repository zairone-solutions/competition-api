@component('mail::message')
### Hello,
<br>

Thank you for organizing a competition. Now share the competition to get maximum participations.
@component('mail::table')
| | |
| ------------------------ |:-------------------------------------------------------------: |
| <b>Title</b>             | {{ $data['title'] }} |
| <b>Tag</b>               | #{{ $data['slug'] }} |
| <b>Announcement Date</b> | {{ date(config("constants.date.format"), strtotime($data['announcement_at'])) }} |
| <b>Voting Date</b>       | {{ date(config("constants.date.format"), strtotime($data['voting_start_at'])) }} |
@endcomponent

<br>
<br>

Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
