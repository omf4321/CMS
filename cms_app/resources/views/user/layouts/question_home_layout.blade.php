<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Sohopath') }}</title>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- include libraries(jQuery, bootstrap) -->
        <link href="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
        <!-- include summernote css/js -->
        <link href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.css" rel="stylesheet">
        <link href='{{ asset('css/style.css') }}' rel="stylesheet">
        <link href='{{ asset('css/util.css'.time()) }}' rel="stylesheet">
        <style type="text/css">
            [v-cloak] > * { display:none };
        </style>
        <script type="text/javascript">
            var window_question_add = true
        </script>
    </head>
    <body>
        <div id="app">
            <div v-cloak>
                <v-app id="keep">
                    <v-toolbar color="amber" class="user_nav">
                        <a href="/">
                            <img src="{{ asset('image/logo.png') }}" height="30px" class="title m-l-5 m-r-10">
                        </a>
                    </v-toolbar>
                      
                    <v-content class="m-t-70">
                        <div>
                            <question_add></question_add>
                        </div>
                    </v-content>
                </v-app>
            </div>
            @yield('content')
        </div>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> 
        <script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 
        <script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.js"></script>
        <script type="text/javascript" src="{{ asset('js/question_app.js') }}"></script>
    </body>
</html>