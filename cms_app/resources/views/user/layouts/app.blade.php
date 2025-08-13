<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Styles -->
    @include('user/layouts/head')
    @yield('style')
    @yield('script')

</head>

<body id="user-body" class="">
    <div id="app" class="animsition"> 

        @include('user/layouts/header_menu')
        <div :class="{'login_overlay': login_load||register_load}" style="display: none;"></div>
        <br>
        <br>
        @if(Session::has('success-register'))
            <div class="container alert alert-success text-center">
                <a class="close" data-dismiss="alert" aria-label="close">×</a>
                {{ Session::get('success-register') }}
            </div>
        @elseif(Auth::check())
            @if (Auth::user()->verify_email==0)
                <div v-cloak class="container alert alert-danger text-center">
                    <a class="close" data-dismiss="alert" aria-label="close">×</a>
                    Please check email and verify your account.
                    <a href="" @click.prevent="resend_verifaction()" v-if="!verification_msg && verify_load==false">Resend Verification Email <span class="fa fa-send-o"> </span></a>
                    <a v-if="verify_load==true">
                        <div class="a" style="--n: 5; text-align:center">
                            <div class="dot_loader" style="--i: 0;"></div>
                            <div class="dot_loader" style="--i: 1;"></div>
                            <div class="dot_loader" style="--i: 2;"></div>
                            <div class="dot_loader" style="--i: 3;"></div>
                            <div class="dot_loader" style="--i: 4;"></div>
                        </div>
                    </a>
                    <span v-if="verification_msg && verify_load==false" class="fc-black">@{{verification_msg}}</span>
                </div>
            @endif
        @endif
        <section id="banner">
            @yield('content')
        </section>

        <br>
        <br>
        
        @include('user/layouts/footer')

        @include('user/layouts/search')
        @include('user/layouts/login_modal')

    </div>

    

    {{-- Script --}}
    @include('user/layouts/script')
    @yield('script')
    
</body>
</html>
