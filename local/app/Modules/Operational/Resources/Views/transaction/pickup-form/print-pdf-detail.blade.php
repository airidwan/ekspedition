@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

<?php 
    use App\Service\TimezoneDateConverter;
    use App\Service\Terbilang;
    use App\Modules\Payable\Model\Transaction\InvoiceLine;
    use App\Modules\Operational\Model\Master\MasterCity;
    $partner = $model->partner;
    $date = !empty($model->pickup_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_time) :  TimezoneDateConverter::getClientDateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="70%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="33%">{{ trans('operational/fields.pickup-number') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->pickup_form_number }}</td>
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
                </tr>
            </table>
            <br/>
        </td>
        <td width="30%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="33%">{{ trans('operational/fields.driver') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->driver->driver_name }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('operational/fields.police-number') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->truck->police_number }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('operational/fields.area') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ !empty($model->deliveryArea) ? $model->deliveryArea->delivery_area_name : '' }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="13%">{{ trans('marketing/fields.pickup-request-number') }}</th>
            <th width="13%" rowspan="2">{{ trans('operational/fields.customer-name') }}</th>
            <th width="13%">{{ trans('shared/common.address') }}</th>
            <th width="15%">{{ trans('operational/fields.item-name') }}</th>
            <th width="7%">{{ trans('operational/fields.weight') }}</th>
            <th width="14%">{{ trans('shared/common.description') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.receipt-date') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.signature') }}</th>
        </tr>
        <tr>
            <th>{{ trans('shared/common.date') }}</th>
            <th>{{ trans('shared/common.phone') }}</th>
            <th>{{ trans('operational/fields.total-coly') }}</th>
            <th>{{ trans('operational/fields.volume') }}</th>
            <th>{{ trans('shared/common.description-add') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($model->lines as $line)
         <?php $date = !empty($line->pickupRequest->pickup_request_time) ? new \DateTime($line->pickupRequest->pickup_request_time) : null ?>
        <tr>
            <td width="5%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="13%">{{ $line->pickupRequest->pickup_request_number }}</td>
            <td width="13%" rowspan="2">{{ $line->pickupRequest->customer_name }}</td>
            <td width="13%">{{ $line->pickupRequest->address }}</td>
            <td width="15%">{{ $line->pickupRequest->item_name }}</td>
            <td width="7%" align="right">{{ number_format($line->pickupRequest->weight,2) }}</td>
            <td width="14%">{{ $line->pickupRequest->note }}</td>
            <td width="10%" rowspan="2" align="right"></td>
            <td width="10%" rowspan="2" align="right"></td>
        </tr>
        <tr>
            <td >{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
            <td >{{ $line->pickupRequest->phone_number }}</td>
            <td align="right">{{ number_format($line->pickupRequest->total_coly) }}</td>
            <td align="right">{{ number_format($line->pickupRequest->dimension, 6) }}</td>
            <td >{{ $line->pickupRequest->note_add }}</td>
        </tr>
        @endforeach
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
            <td width="50%"></td>
            <td width="25%" align="center">{{ trans('operational/fields.driver') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.operational-admin') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="50%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">( {{ $model->driver->driver_name }} )</td>
            <td width="25%" align="center">( {{ $user->full_name }} )</td>
        </tr>
    </tbody>
</table>
@endsection
