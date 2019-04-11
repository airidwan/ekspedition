<?php
use App\Modules\Operational\Model\Transaction\ManifestLine;
?>

@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.number, .amount{ font-weight: bold; font-size: 12px; }
.note { font-size: 8px; }
</style>
@endsection

<?php 
    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.manifest-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.route') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->route->route_code }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $date->format('d-m-Y') }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.driver') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->driver->driver_name }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.driver-assistant') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($model->driverAssistant) ? $model->driverAssistant->driver_name : '' }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.owner-name') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->truck->owner_name }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.police-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->truck->police_number }}</td>
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
            <th width="15%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="19%" rowspan="2">{{ trans('operational/fields.item-name') }}</th>
            <th width="15%">{{ trans('operational/fields.sender') }}</th>
            <th width="10%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="10%">{{ trans('operational/fields.weight') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.item-unit') }}</th>
            <th width="10%" rowspan="2">{{ trans('shared/common.amount') }}</th>
            <th width="6%" rowspan="2">{{ trans('shared/common.status') }} *</th>
        </tr>
        <tr>
            <th>{{ trans('operational/fields.destination-city') }}</th>
            <th>{{ trans('operational/fields.receiver') }}</th>
            <th>{{ trans('operational/fields.coly-send') }}</th>
            <th>{{ trans('operational/fields.volume') }}</th>
            
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $lines = \DB::table('op.trans_manifest_line')
                        ->select('trans_manifest_line.*')
                        ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_manifest_line.resi_header_id')
                        ->where('trans_manifest_line.manifest_header_id', '=', $model->manifest_header_id)
                        ->orderBy('trans_resi_header.resi_number', 'asc')
                        ->get()
        ?>
        @foreach($lines as $line)
        <?php
        $line = ManifestLine::find($line->manifest_line_id);
        ?>
        <tr>
            <td width="5%" align="center" rowspan="2">{{ $no++ }}</td>
            <td width="15%" align="center">{{ $line->resi->resi_number }}</td>
            <td width="19%" rowspan="2">{{ $line->resi->item_name }}</td>
            <td width="15%">{{ $line->resi->sender_name }}</td>
            <td width="10%" align="right">{{ number_format($line->resi->totalColy()) }}</td>
            <td width="10%" align="right">{{ number_format($line->resi->totalWeightAll()) }}</td>
            <td width="10%" align="right" rowspan="2">
                @foreach($line->resi->lineUnit as $lineUnit)
                    {{ $lineUnit->coly }} {{ $lineUnit->item_name }}
                @endforeach
            </td>
            <td width="10%" rowspan="2" align="right">Rp. {{ number_format($line->resi->total()) }}</td>
            <td width="6%"  rowspan="2" align="center">{{ $line->resi->getSingkatanPayment() }}</td>
        </tr>
        <tr>
            <td align="center">{{ $line->resi->route->cityEnd->city_name }}</td>
            <td>{{ $line->resi->receiver_name }}</td>
            <td align="right">{{ number_format($line->coly_sent) }}</td>
            <td align="right">{{ number_format($line->resi->totalVolumeAll(), 4) }}</td>
            
        </tr>
        @endforeach
    </tbody>
</table>
<p class="note"> *) Status: C = "Cash", BTS = "Bill to Sender", BTR = "Bill to Receiver"</p>

<?php

$city = App\Modules\Operational\Model\Master\MasterCity::find(\Session::get('currentBranch')->city_id);
$date = new \DateTime;
?>
<br><br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="10%">{{ trans('operational/fields.total-weight') }}</td>
            <td width="2%">:</td>
            <td width="38%">{{ number_format($model->getTotalTonasa()) }} Kg</td>
            <td width="15%">{{ trans('operational/fields.total-cash') }}</td>
            <td width="2%">:</td>
            <td width="33%">Rp. {{ number_format($model->getTotalCash()) }}</td>

        </tr>
        <tr>
            <td width="10%">{{ trans('operational/fields.total-volume') }}</td>
            <td width="2%">:</td>
            <td width="38%">{{ number_format($model->getTotalVolume(), 4) }} M<sup>3</sup></td>
            <td width="15%">{{ trans('operational/fields.total-bill-sender') }}</td>
            <td width="2%">:</td>
            <td width="33%">Rp. {{ number_format($model->getTotalBillSender()) }} </td>
        </tr>
        <tr>
            <td width="10%">{{ trans('operational/fields.total-unit') }}</td>
            <td width="2%">:</td>
            <td width="38%">{{ number_format($model->getTotalUnit()) }}</td>
            <td width="15%">{{ trans('operational/fields.total-bill-receiver') }}</td>
            <td width="2%">:</td>
            <td width="33%">Rp. {{ number_format($model->getTotalBillReceiver()) }} </td>
        </tr>
    </tbody>
</table>
@endsection
