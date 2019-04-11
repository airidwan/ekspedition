@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

<?php 
    use App\Service\Terbilang;
    use App\Modules\Operational\Model\Master\MasterCity;
    use App\Modules\Operational\Model\Transaction\ResiStock;

    $date = !empty($model->customer_taking_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->customer_taking_time) : null;
    $resiStock = ResiStock::where('resi_header_id', '=', $model->resi_header_id)->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();
?>

@section('content')
<table id="filters" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="60%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="33%">{{ trans('operational/fields.customer-taking-number') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->customer_taking_number }}</td>
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
                <tr>
                    <td width="33%">{{ trans('operational/fields.sender') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->resi->sender_name }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="40%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="33%">{{ trans('operational/fields.receiver') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->resi->receiver_name }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('shared/common.address') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->resi->receiver_address }}</td>
                </tr>
                <tr>
                    <td width="33%">{{ trans('shared/common.phone') }}</td>
                    <td width="2%">:</td>
                    <td width="65%">{{ $model->resi->receiver_phone }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="20%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="25%">{{ trans('operational/fields.item-name') }}</th>
            <th width="5%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="5%">{{ trans('operational/fields.coly-wh') }}</th>
            <th width="10%">{{ trans('operational/fields.weight') }}</th>
            <th width="10%">{{ trans('operational/fields.volume') }}</th>
            <th width="25%">{{ trans('shared/common.description') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="20%">{{ $model->resi->resi_number }}</td>
            <td width="25%">{{ $model->resi->item_name }}</td>
            <td width="5%" align="right">{{ number_format($model->resi->totalColy()) }}</td>
            <td width="5%" align="right">{{ number_format(!empty($resiStock) ? $resiStock->coly : 0 ) }}</td>
            <td width="10%" align="right">{{ number_format($model->resi->totalWeight(), 2) }}</td>
            <td width="10%" align="right">{{ number_format($model->resi->totalVolume(), 6) }}</td>
            <td width="25%">{{ $model->resi->description }}</td>
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
            <td width="50%"></td>
            <td width="25%" align="center">{{ trans('shared/common.print-by') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.admin') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="50%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">( {{ \Auth::user()->full_name }} )</td>
            <td width="25%" align="center">( {{ $user->full_name }} )</td>
        </tr>
    </tbody>
</table>
@endsection
