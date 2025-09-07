<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Inventory System</title>

    <link rel="stylesheet" href="/css/all.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <script>
        (function() {
            window.Laravel = {
                csrfToken: '{{ csrf_token() }}'
            };
        })();
    </script>

</head>

<body>
    <div id="app">

        @if (Auth::check() && Auth::user()->role->permission)
            <mainapp :user="{{Auth::user()}}" :role="{{Auth::user()->role}}"  :branch="{{Auth::user()->branch}}" :department="{{Auth::user()->department}}" :permission="{{Auth::user()->role->permission}}"></mainapp>
        @else
            <login_external :user="false"></login_external>
        @endif
    </div>
</body>
{{-- <script src="{{mix('/js/app.js')}}"></script> --}}
<script src="{{ asset('js/app.js') }}"></script>

</html>
