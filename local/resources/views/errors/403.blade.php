@extends('layouts.clean')

@section('title', '403 - Access Denied')

@section('content')
<div class="container">
    <div class="full-content-center animated flipInX">
        <h1>403</h1>
        <h2>You dont have access to this page</h2><br>
        <br><br><br>
        <a class="btn btn-primary btn-sm" href="{{ \URL::previous() }}"><i class="fa fa-angle-left"></i> Back to Last Page</a>
        <a class="btn btn-primary btn-sm" href="{{ \URL('/') }}"><i class="fa fa-home"></i> Back to Dashboard</a>
    </div>
</div>
@endsection