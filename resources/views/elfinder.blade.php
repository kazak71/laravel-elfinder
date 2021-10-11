<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <title>Файлов бразусер</title>

{{--         <!-- jQuery and jQuery UI (REQUIRED) -->
        {!! Html::style('js/jquery-ui/jquery-ui.css') !!}
        {!! Html::script('js/jquery.js') !!}
        {!! Html::script('js/jquery-ui/jquery-ui.min.js') !!} --}}

                <!-- jQuery and jQuery UI (REQUIRED) -->
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

        <!-- elFinder CSS (REQUIRED) -->
        <link rel="stylesheet" type="text/css" href="{{ asset($dir.'/css/elfinder.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset($dir.'/css/theme.css') }}">

        <!-- elFinder JS (REQUIRED) -->
        <script src="{{ asset($dir.'/js/elfinder.min.js') }}"></script>

        @if($locale)
            <!-- elFinder translation (OPTIONAL) -->
            <script src="{{ asset($dir."/js/i18n/elfinder.$locale.js") }}"></script>
        @endif

        <!-- elFinder initialization (REQUIRED) -->
        <script type="text/javascript" charset="utf-8">
            // Documentation for client options:
            // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
            $().ready(function() {
                var elf = $('#elfinder').elfinder({
                    // set your elFinder options here
                    @if($locale)
                        lang: '{{ $locale }}', // locale
                    @endif
                    customData: {
                        _token: '{{ csrf_token() }}'
                    },
                    url : '{{ route("elfinder.connector") }}',  // connector URL
                    url : '{{ route("elfinder.connector",["rootDir"=>$rootDir]) }}',  // connector URL
                    soundPath: '{{ asset($dir.'/sounds') }}',
                    height : $(window).height()-20,
                    resizable: false,
                }).elfinder('instance');
                elf.exec('fullscreen');

                var resizeTimer;
                $(window).resize(function() {
                    resizeTimer && clearTimeout(resizeTimer);
                    if (! $('#elfinder').hasClass('elfinder-fullscreen')) {
                        resizeTimer = setTimeout(function() {
                            var h = parseInt($(window).height()-20);
                            if (h != parseInt($('#elfinder').height())) {
                                elf.resize('100%', h);
                            }
                        }, 200);
                    }
                });
            });
        </script>
    </head>
    <body>

        <!-- Element where elFinder will be created (REQUIRED) -->
        <div id="elfinder"></div>

    </body>
</html>
