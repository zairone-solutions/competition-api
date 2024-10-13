@component('mail::message', ["title" => $data["title"]])
### Hurray!,
<br>

You have won #{{ $data['competition']['slug']  }}. The prize money has been transfered to your wallet.
@component('mail::table')
| | |
| ------------------------ |:-------------------------------------------------------------: |
| <b>Title</b>             | {{ $data['competition']['title'] }} |
| <b>Tag</b>               | #{{ $data['competition']['slug'] }} |
| <b>Prize Money</b>       | {{ $data['prize_money'] }}          |
| <b>Announcement Date</b> | {{ date(config("constants.date.format"), strtotime($data['competition']['announcement_at'])) }} |
@endcomponent

<br>
<br>

Thanks, <br>
{{ config('app.name') }}'s Team
@endcomponent
