@extends('user.layouts.user_login_layout')

@section('content')

<table class="table table-bordered table-responsive">
    <thead>
        <tr>
            <th>Reg No</th>
            <th>Pay To</th>
            <th>Bkash Number</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($confirmation as $list)
        <tr>
            <td>{{$list->reg_no}}</td>
            <td>{{$list->pay_to}}</td>
            <td>{{$list->bkash_number}}</td>
            <td>{{$list->created_at}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
