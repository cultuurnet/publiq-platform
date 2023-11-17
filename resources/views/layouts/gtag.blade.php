@php $tagId = config('gtag.gtagMeasurementId') @endphp
@if ($tagId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $tagId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $tagId }}');
    </script>
@endif
