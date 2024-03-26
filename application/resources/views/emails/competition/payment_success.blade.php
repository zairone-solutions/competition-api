@component('mail::message', ["title" => $data["title"]])
### Great!,
<br>

Your competition bill of <b>Rs.{{ $data['payment']['amount'] }}</b> has been paid.
You can publish whenever you want. But make sure, you adjust the voting and announcement dates first.
@component('mail::table')
| | |
| ------------------------ |:-------------------------------------------------------------: |
| <b>Title</b>             | {{ $data['competition']['title'] }} |
| <b>Tag</b>               | #{{ $data['competition']['slug'] }} |
| <b>Announcement Date</b> | {{ date(config("constants.date.format"), strtotime($data['competition']['announcement_at'])) }} |
| <b>Voting Date</b>       | {{ date(config("constants.date.format"), strtotime($data['competition']['voting_start_at'])) }} |
@endcomponent

<br>
<br>

Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
