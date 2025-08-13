@extends('admin.layout.auth')

@section('content')

<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="login100-pic js-tilt" data-tilt>
                <img src="/image/cms.svg" alt="IMG">
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form class="login100-form validate-form" role="form" method="POST" action="{{ url('/admin/login') }}">
                {{ csrf_field() }}
                <span class="login100-form-title">
                    Admin Login
                </span>
                <div class="{{ $errors->has('email') ? ' has-error' : '' }}">
                    <div class="form-group wrap-input100 validate-input data-validate = "Valid email is required: ex@abc.xyz">
                        <input class="input100" type="text" name="email" placeholder="Email" required value="{{ old('email') }}" autofocus>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="{{ $errors->has('email') ? ' has-error' : '' }}">
                    <div class="wrap-input100 form-group validate-input" data-validate = "Password is required">
                        <input class="input100" type="password" name="password" placeholder="Password" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                
                <div class="container-login100-form-btn">
                    <button type="submit" class="login100-form-btn">
                        Login
                    </button>
                </div>

                <div class="text-center p-t-12">
                    <span class="txt1">
                        Forgot
                    </span>
                    <a class="txt2" href="{{ url('/admin/password/reset') }}">
                        Username / Password?
                    </a>
                </div>

                <div class="text-center p-t-100">
                    
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
