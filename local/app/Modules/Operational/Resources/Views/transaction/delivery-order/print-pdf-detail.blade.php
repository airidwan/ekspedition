@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

<?php 
    use App\Service\Terbilang;
    use App\Modules\Payable\Model\Transaction\InvoiceLine;
    use App\Modules\Operational\Model\Master\MasterCity;
    $partner = $model->partner;
    $startTime      = !empty($model->delivery_start_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->delivery_start_time) : null;
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="28%">{{ trans('operational/fields.do-number') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ $model->delivery_order_number }}</td>
                </tr>
                <tr>
                    <td width="28%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ !empty($startTime) ? $startTime->format('d-m-Y') : '' }}</td>
                </tr>
                <tr>
                    <td width="28%">{{ trans('shared/common.description') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ $model->note }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="28%">{{ trans('operational/fields.driver') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ $model->driver->driver_name }}</td>
                </tr>
                <tr>
                    <td width="28%">{{ trans('operational/fields.assistant') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ !empty($model->assistant) ? $model->assistant->driver_name : '' }}</td>
                </tr>
                <tr>
                    <td width="28%">{{ trans('operational/fields.police-number') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ $model->truck->police_number }}</td>
                </tr>
            </table>
            <br/>
        </td>
        @if(!empty($partner))
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="28%">{{ trans('operational/fields.partner') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ !empty($partner) ? $partner->vendor_name : '' }}</td>
                </tr>
                <tr>
                    <td width="28%">{{ trans('shared/common.address') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ !empty($partner) ? $partner->address : '' }}</td>
                </tr>
                <tr>
                    <td width="28%">{{ trans('shared/common.phone') }}</td>
                    <td width="2%">:</td>
                    <td width="70%">{{ !empty($partner) ? $partner->phone_number : '' }}</td>
                </tr>
            </table>
            <br/>
        </td>
        @endif
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th rowspan="2" width="5%">{{ trans('shared/common.num') }}</th>
            <th rowspan="2" width="10%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="10%">{{ trans('operational/fields.item-name') }}</th>
            <th width="7%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="7%">{{ trans('operational/fields.cost') }}</th>
            <th width="16%">{{ trans('operational/fields.receiver') }}</th>
            <th rowspan="2" width="10%">{{ trans('shared/common.description') }}</th>
            <th rowspan="2" width="5%">{{ trans('operational/fields.coly-receipt') }}</th>
            <th rowspan="2" width="5%">{{ trans('operational/fields.coly-return') }}</th>
            <th rowspan="2" width="10%">{{ trans('operational/fields.receipt-by') }}</th>
            <th rowspan="2" width="8%">{{ trans('operational/fields.receipt-date') }}</th>
            <th rowspan="2" width="7%">{{ trans('operational/fields.signature') }}</th>
        </tr>
        <tr>
            <th width="10%">{{ trans('operational/fields.item-unit') }}</th>
            <th width="7%">{{ trans('operational/fields.coly-sent') }}</th>
            <th width="7%">{{ trans('operational/fields.payment') }}</th>
            <th width="16%">{{ trans('shared/common.address') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($model->lines as $line)
        <tr>
            <td rowspan="2" width="5%" align="center">{{ $no++ }}</td>
            <td rowspan="2" width="10%">{{ $line->resi->resi_number }}</td>
            <td width="10%">{{ $line->resi->item_name }}</td>
            <td width="7%" align="right">{{ number_format($line->resi->totalColy()) }}</td>
            <td width="7%" align="right">{{ $line->invoice !== null ? number_format($line->invoice->totalInvoice()) : 0 }}</td>
            <td width="16%">{{ $line->resi->receiver_name }}</td>
            <td rowspan="2" width="10%">{{ $line->description }}</td>
            <td rowspan="2" width="5%" align="right"></td>
            <td rowspan="2" width="5%" align="right"></td>
            <td rowspan="2" width="10%" align="right"></td>
            <td rowspan="2" width="8%" align="right"></td>
            <td rowspan="2" width="7%" align="right"></td>
        </tr>
        <tr>
            <td width="10%">
                @if($line->lineUnit)
                @foreach($line->lineUnit as $unit)
                    {{ $unit->coly }} Koli - {{ $unit->item_name }}<br/>
                @endforeach
                @endif
            </td>
            <td width="7%" align="right">{{ number_format($line->total_coly) }}</td>
            <td width="7%" align="center">{{ $line->resi->getSingkatanPayment() }}</td>
            <td width="16%">{{ $line->resi->receiver_address }}</td>
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
            <td width="32%" align="center">{{ $city->city_name }}, {{ $date->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="16%" align="center">{{ trans('shared/common.warehouse-admin') }}</td>
            <td width="16%" align="center">{{ trans('shared/common.warehouse-manager') }}</td>
            <td width="16%" align="center">{{ trans('shared/common.operational-admin') }}</td>
            <td width="16%" align="center">{{ trans('shared/common.finance-admin') }}</td>
            <td width="16%" align="center">{{ trans('shared/common.kasir') }}</td>
            <td width="16%" align="center">{{ !empty($partner) ? trans('operational/fields.partner') : '' }}</td>
        </tr>
        <tr>
            <td width="100%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="16%" align="center">( {{ $user->full_name }} )</td>
            <td width="16%" align="center">(..............................)</td>
            <td width="16%" align="center">(..............................)</td>
            <td width="16%" align="center">(..............................)</td>
            <td width="16%" align="center">(..............................)</td>
            <td width="16%" align="center"> {{ !empty($partner) ? '( '.$partner->vendor_name.' )' : '' }} </td>
        </tr>
    </tbody>
</table>
@endsection
