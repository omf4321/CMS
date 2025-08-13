@extends('admin.layout.auth')

@section('content')

<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100" style="padding-top: 40px">

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <form class="row" method="POST" action="{{ route('admin.setup.save') }}" enctype="multipart/form-data" class="m-auto">
                @csrf
                <span class="login100-form-title hind fw-600">
                    Welcome! Set Up Your Organization
                </span>
                <div class="col-lg-7">
                    <!-- Company Name -->
                    <div class="form-group required">
                        <label for="organization_name">Name of your Organization</label>
                        <input type="text" class="form-control" name="organization_name" id="organization_name" required placeholder="XYZ Learning Center">
                    </div>
                    <!-- Company Name -->
                    <div class="form-group required">
                        <label for="branch_quantity">No of Branch You Have</label>
                        <input type="number" class="form-control" name="branch_quantity" id="branch_quantity" required placeholder="1">
                    </div>
                    <!-- Company Name -->
                    <div class="form-group required">
                        <label for="branch_name">This Branch Name</label>
                        <input type="text" class="form-control" name="branch_name" id="branch_name" required placeholder="Chawkbazar Branch">
                    </div>

                    <!-- System Type -->
                    
                    <div class="{{ $errors->has('system_type') ? 'has-error' : '' }} system-type-container">
                        <div class="wrap-input100 form-group validate-input" data-validate="This Field is required">
                            <label class="fs-16 fw-600 mb-3 m-l-0" for="system_type">System Type</label>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="system_type" id="school" value="school" required>
                                <label class="form-check-label" for="school">School <small>(Class - Section - Shift)</small></label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="system_type" id="coaching" value="coaching" required>
                                <label class="form-check-label" for="coaching">Coaching <small>(Class - Batch)</small></label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="system_type" id="single" value="single" required>
                                <label class="form-check-label" for="single">Single Class/Course <small>(Only Batch e.g., BCS Coaching)</small></label>
                            </div>
                        </div>
                    </div>

                    <!-- Logo Upload -->
                    <div class="form-group required">
                        <label for="logo">Institute Logo</label>
                        <input type="file" class="form-control-file" name="logo" id="logo" accept="image/*">
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm p-3" style="max-height: 600px; overflow-y: auto;">
                        <h5 class="mb-3 fw-600 hind" style="line-height: 1.5;">নিচের তথ্যগুলো সবার জন্য প্রয়োজনীয় নয়। আপনার প্রয়োজনীয় তথ্যগুলো নির্বাচন করুন</h5>
                        @foreach($student_setup_field as $key => $value)
                            @if(is_array($value) && isset($value['options']))
                                <div class="form-group mt-3">
                                <strong>{{ $value['label'] }}</strong>
                                <div class="ml-3 mt-2">
                                    @foreach($value['options'] as $optKey => $optValue)
                                    <div class="form-check">
                                        <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="admission_fields[]" 
                                        id="{{ $optKey }}" 
                                        value="{{ $optKey }}"
                                        { (old('admission_fields') && in_array($optKey, old('admission_fields'))) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label  m-l-20 fs-14 m-t-4" for="{{ $optKey }}">{{ $optValue }}</label>
                                    </div>
                                    @endforeach
                                </div>
                                </div>
                            @else
                                <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="admission_fields[]" 
                                    id="{{ $key }}" 
                                    value="{{ $key }}"
                                    {{ (old('admission_fields') && in_array($key, old('admission_fields'))) ? 'checked' : '' }}
                                >
                                <label class="form-check-label m-l-20 fs-14 m-t-4" for="{{ $key }}">{{ $value }}</label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    </div>

                <div class="container-login100-form-btn">
                    <button type="submit" class="login100-form-btn">
                        Save & Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@section('css')
<style>
    .form-check-input,
    .form-check-label {
        cursor: pointer;
    }
    .system-type-container {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .form-check-input {
        transform: scale(1.3);
        cursor: pointer;
    }

    .form-check-label {
        font-size: 16px;
        font-weight: 500;
        margin-left: 10px;
        cursor: pointer;
    }

    .form-check {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
    }

    .wrap-input100 label {
        display: block;
        margin-bottom: 0px;
        margin-left: 20px;
    }

    .has-error {
        border-left: 3px solid #dc3545;
        padding-left: 10px;
    }
</style>
@endsection