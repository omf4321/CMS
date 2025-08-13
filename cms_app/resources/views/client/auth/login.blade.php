@extends('user.layouts.user_login_layout')

@section('content')

<div class="container limiter">
    <div class="container-login100" style="background-image: url('images/bg-01.jpg');">
        <div class="wrap-login100 p-l-100 p-r-100 p-t-62 p-b-33">
            <div class="login100-form validate-form flex pos-rel" style="justify-content: space-between; flex-wrap: wrap;">
                <span class="login100-form-title p-b-20"> Student Login </span>
                
                <form id="teacher_login_form" class="w-full" method="POST" action="{{ route('client.login') }}">
                    {{ csrf_field() }}
                    <div class="p-t-13 p-b-9">
                       <span class="txt1"> Registration Number </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="username" required value="{{old('username')}}">
                        <span class="focus-input100"></span>
                    </div>
                    @if ($errors->has('reg_no'))
                        <span class="help-block tch_err fc-red"> <strong>{{ $errors->first('reg_no') }}</strong> </span>
                    @endif
                    
                    <div class="p-t-13 p-b-9">
                        <span class="txt1"> Password </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="password" name="password" required>
                        <span class="focus-input100"></span>
                    </div>
                    @if ($errors->has('password'))
                        <span class="help-block tch_err fc-red"> <strong>{{ $errors->first('password') }}</strong> </span>
                    @endif
                    <div class="checkbox" style="display: none;">
                        <label> <input type="checkbox" name="remember" value="true"> Keep Me Sign In </label>
                    </div>

                    <div class="container-login100-form-btn m-t-17">
                        <button class="login100-form-btn"> Sign In </button>
                    </div>

                    <div class="w-full text-center p-t-30">
                    <span class="">If you Get "The page has expired due to inactivity. Please reload and try again." </span> <a class="text-danger" style="text-decoration: underline;" href="/">Click here then click to student and login again</a> </div>

                    <div class="w-full text-center p-t-55">
                    <a class="txt2 bo1">On Forget Password or Any Problem with Login Please Contact with Sohopath Authority </a> </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
