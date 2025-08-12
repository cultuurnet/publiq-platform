@include('mails.partials.header')

<p>Je integratie met naam {{ $integrationName }} is zonet aangemaakt op het publiq platform. Je kan meteen
    starten met de ontwikkeling van je integratie op onze testomgeving. Je toegangssleutels vind je op je
    integratiepagina: <a href="{{ $integrationDetailpage }}">{{ $integrationDetailpage }}</a>.</p>

@include('mails.partials.button', [
    'buttonText' => 'Bekijk integratie',
    'organisationUrl' => $integrationDetailpage
])

<h2>Test-omgeving</h2>
<p>We hebben je permissies ingesteld voor de test-organisator. Met de test-dataset kan je dus meteen aan de slag op de
    testomgeving.</p>
<ul>
    <li>[TEST] UiTPAS Organisatie (Regio Gent + Paspartoe) met ID 0ce87cbc-9299-4528-8d35-92225dc9489f</li>
</ul>
<p>Je vindt alvast alle informatie in onze documentatie.</p>

<h2>Productie-omgeving</h2>
<p>Wanneer je klaar bent om naar productie te gaan, klik dan op “Activatie aanvragen” in je integratie op publiq
    platform. Je integratie krijgt rechten om acties uit te voeren voor specifieke UiTPAS-organisaties. Je kan ook later
    nog toegang vragen tot extra UiTPAS-organisaties via de tab “Organisaties” in je integratie project.<br>
    <strong>We vragen eerst een kleine live demo om de correcte werking te valideren.</strong> Aangezien de registratie
    van kortingen financiële gevolgen heeft voor UiTPAS-organisaties en gemeenten, is dit een belangrijke stap om
    misverstanden of fouten te voorkomen.<br>
    Als je integratie voldoende getest is, neem dan <a href="mailto:technical-support@publiq.be">contact met ons op</a>
    om een videocall in te plannen. Als alles oké is, krijg je dezelfde dag de productie-credentials.</p>

<h2>Enkele UiTPAS-Tips en handige links:</h2>
<ul>
    <li><strong>Authenticatie:</strong> gebruik <a
            href="https://docs.publiq.be/docs/authentication/methods/client-access-token">client access tokens</a>.
        Vraag daarbij enkel een nieuw token wanneer het oude token vervallen is, aan de hand van <em>expires_in</em>.
    </li>
    <li>Voor de registratie van kansentarieven is het belangrijk om <a
            href="https://docs.publiq.be/docs/uitpas/ticket-sales/registering">onze gids</a> nauwgezet te volgen.
    </li>
    <li><a href="https://publiq.stoplight.io/docs/uitpas/introduction">Documentatie over de de UiTPAS API - voor alle acties gerelateerd aan de UiTPAS-kaart</a></li>
    <li><a href="https://publiq.stoplight.io/docs/uitdatabank/entry-api/introduction">Documentatie over de entry API - voor alle acties gerelateerd aan de invoer van events in de UiTdatabank</a></li>
    <li>Zorg er ook zeker voor dat wanneer een betaling niet doorgaat (bv annulatie achteraf), <a
            href="https://docs.publiq.be/docs/uitpas/uitpas-api/reference/operations/delete-a-ticket-sale">het
            geregistreerde kansentarief geannuleerd wordt</a> (anders zou de gemeente onterecht terug betalen!)
    </li>
    <li><a href="https://docs.publiq.be/docs/uitpas/9fg84nc7ean2s-user-friendly-error-messages">Gebruiksvriendelijke
            foutmeldingen</a><br/>
        Onder andere bij het registreren van kansentarieven zijn er heel wat mogelijke foutmeldingen, bv de kaart van de
        pashouder is geblokkeerd of de pashouder heeft geen recht meer op kansentarief.<br>
        We geven in onze respons steeds een ‘endUserMessage’ terug dat begrijpelijk is voor de eindgebruiker, zodat je
        deze fouten niet zelf moet vertalen. <strong>Toon dit bericht steeds aan de eindgebruiker zodat deze begrijpt
            waarom iets niet lukte.</strong></li>
    <li><strong>Opgelet:</strong> indien je bv. als ticketing systeem verschillende klanten-omgevingen hebt, <strong>dan
            moet je per klant een integratie-project aanvragen</strong>, en zal je dus per klant een client id en secret
        hebben.
    </li>
</ul>

<h2>Troubleshooting</h2>
<p>Heb je technische vragen over je integratie, dan kan je ons bereiken via <a
        href="mailto:technical-support@publiq.be">technical-support@publiq.be</a> of via het Slack-kanaal
    <em>#technical-support-publiq</em>. Toegang tot het Slack-kanaal kan je aanvragen via deze pagina (‘Support via
    Slack’ ->
    ‘Toegang vragen’).</p>
<p><strong>Geef bij je vragen steeds volgende informatie mee</strong> (indien van toepassing), zodat we je zo snel
    mogelijk kunnen
    helpen:</p>
<ul>
    <li>
        Je client-id(s), of je API-key(s)
    </li>
    <li>
        De omgeving waarin je problemen ondervindt (test, productie of beide)
    </li>
    <li>
        Een voorbeeld van de HTTP requests die je verstuurt (inclusief URL, method, headers en body data)
    </li>
    <li>
        Een voorbeeld van de HTTP response(s) die je ontvangt (inclusief status, headers en body data)
    </li>
</ul>

<p>We horen ook graag van je hoe voor jou de integratie met onze API’s verliep. Wil je daarom onderstaande vragenlijst
    invullen?</p>

<p><a href="https://tally.so/r/wg5adm" target="_blank">Naar de vragenlijst</a></p>

<p>Veel succes met je integratie,</p>

<p>Het publiq platform team.</p>

@include('mails.partials.footer')
