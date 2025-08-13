<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        {{-- <link href='{{ asset('css/material-icon.css') }}' rel="stylesheet"> --}}
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
        <!-- include libraries(jQuery, bootstrap) -->
        <link href="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
        <!-- include summernote css/js -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.css" rel="stylesheet">
        <link href='{{ asset('css/style-v1.1.css') }}' rel="stylesheet">
        <link href='{{ asset('css/util.css?v=1') }}' rel="stylesheet">
        <style type="text/css">
            [v-cloak] > * { display:none };

            body {
              font-family: Arial, sans-serif;
              margin: 0;
            }

            .navbar {
              display: flex;
              align-items: center;
              padding: 10px 20px;
              background-color: #2c3e50;
              color: white;
            }

            .menu-toggle {
              color: white;
              border: none;
              padding: 8px 12px;
              cursor: pointer;
              margin-right: 10px;
            }

            .menu-wrapper {
              display: flex;
              align-items: center;
            }

            .menu-container {
              overflow: hidden;
              max-width: 0;
              transition: max-width 0.4s ease;
/*              background-color: #ecf0f1;*/
              white-space: nowrap;
              border-radius: 4px;
            }

            .menu-container.show {
              max-width: 100%; /* or however wide your menu should be */
            }

            .menu-item {
              display: inline-block;
              padding: 10px 15px;
              color: #2c3e50;
              text-decoration: none;
            }

            .menu-item:hover {
              background-color: #ddd;
            }
        </style>

        @if (Auth::guard('admin')->check())
            <script type="text/javascript">
                var window_user_role = "";
                var window_admin_role = {!!json_encode($role)!!};
                var window_admin_permissions = {!!json_encode($permissions)!!};
                var window_branch = {!!json_encode($branch)!!};
                var window_echelons = {!!json_encode($echelons)!!};
                var window_batches = {!!json_encode($batches)!!};
                var window_years = {!!json_encode($years)!!};
                var window_branch_id = window_branch.id
                var window_search_label = {!!json_encode($search_label)!!};
                var window_batch_label = {!!json_encode($batch_label)!!};
                var window_search_by = {!!json_encode($search_by)!!};
                var window_allow_field = {!!json_encode($allow_field)!!};
                var window_reg_no_label = {!!json_encode($reg_no_label)!!};
                var window_student_type = {!!json_encode($student_type)!!};
                var window_enable_echelon_field = {!!json_encode($enable_echelon_field)!!};
                var window_latest_unpaid = {!!json_encode($latest_unpaid)!!}
                var window_student_field = {!!json_encode($student_form_field)!!}
                // var window_latest_unpaid = {}
            </script>
        @endif
    </head>
    <body>
        <div id="app">
            <div v-cloak>
                <v-app id="keep">
                    {{-- ============== side menu ============== --}}
                    <v-navigation-drawer
                        v-model="drawer"
                        fixed
                        clipped
                        class="teal lighten-2"
                        app
                        mobile-breakpoint="1200"
                        width="280"
                        style="z-index: 2; padding-top: 85px;"
                        >
                        <v-list
                            dense
                            class="teal lighten-2"
                            >
                            <template>
                                @include('admin.layouts.admin_navigation_menu')
                            </template>
                        </v-list>
                    </v-navigation-drawer>
                    {{-- ============== Top Menu ============== --}}
                    <v-app-bar elevate="1" color="teal lighten-2" class="user_nav p-10" style="height: 85px;">
                        <v-app-bar-nav-icon clipped-left @click.native="drawer = !drawer" color="white"></v-app-bar-nav-icon>
                        <img src="/image/logo_1.png" alt="John" width="100" style="margin-right: 50px; margin-left: 20px;" />
                        
                        <div class="menu-wrapper">
                            <button class="menu-toggle" onclick="toggleMenu()">
                                <v-icon class="fs-40" color="white" dark>apps</v-icon>
                            </button>
                            <div class="menu-container" id="menu">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="p-5">
                                                <router-link class="text-center flex cur-p" :to="{name: 'student_list'}">
                                                    <v-icon size="large">person</v-icon>
                                                    <v-btn plain class="p-5 tiny-btn fs-12">Students</v-btn>
                                                </router-link>
                                            </td>
                                            <td class="p-5">
                                                <router-link class="text-center flex cur-p" :to="{name: 'attendance_list'}">
                                                    <v-icon size="large">fingerprint</v-icon>
                                                    <v-btn plain class="p-5 tiny-btn fs-12">Attendance</v-btn>
                                                </router-link>
                                            </td>
                                            <td class="p-5">
                                                <router-link class="text-center flex cur-p" :to="{name: 'student_payment'}">
                                                    <v-icon size="large">request_quote</v-icon>
                                                    <v-btn plain class="p-5 tiny-btn fs-12">Collect Fees</v-btn>
                                                </router-link>
                                            </td>
                                            <td class="p-5">
                                                <router-link class="text-center flex cur-p" :to="{name: 'exam_mark_edit'}">
                                                    <v-icon size="large">note_alt</v-icon>
                                                    <v-btn plain class="p-5 tiny-btn fs-12">Exam List</v-btn>
                                                </router-link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <v-spacer></v-spacer>

                        <v-menu offset-y bottom left transition="fade-transition">
                            <template v-slot:activator="{ on }">
                                <v-btn v-on="on" icon>
                                    <v-avatar size="36">
                                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" />
                                    </v-avatar>
                                </v-btn>
                            </template>

                            <v-list style="min-width: 250px;">
                                <v-list-item-title class="px-4 py-2 font-weight-bold">{{$admin->name}}</v-list-item-title>
                                <v-list-item-subtitle class="px-4 pb-2">@{{random.random_number}} 
                                    <v-icon @click="change_random" size="large">refresh</v-icon>
                                </v-list-item-subtitle>
                                <v-divider></v-divider>
                                <v-list-item link href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                    <v-list-item-icon >
                                        <v-icon>mdi-logout</v-icon>
                                    </v-list-item-icon>
                                    <v-list-item-title>Logout</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </v-app-bar>
                    {{-- ============== router view ============== --}}
                    <v-main>
                        <router-view></router-view>
                    </v-main>
                </v-app>
            </div>
            @yield('content')
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> 
        <script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.js"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.js"></script>
        <script type="text/javascript" src="{{ asset('js/app-v1.1.js?v=11') }}"></script> {{-- 47 --}}
        <script type="text/javascript" src="{{ asset('js/printThis.js') }}"></script>
        <script>
          function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('show');
          }
        </script>
    </body>
</html>