@include('mails.partials.header')

<p>Je integratie met naam {{ $integrationName }} is geactiveerd🎉.</p>

<p>Je toegangssleutels voor de liveomgeving vind je op je integratiepagina:
    <a href="{{ $url }}">{{ $url }}</a>.</p>

@include('mails.partials.button', [
    'buttonText' => 'Bekijk integratie',
    'organisationUrl' => $url
])

<p>We horen ook graag van je hoe voor jou de integratie met onze API’s verliep. Wil je daarom onderstaande vragenlijst
    invullen?</p>

<p><a href="https://tally.so/r/wg5adm" target="_blank">Naar de vragenlijst</a></p>

<p>Het invullen duurt slechts 5 minuten en helpt ons enorm onze producten en services verder te optimaliseren.</p>

<p>Bedankt!</p>

<p>Het publiq platform team.</p>

@include('mails.partials.footer')
