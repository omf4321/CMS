@extends('user.layouts.user_login_layout')

@section('content')

<div class="limiter">
    <div class="container-login100" style="background-image: url('images/bg-01.jpg');">
        <div class="wrap-login100 p-l-100 p-r-100 p-t-62 p-b-33">
            <div class="login100-form validate-form flex pos-rel" style="justify-content: space-between; flex-wrap: wrap;">
                <div class="logo text-center w-full m-b-10">
                    <a href="/"><img src="{{ asset('image/logo.png') }}" alt="logo" style="width: 90px;"></a>
                </div>
                <span class="login100-form-title p-b-53 fs-22 fw-600 hind"> Confirm Your Payment </span>

                @if(session()->has('message'))
                    <div class="alert alert-success w-full">
                        {{ session()->get('message') }}
                    </div>
                @endif

                @if(session()->has('error'))
                    <div class="alert alert-danger w-full">
                        {{ session()->get('error') }}
                    </div>
                @endif


                @if($errors->any())
                    <div class="alert alert-danger w-full">
                        @foreach ($errors->all() as $error)
                            <div>{{$error}}</div>
                        @endforeach
                    </div>
                @endif

                <form id="teacher_login_form" class="w-full" method="POST" action="{{ route('confirm-payment') }}">
                    {{ csrf_field() }}
                    <div class="p-t-13 p-b-9">
                        <span class="txt1 hind fw-600"> Registration No </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="number" name="reg_no" required value="{{ old('reg_no') }}">
                    </div>
                    @if ($errors->has('reg_no'))
                        <span class="help-block fc-red"> <strong>{{ $errors->first('reg_no') }}</strong> </span>
                    @endif
                    
                    <div class="p-t-25 p-b-9">
                        <span class="txt1 hind fw-600"> Pay To </span>
                    </div>
                    <div style="padding: 0px 20px">
                        <label class="radio p-b-10">
                            <span class="radio__input">
                                <input type="radio" name="pay_to" value="01688554488" {{ old('pay_to') == '01688554488' ? "checked" : "" }}>
                                {{-- <span class="radio__control"></span> --}}
                            </span>
                            <span class="radio__label">01688-554488 (Merchant)</span>
                        </label>

                        <label class="radio p-b-10">
                            <span class="radio__input">
                                <input type="radio" name="pay_to" value="01676352571" {{ old('pay_to') == '01676352571' ? "checked" : "" }}>
                                {{-- <span class="radio__control"></span> --}}
                            </span>
                            <span class="radio__label">01676-352571 (Personal)</span>
                        </label>
                    </div>

                    <div class="p-t-13 p-b-9">
                        <span class="txt1 hind fw-600"> Bkash Number (Last 4 Digit) </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="number" name="bkash_number" required value="{{ old('bkash_number') }}">
                    </div>


                    <div class="p-t-13 p-b-9">
                        <span class="txt1 hind fw-600"> Month </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <select class="input100" name="month" style="padding: 10px; width: 100%">
                            <option {{ old('month') == 1 ? "selected" : "" }} value="1">January</option>
                            <option {{ old('month') == 2 ? "selected" : "" }} value="2">February</option>
                            <option {{ old('month') == 3 ? "selected" : "" }} value="3">March</option>
                            <option {{ old('month') == 4 ? "selected" : "" }} value="4">April</option>
                            <option {{ old('month') == 5 ? "selected" : "" }} value="5">May</option>
                            <option {{ old('month') == 6 ? "selected" : "" }} value="6">June</option>
                            <option {{ old('month') == 7 ? "selected" : "" }} value="7">July</option>
                            <option {{ old('month') == 8 ? "selected" : "" }} value="8">August</option>
                            <option {{ old('month') == 9 ? "selected" : "" }} selected value="9">September</option>
                            <option {{ old('month') == 10 ? "selected" : "" }} value="10">October</option>
                            <option {{ old('month') == 11 ? "selected" : "" }} value="11">November</option>
                            <option {{ old('month') == 12 ? "selected" : "" }} value="12">December</option>
                        </select>
                    </div>

                    <div class="container-login100-form-btn m-t-17">
                        <button class="login100-form-btn"> Submit </button>
                    </div>
                    <div class="w-full text-center p-t-55">
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
