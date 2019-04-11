@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

<?php 
    use App\Service\Terbilang;
    use App\Service\TimezoneDateConverter;
    use App\Modules\Operational\Model\Master\MasterCity;
    $date = !empty($model->pickup_request_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_request_time) :  TimezoneDateConverter::getClientDateTime();
?>

@section('content')
<table id="filters" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="60%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="33%">{{ trans('marketing/fields.pickup-request-number') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->pickup_request_number }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $date->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('shared/common.description') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->note }}</td>
                </tr><tr>
                    <td width="33%">{{ trans('marketing/fields.pickup-cost') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">Rp. {{ number_format($model->pickup_cost) }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="40%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="33%">{{ trans('operational/fields.customer') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->customer_name }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('shared/common.address') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->address }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('shared/common.phone') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->phone_number }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="20%">{{ trans('operational/fields.item-name') }}</th>
            <th width="10%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="10%">{{ trans('operational/fields.weight') }}</th>
            <th width="10%">{{ trans('operational/fields.volume') }}</th>
            <th width="25%">{{ trans('shared/common.description') }}</th>
            <th width="25%">{{ trans('shared/common.description-add') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="20%">{{ $model->item_name }}</td>
            <td width="10%">{{ $model->total_coly }}</td>
            <td width="10%" align="right">{{ number_format($model->weight, 2) }}</td>
            <td width="10%" align="right">{{ number_format($model->dimension, 6) }}</td>
            <td width="25%">{{ $model->note }}</td>
            <td width="25%">{{ $model->note_add }}</td>
        </tr>
    </tbody>
</table>

<?php
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$user = App\User::find($model->created_by);
$date = new \DateTime;
?>
<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $date->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="25%"></td>
            <td width="25%" align="center">{{ trans('operational/fields.customer-name') }}</td>
            <td width="25%" align="center">{{ trans('operational/fields.driver') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.admin') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="50%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="25%"></td>
            <td width="25%" align="center">( {{ $model->customer_name }} )</td>
            <td width="25%" align="center">( {{ !empty($model->getDriver()) ? $model->getDriver() : '...........................' }} )</td>
            <td width="25%" align="center">( {{ $user->full_name }} )</td>
        </tr>
    </tbody>
</table>
@endsection
