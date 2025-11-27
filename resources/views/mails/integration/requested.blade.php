@include('mails.partials.header')

<p>We hebben je aanvraag tot activatie voor de integratie met naam <strong>{{ $integrationName }}</strong> goed
    ontvangen.</p>

@if($showContentCheck ?? false)
<p>Om de integratie te activeren stuur je via de integratie 5 testevenementen zoals beschreven in de documentatie.
    Gelieve deze evenementen, met hun respectievelijke identificatienummers, te sturen naar
    <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a> met als onderwerp <em>"content
        check"</em>.</p>
@endif

<p>Ons team zal je aanvraag zo spoedig mogelijk verwerken en de testevenementen beoordelen.
    Bij activatie ontvang je een bevestigingsmail met je toegang tot de live omgeving.</p>

<p>Heb je nog verdere vragen, contacteer <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a>.
</p>

<p>Met vriendelijke groet,</p>
<p>Het publiq platform team</p>

@include('mails.partials.footer')
