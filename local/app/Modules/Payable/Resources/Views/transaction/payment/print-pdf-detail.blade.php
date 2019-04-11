<?php
use App\Service\Terbilang;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.table{ font-size: 10px }
.number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

@section('content')
<br/><br/>
<table class="number" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="100%" align="right">No : {{ $model->payment_number }}</td>
        </tr>
    </tbody>
</table>

<?php 
    $invoice = $model->invoice;
    $type    = $invoice->type;
    $vendorName = '';
    if(in_array($type->type_id, InvoiceHeader::VENDOR_TYPE)){
        $vendor     = $invoice->vendor;
        $vendorName = $vendor->vendor_name;
    }else{
        $vendor = $invoice->driver;
        $vendorName = $vendor->driver_name;
    }
?>

<br/><br/>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="23%">{{ trans('payable/fields.payment-to') }}</td>
            <td width="2%">:</td>
            <td width="75%">{{ $vendorName }}</td>
        </tr>
        <tr>
            <td width="23%">{{ trans('payable/fields.nominal') }}</td>
            <td width="2%">:</td>
            <td width="75%">{{ trim(ucwords(Terbilang::rupiah($model->getTotalPayment()))) . ' Rupiah' }}</td>
        </tr>
        <tr>
            <td width="23%">{{ trans('payable/fields.for-payment') }}</td>
            <td width="2%">:</td>
            <td width="75%">{{ 'Invoice No. ' . $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td width="23%"></td>
            <td width="2%"></td>
            <td width="75%">{{ $model->note }}</td>
        </tr>
    </tbody>
</table>

<br/><br/><br/>
<table class="amount" id="amount" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="100%"><u>{{ 'Rp. ' . number_format($model->getTotalPayment()) }}</u></td>
        </tr>
    </tbody>
</table>

<?php
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$date = new \DateTime;
?>
<br/><br/><br/>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $date->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">{{ trans('payable/fields.chasier') }}</td>
            <td width="25%" align="center">{{ trans('payable/fields.receiver') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" height="40px" align="center"></td>
            <td width="25%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">( {{ \Auth::user()->full_name }} )</td>
            <td width="25%" align="center">( {{ $vendorName }} )</td>
        </tr>
    </tbody>
</table>
@endsection
