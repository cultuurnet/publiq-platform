@include('mails.partials.header')

<p>Een jaar geleden maakte je de integratie <strong>{{ $integrationName }}</strong> aan op het publiq-platform.</p>

<p>We hebben opgemerkt dat deze integratie momenteel nog met testdata werkt en dat er nog geen activatie-aanvraag is
    ingediend.</p>

<p>Om ons publiq-platform zo ordelijk mogelijk te houden, verwijderen we graag de ongebruikte integraties. Indien jullie
    toch gebruik willen maken van de integratie, laat het ons dan zeker weten.</p>

<h2>Liep je ergens vast tijdens het opstellen van de integratie?</h2>
<p>We helpen je graag verder om je integratie volledig operationeel te maken.
    Als je technische ondersteuning nodig hebt, kun je contact opnemen via
    <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a>.
    Voor andere vragen met betrekking tot je integratie kun je ons bereiken op
    <a href="mailto:partnerships@publiq.be">partnerships@publiq.be</a>.
</p>

<p>Zonder respons op deze mail wordt de integratie verwijderd.</p>

<p>Met vriendelijke groet,</p>
<p>Het publiq platform team</p>

@include('mails.partials.footer')
