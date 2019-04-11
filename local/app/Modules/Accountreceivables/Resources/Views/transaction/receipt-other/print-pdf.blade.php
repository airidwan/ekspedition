<?php
use App\Service\Terbilang;
use App\Modules\Operational\Model\Master\MasterCity;
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
            <td width="100%" align="right">No : {{ $model->receipt_number }}</td>
        </tr>
    </tbody>
</table>

<br/><br/>
<?php
$untukPembayaran = $model->description;
if ($model->isExtraCost()) {
    $untukPembayaran = 'Resi No. '.$model->resi->resi_number;
} elseif ($model->isKasbon()) {
    $untukPembayaran = 'Invoice Kasbon No. '.$model->invoiceApHeader->invoice_number;
}
?>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="23%">{{ trans('accountreceivables/fields.received-from') }}</td>
            <td width="2%">:</td>
            <td width="75%">{{ $model->person_name }}</td>
        </tr>
        <tr>
            <td width="23%">{{ trans('accountreceivables/fields.nominal') }}</td>
            <td width="2%">:</td>
            <td width="75%">{{ ucwords(trim(Terbilang::rupiah($model->amount))) . ' Rupiah' }}</td>
        </tr>
        <tr>
            <td width="23%">{{ trans('accountreceivables/fields.untuk-pembayaran') }}</td>
            <td width="2%">:</td>
            <td width="75%">{{ $untukPembayaran }}</td>
        </tr>
        <tr>
            <td width="23%"></td>
            <td width="2%"></td>
            <td width="75%">{{ $model->isExtraCost() || $model->isKasbon() ? $model->description : '' }}</td>
        </tr>
    </tbody>
</table>

<br/><br/><br/>
<table class="amount" id="amount" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="100%"><u>{{ 'Rp. ' . number_format($model->amount) }}</u></td>
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
            <td width="75%"></td>
            <td width="25%" align="center">{{ trans('accountreceivables/fields.penerima') }}</td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">( {{ \Auth::user()->full_name }} )</td>
        </tr>
    </tbody>
</table>
@endsection
