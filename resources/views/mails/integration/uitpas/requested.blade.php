@include('mails.partials.header')

<p>We hebben je aanvraag tot activatie voor de integratie met naam {{ $integrationName }} voor de organisator(en) <strong>{{ $organizerName }}</strong> goed ontvangen.</p>

<h2>Bouw je voor de eerste keer een integratie met UiTPAS?</h2>
<p>We vragen dan een kleine <strong>live demo</strong> om de correcte werking even te valideren. Contacteer
    <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a> om een videomoment in te plannen.
    We bekijken dan vooral of je de richtlijnen uit de documentatie toepast, bijvoorbeeld met betrekking tot het registreren van kortingen.<br>
    Als alles oké is, krijg je dezelfde dag de productie-credentials.</p>

<h2>Is je integratie bij ons al bekend en vraag je toegang tot extra organisatoren?</h2>
<p>Ons team behandelt je aanvraag zo snel mogelijk. Bij activatie ontvang je een bevestigingsmail van ons met je toegang tot de live omgeving.</p>

<p>Heb je nog verdere vragen, contacteer <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a>.</p>

<p>Vriendelijke groet,</p>

<p>Het publiq platform team.</p>

@include('mails.partials.footer')
