@extends('layouts.clean')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container">
    <div class="full-content-center animated flipInX">
        <h1>404</h1>
        <h2>The page you are looking for is definitely not this!</h2><br>
        <br><br><br>
        <a class="btn btn-primary btn-sm" href="{{ \URL::previous() }}"><i class="fa fa-angle-left"></i> Back to Last Page</a>
        <a class="btn btn-primary btn-sm" href="{{ \URL('/') }}"><i class="fa fa-home"></i> Back to Dashboard</a>
    </div>
</div>
@endsection