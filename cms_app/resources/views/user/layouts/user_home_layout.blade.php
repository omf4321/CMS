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
        <link href="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
        <!-- include summernote css/js -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.css" rel="stylesheet">
        <link href='{{ asset('css/style-v1.1.css') }}' rel="stylesheet">
        <link href='{{ asset('css/util.css') }}' rel="stylesheet">
        <style type="text/css">
            [v-cloak] > * { display:none };
        </style>
        @if (Auth::guard('web')->check())
            <script type="text/javascript">
                var window_user_role = {!! json_encode($roles) !!};
                var window_admin_role = null;
                var window_admin_permissions = [];
                var window_teacher_id = {!! json_encode($teacher->id) !!};
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
                        <router-link to="/">
                            {{-- <img src="{{ asset('image/logo.png') }}" height="30px" class="title m-l-5 m-r-10"> --}}
                            <h3 class="title m-l-5 m-r-10 m-t-10">LOGO</h3>
                        </router-link>
                        <v-flex lg9 class="text-xs-center hidden-md-and-down">
                              <v-btn flat :to="{name:'profile'}"><v-icon class="mr-1">person</v-icon>Profile</v-btn>
                              @role('teacher', 'web')
                                <v-btn flat :to="{name:'user_schedule'}">
                                    <v-icon class="mr-1 fa fa fa-calendar-o"></v-icon>Schedule
                                </v-btn>
                              @endrole
                              {{-- @role('teacher', 'web')
                              <v-btn flat :to="{name:'user_question_list'}"><v-icon class="mr-1 fa fa-question-circle-o"></v-icon>Question</v-btn>
                              @endrole --}}
                              <v-btn flat :to="{name:'user_payment'}"><v-icon class="mr-1">payment</v-icon>
                              Payment</v-btn>
                              @if (Auth::guard('web')->check())
                                @if (Auth::guard('web')->user()->teachers->act_as_teacher == 1)
                                    <v-btn flat href="/user/act_as_teacher"><v-icon class="fa fa-sign-out m-r-5"></v-icon>Logout</v-btn>
                                @else
                                    <v-btn flat href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><v-icon class="fa fa-sign-out m-r-5"></v-icon>Logout</v-btn>
                                @endif
                              @endif
                        </v-flex>                        
                        <v-flex class="text-xs-center hidden-lg-and-up" style="white-space: nowrap; overflow: auto;">
                            <v-btn flat :to="{name:'profile'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px">
                              <v-icon class="m-r-5 fs-18">person</v-icon>
                            </v-btn>
                            @role('teacher', 'web')
                            <v-btn flat :to="{name:'user_schedule'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px">
                              <v-icon class="m-r-5 fs-18 fa fa-calendar-o"></v-icon>
                            </v-btn>
                            {{-- <v-btn flat :to="{name:'user_question_list'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px">
                              <v-icon class="m-r-5 fs-18 fa fa-question-circle-o"></v-icon>
                            </v-btn> --}}
                            @endrole
                            <v-btn flat :to="{name:'user_payment'}" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px">
                              <v-icon class="m-r-5 fs-24">payment</v-icon>
                            </v-btn>
                            @if (Auth::guard('web')->check())
                                @if (Auth::guard('web')->user()->teachers->act_as_teacher == 1)
                                    <v-btn flat href="/user/act_as_teacher"><v-icon class="fa fa-sign-out m-r-5"></v-icon></v-btn>
                                @else
                                    <v-btn flat href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="fs-14 fw-700" style="min-width: 20px; padding: 0px 5px">
                                        <v-icon class="fa fa-sign-out m-r-5 fs-18"></v-icon>
                                    </v-btn>
                                @endif
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
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </v-app>
            </div>
            @yield('content')
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> 
        <script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.js"></script>
        <script type="text/javascript" src="{{ asset('js/user_app-v1.31.js') }}"></script> {{-- 25 --}}
    </body>
</html>