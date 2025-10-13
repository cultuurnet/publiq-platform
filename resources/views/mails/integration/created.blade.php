@include('mails.partials.header')

<p>Je integratie met naam {{ $integrationName }} is zonet met succes aangemaakt op het publiq platform. Je kan meteen
    starten met de ontwikkeling van je integratie op onze testomgeving. Je toegangssleutels vind je op je
    integratiepagina: <a href="{{ $url }}">{{ $url }}</a>.</p>

@include('mails.partials.button', [
    'buttonText' => 'Bekijk integratie',
    'organisationUrl' => $url
])

@if ($type === 'search-api')
    <p>Ben je een lokaal bestuur en lid van het UiTnetwerk?<br>
        Dan krijg je gratis toegang tot de productieomgeving van UiTdatabank voor het gebruik van de Search API.
        Vraag de activatie gewoon aan via je integratiepagina.
        We activeren de toegang binnen enkele werkdagen, een couponcode is niet meer nodig.</p>
@endif

@if ($type !== 'widgets')
<p>Om snel van start te kunnen gaan raden we je zeker aan om volgende documentatie door te nemen:</p>

<ul>
    <li><a href="https://publiq.stoplight.io/docs/authentication" target="_blank">Authenticatie</a></li>
    @if ($type === 'search-api')
        <li><a href="https://publiq.stoplight.io/docs/uitdatabank/search-api/introduction">Search API</a></li>
    @elseif ($type === 'entry-api')
        <li><a href="https://publiq.stoplight.io/docs/uitdatabank/entry-api/introduction">Entry API</a></li>
    @endif
</ul>
@endif


<p>Heb je technische vragen over je integratie, dan kan je ons bereiken op
    <a href="mailto:technical-support@publiq.be">technical-support@publiq.be</a>.</p>

<p>Geef bij vragen steeds volgende informatie mee (indien van toepassing), zodat we je zo snel mogelijk kunnen
    helpen:</p>

<ul>
    <li>Je client-id(s), of je API-key(s) (de secret geef je niet mee)</li>
    <li>De omgeving waarin je problemen ondervindt (test, productie of beide)</li>
    <li>Een voorbeeld van de HTTP requests die je verstuurt (inclusief URL, method, headers en body data)</li>
    <li>Een voorbeeld van de HTTP response(s) die je ontvangt (inclusief status, headers en body data)</li>
</ul>

<p>Veel succes met je integratie,</p>
<p>Het publiq platform team.</p>

@include('mails.partials.footer')
