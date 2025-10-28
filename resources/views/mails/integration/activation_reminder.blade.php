@include('mails.partials.header')

<p>Een tijdje geleden maakte je de integratie {{ $integrationName }} aan op het publiq-platform. We hebben opgemerkt dat
    deze integratie momenteel nog met testdata werkt en dat er nog geen activatie-aanvraag is ingediend.</p>

<p>We helpen je graag verder om je integratie volledig operationeel te maken. Als je technische ondersteuning nodig
    hebt, kun je contact opnemen via <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a>. Voor andere vragen met betrekking tot je integratie
    kun je ons bereiken op <a href="mailto:partnerships@publiq.be">partnerships@publiq.be</a>.</p>

<p>We staan klaar om je te ondersteunen!</p>

<p>Met vriendelijke groet,</p>
<p>Het publiq platform team</p>

@include('mails.partials.footer')
