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
        <link href='{{ asset('css/style-v1.1.css') }}' rel="stylesheet">
        <link href='{{ asset('css/util.css') }}' rel="stylesheet">
        <style type="text/css">
            [v-cloak] > * { display:none };
        </style>
        @if (Auth::guard('client')->check())
            <script type="text/javascript">
                var window_authorise = {!! json_encode($authorise) !!};
                var window_branch = {!!json_encode($branch)!!};
                var window_echelons = {!!json_encode($echelons)!!};
            </script>
        @endif
    </head>
    <body>
        <div id="app">
            <div v-cloak>
                <v-app id="keep">
                    <v-toolbar color="amber" class="user_nav">
                        <img src="{{ asset('image/logo.png') }}" height="30px" class="title m-l-5 m-r-10">
                        <v-flex lg9 class="text-xs-center hidden-md-and-down">
                                <v-btn flat to="/"><v-icon class="mr-1">home</v-icon>Home</v-btn>
                                <v-btn flat :to="{name:'student_profile'}"><v-icon class="mr-1">person</v-icon>Profile</v-btn>
                                {{-- <v-btn flat :to="{name:'student_performance'}"> <v-icon class="mr-1">insert_chart_outlined</v-icon>Performance</v-btn> --}}
                                {{-- <v-btn flat :to="{name:'student_material'}"> <v-icon class="mr-1">sticky_note_2</v-icon>Materials</v-btn> --}}
                                @if (Auth::guard('client')->check())
                                    <v-btn flat href="{{ route('client.logout') }}" onclick="event.preventDefault(); document.getElementById('logout_client_form').submit();"><v-icon class="fa fa-sign-out m-r-5"></v-icon>Logout</v-btn>
                                @endif
                        </v-flex>                        
                        <v-flex class="text-xs-center hidden-lg-and-up" style="white-space: nowrap; overflow: auto;">
                            <v-btn flat to="/" style="min-width: 20px; padding: 0px 5px"><v-icon class="mr-1">home</v-icon></v-btn>
                            <v-btn flat :to="{name:'student_profile'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px"><v-icon class="m-r-5 fs-18">person</v-icon>
                            </v-btn>
                            {{-- <v-btn flat :to="{name:'student_performance'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px"><v-icon class="m-r-5 fs-18">insert_chart_outlined</v-icon></v-btn> --}}
                            {{-- <v-btn flat :to="{name:'student_material'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px"><v-icon class="m-r-5 fs-18">sticky_note_2</v-icon></v-btn> --}}
                            @if (Auth::guard('client')->check())
                                <v-btn flat href="{{ route('client.logout') }}" onclick="event.preventDefault(); document.getElementById('logout_client_form').submit();" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px">
                                    <v-icon class="fa fa-sign-out m-r-5 fs-18"></v-icon>
                                </v-btn>
                            @endif
                            
                        </v-flex>
                        <v-spacer class="hidden-md-and-down"></v-spacer>
                    </v-toolbar>
                      
                    <v-content class="m-t-70">
                        <div>
                            <router-view></router-view>
                        </div>
                    </v-content>
                    {{-- login form --}}
                    <form id="logout_client_form" action="{{ route('client.logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </v-app>
            </div>
            @yield('content')
        </div>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> 
        <script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 
        <script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.js"></script>
        <script type="text/javascript" src="{{ asset('js/client_app.js') }}"></script> {{-- 14 --}}
    </body>
</html>