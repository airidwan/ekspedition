@extends('layouts.print')

<?php
use App\Modules\Operational\Model\Master\MasterCity;
use App\Service\Terbilang;

$invoiceDate = new \DateTime($model->created_date);
$date = new \DateTime();
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$extraCost = 0;
foreach ($model->resi->invoices as $invoice) {
    if ($invoice->invoice_id != $model->invoice_id) {
        $extraCost += $invoice->totalInvoice();
    }
}

$totalReceipt = 0;
foreach ($model->resi->invoices as $invoice) {
    $totalReceipt += $invoice->totalReceipt();
}

$total = $model->totalInvoice() + $extraCost - $totalReceipt;
?>

@section('content')
<br/><br/>
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="100%" cellpadding="0" cellspacing="0">
            {{ trans('shared/common.to') }}
        </td>
    </tr>
    <tr>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('shared/common.name') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->bill_to }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.address') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->bill_to_address }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.phone') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->bill_to_phone }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('accountreceivables/fields.invoice-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->invoice_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $invoiceDate->format('d-m-Y') }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="0">
    <thead>
        <tr>
            <th width="15%" style="border: 1px solid black">{{ trans('operational/fields.resi-number') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('operational/fields.route') }}</th>
            <th width="20%" style="border: 1px solid black">{{ trans('operational/fields.item-name') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('operational/fields.coly') }}</th>
            <th width="15%" style="border: 1px solid black">{{ trans('operational/fields.measurement') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('accountreceivables/fields.amount') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('accountreceivables/fields.discount') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('accountreceivables/fields.total') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="15%" style="border: 1px solid black">{{ $model->resi->resi_number }}</td>
            <td width="10%" style="border: 1px solid black">{{ $model->resi->route->route_code }}</td>
            <td width="20%" style="border: 1px solid black">{{ $model->resi->getItemAndUnitNames() }}</td>
            <td width="10%" align="right" style="border: 1px solid black">{{ $model->resi->totalColy() }}</td>
            <td width="15%" align="right" style="border: 1px solid black">
                {{ !empty($model->resi->totalWeight()) ? number_format($model->resi->totalWeight()).' kg' : '' }}
                {{ !empty($model->resi->totalWeight()) && !empty($model->resi->totalVolume()) ? ', ' : '' }}
                {{ !empty($model->resi->totalVolume()) ? number_format($model->resi->totalVolume(), 6).' m3' : '' }}
            </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->amount) }}</td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->totalDiscount()) }}</td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->totalInvoice()) }}</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black">{{ trans('accountreceivables/fields.subtotal') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->totalInvoice()) }}</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black">{{ trans('accountreceivables/fields.extra-cost') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($extraCost) }}</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black">{{ trans('accountreceivables/fields.total-payment') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($totalReceipt) }}</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black"><strong>{{ trans('shared/common.total') }} </strong></td>
            <td width="10%" align="right" style="border: 1px solid black"><strong>{{ number_format($total) }}</strong></td>
        </tr>
    </tbody>
</table>

<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="100%" style="font-weight: bold"><u>{{ !empty($total) ? ucwords(trim(Terbilang::rupiah($total))) : 'Nol' }} Rupiah</u></td>
        </tr>
    </tbody>
</table>

<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $date->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ trans('payable/fields.finance') }}</td>
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
