@extends('user.layouts.user_login_layout')

@section('content')

<div class="container limiter">
    <div class="container-login100" style="background-image: url('images/bg-01.jpg');">
        <div class="wrap-login100 p-l-100 p-r-100 p-t-62 p-b-33">
            <div class="login100-form validate-form flex pos-rel" style="justify-content: space-between; flex-wrap: wrap;">
                <span class="login100-form-title p-b-20"> Teacher Login </span>
                
                <form id="teacher_login_form" class="w-full" method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}
                    <div class="p-t-13 p-b-9">
                       <span class="txt1"> Registration No </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="user_reg_no" required value="{{old('email')}}">
                        <span class="focus-input100"></span>
                    </div>
                    @if ($errors->has('email'))
                        <span class="help-block tch_err fc-red"> <strong>{{ $errors->first('email') }}</strong> </span>
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

                    <div class="w-full text-center p-t-55">
                    <a class="txt2 bo1">On Forget Password Contact with Authority </a> </div>
                </form>

                {{-- <form id="student_login_form" style="display: none;" class="w-full" method="POST" action="{{ route('client.login') }}">
                    {{ csrf_field() }}
                    <div class="p-t-13 p-b-9">
                        <span class="txt1"> Registration No </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="user_reg_no" required>
                        <span class="focus-input100"></span>
                    </div>
                    @if ($errors->has('user_reg_no'))
                        <span class="help-block std_err fc-red"> <strong>{{ $errors->first('user_reg_no') }}</strong> </span>
                    @endif
                    
                    <div class="p-t-13 p-b-9">
                        <span class="txt1"> Password </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="password" name="password" required>
                        <span class="focus-input100"></span>
                    </div>
                    <div class="checkbox">
                        <label> <input type="checkbox" name="remember"> Keep Me Sign In </label>
                    </div>

                    <div class="container-login100-form-btn m-t-17">
                        <button class="login100-form-btn"> Sign In </button>
                    </div>

                    <div class="w-full text-center p-t-55">
                    <a href="{{ route('client.password.reset') }}" class="txt2 bo1"> Forget Password </a> </div>
                </form> --}}
            </div>
        </div>
    </div>
</div>
@endsection
