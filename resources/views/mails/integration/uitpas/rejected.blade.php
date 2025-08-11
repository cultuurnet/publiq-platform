@include('mails.partials.header')

<p>Je integratie met naam {{ $integrationName }} heeft geen activatie ontvangen voor de organisator <strong>{{ $organizerName }}</strong>.</p>

<p>Je kan ons bereiken via technical-support@publiq.be of via het Slack-kanaal #technical-support-publiq om na te gaan waarom deze aanvraag precies werd afgekeurd.</p>

<p>Het publiq platform team.</p>

@include('mails.partials.footer')
