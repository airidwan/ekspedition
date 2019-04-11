@extends('layouts.clean')

@section('title', '500 Internal Server Error')

@section('content')
<div class="container">
    <div class="full-content-center animated flipInX">
        <h1>500</h1>
        <h2>We are unable to show this page to you correctly!</h2>
        <br>
        <p class="text-lightblue-2">Please contact your web administrator</p>
        <br>
        <a class="btn btn-primary btn-sm" href="{{ URL::previous() }}"><i class="fa fa-angle-left"></i> Back to Last Page</a>
        <a class="btn btn-primary btn-sm" href="{{ URL('/') }}"><i class="fa fa-home"></i> Back to Dashboard</a>
    </div>
</div>
@endsection