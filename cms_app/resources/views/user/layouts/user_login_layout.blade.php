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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login_style.css') }}" rel="stylesheet">
    <link href='{{ asset('css/util.css') }}' rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script> 
    <script type="text/javascript">
        $(document).ready(function(){
            if ($('#teacher_login_form .tch_err').length){
                $('#student_login_form').hide();
                $('#student_btn_text').hide();
                $('#teacher_login_form').show();
                $('#teacher_btn_text').show();
                $('#teacher_login_btn').css('width', '70%');
                $('#student_login_btn').css('width', '20%');
            }
            if ($('#student_login_form .std_err').length){
                $('#teacher_login_form').hide();
                $('#teacher_btn_text').hide();
                $('#student_login_form').show();
                $('#student_btn_text').show();
                $('#teacher_login_btn').css('width', '20%');
                $('#student_login_btn').css('width', '70%');
            }
            $("#teacher_login_btn, #teacher_img").click(function(e){
                e.preventDefault();
                if ($(window).width() > 768) {
                    $('#teacher_login_btn').animate({width: '70%'}, "fast");
                    $('#student_login_btn').animate({width: '20%'}, "fast");
                    $('#teacher_btn_text').show('fast');
                    $('#student_btn_text').fadeOut('fast');
                } else {
                    $('#teacher_login_btn').css('opacity', 1);
                    $('#student_login_btn').css('opacity', .5);
                    $('#teacher_img').css('opacity', 1);
                    $('#student_img').css('opacity', .5);
                }
                $('#teacher_login_form').fadeIn('slow');
                $('#student_login_form').hide();
            });
            $("#student_login_btn, #student_img").click(function(e){
                e.preventDefault();
                if ($(window).width() > 768) {
                    $('#student_login_btn').animate({width: '70%'}, "fast");
                    $('#teacher_login_btn').animate({width: '20%'}, "fast");
                    $('#student_btn_text').show('fast');
                    $('#teacher_btn_text').fadeOut('fast');
                } else {
                    $('#teacher_login_btn').css('opacity', .5);
                    $('#student_login_btn').css('opacity', 1);
                    $('#teacher_img').css('opacity', .5);
                    $('#student_img').css('opacity', 1);
                }
                $('#student_login_form').fadeIn('slow');
                $('#teacher_login_form').hide();
            });
        });
    </script>
    
</body>
</html>
