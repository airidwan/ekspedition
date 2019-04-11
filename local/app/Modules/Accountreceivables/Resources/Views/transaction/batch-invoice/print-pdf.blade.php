@extends('layouts.print')

<?php
use App\Modules\Operational\Model\Master\MasterCity;
use App\Service\Terbilang;

$invoiceDate = new \DateTime($model->created_date);
$date = new \DateTime();
$city = MasterCity::find(\Session::get('currentBranch')->city_id);

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
                    <td width="38%">{{ trans('accountreceivables/fields.batch-invoice-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->batch_invoice_number }}</td>
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
            <th width="15%" style="border: 1px solid black">{{ trans('accountreceivables/fields.invoice-number') }}</th>
            <th width="15%" style="border: 1px solid black">{{ trans('operational/fields.resi-number') }}</th>
            <th width="25%" style="border: 1px solid black">{{ trans('operational/fields.sender') }}</th>
            <th width="5%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.coly') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.measurement') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('accountreceivables/fields.amount') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('accountreceivables/fields.discount') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('accountreceivables/fields.total') }}</th>
        </tr>
        <tr>
            <th style="border: 1px solid black">{{ trans('shared/common.type') }}</th>
            <th style="border: 1px solid black">{{ trans('shared/common.date') }}</th>
            <th style="border: 1px solid black">{{ trans('operational/fields.receiver') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($model->lines as $line)
        <?php $date = !empty($line->invoice->resi->created_date) ? new \DateTime($line->invoice->resi->created_date) : null  ?>
        <tr>
            <td width="15%" style="border: 1px solid black">{{ $line->invoice->invoice_number }}</td>
            <td width="15%" style="border: 1px solid black">{{ $line->invoice->resi->resi_number }}</td>
            <td width="25%" style="border: 1px solid black">{{ $line->invoice->resi->getCustomerName() }}</td>
            <td width="5%" align="right" style="border: 1px solid black" rowspan="2">{{ $line->invoice->resi->totalColy() }}</td>
            <td width="10%" align="right" style="border: 1px solid black" rowspan="2">
                {{ !empty($line->invoice->resi->totalWeight()) ? number_format($line->invoice->resi->totalWeight()).' kg' : '' }}
                {{ !empty($line->invoice->resi->totalWeight()) && !empty($line->invoice->resi->totalVolume()) ? ', ' : '' }}
                {{ !empty($line->invoice->resi->totalVolume()) ? number_format($line->invoice->resi->totalVolume(), 6).' m3' : '' }}
            </td>
            <td width="10%" align="right" style="border: 1px solid black" rowspan="2">{{ number_format($line->invoice->amount) }}</td>
            <td width="10%" align="right" style="border: 1px solid black" rowspan="2">{{ number_format($line->invoice->totalDiscount()) }}</td>
            <td width="10%" align="right" style="border: 1px solid black" rowspan="2">{{ number_format($line->invoice->totalInvoice()) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid black">{{ $line->invoice->type }}</td>
            <td style="border: 1px solid black">{{ !empty($date) ? $date->format('d-m-Y') : '' }}</td>
            <td style="border: 1px solid black">{{ $line->invoice->resi->getCustomerReceiverName() }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black">{{ trans('accountreceivables/fields.subtotal') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->total()) }}</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black">{{ trans('accountreceivables/fields.total-payment') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->totalReceipt()) }}</td>
        </tr>
        <tr>
            <td width="70%"></td>
            <td width="20%" align="right" style="border: 1px solid black"><strong>{{ trans('shared/common.total') }} </strong></td>
            <td width="10%" align="right" style="border: 1px solid black"><strong>{{ number_format($model->remaining()) }}</strong></td>
        </tr>
    </tbody>
</table>

<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="100%" style="font-weight: bold"><u>{{ !empty($model->remaining()) ? ucwords(trim(Terbilang::rupiah($model->remaining()))) : 'Nol' }} Rupiah</u></td>
        </tr>
    </tbody>
</table>

<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $invoiceDate->format('d-m-Y') }}</td>
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
