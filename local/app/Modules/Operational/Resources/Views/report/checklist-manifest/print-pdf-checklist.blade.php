<?php
use App\Modules\Operational\Model\Transaction\ManifestLine;
?>

@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
    .number, .amount{ font-weight: bold; font-size: 12px; }
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
            <th width="14%" rowspan="2">{{ trans('operational/fields.resi-number') }}</th>
            <th width="19%" rowspan="2">{{ trans('operational/fields.item-name') }}</th>
            <th width="15%">{{ trans('operational/fields.sender') }}</th>
            <th width="8%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="9%">{{ trans('operational/fields.weight') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.destination-city') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.check') }}</th>
            <th width="10%" rowspan="2">{{ trans('shared/common.description') }}</th>
        </tr>
        <tr>
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
        <td width="5%" rowspan="2" align="center">{{ $no++ }}</td>
        <td width="14%" rowspan="2" align="center">{{ $line->resi->resi_number }}</td>
        <td width="19%" rowspan="2">{{ $line->resi->item_name }}</td>
        <td width="15%">{{ $line->resi->sender_name }}</td>
        <td width="8%" align="right">{{ number_format($line->resi->totalColy()) }}</td>
        <td width="9%" align="right">{{ number_format($line->resi->totalWeightAll()) }}</td>
        <td width="10%" rowspan="2">{{ $line->resi->route->cityEnd->city_name }}</td>
        <td width="10%" rowspan="2"></td>
        <td width="10%" rowspan="2"></td>
    </tr>
    <tr>
        <td>{{ $line->resi->receiver_name }}</td>
        <td align="right">{{ number_format($line->coly_sent) }}</td>
        <td align="right">{{ number_format($line->resi->totalVolumeAll(), 4) }}</td>

    </tr>
    @endforeach
</tbody>
</table>
<?php

$city = App\Modules\Operational\Model\Master\MasterCity::find(\Session::get('currentBranch')->city_id);
$date = new \DateTime;
?>
<br><br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="50%" align="center">
                <table class="table" cellspacing="0" cellpadding="2">
                    <tbody>
                        <tr>
                            <td width="99%" align="center"></td>
                        </tr>
                        <tr>
                            <td width="99%" align="center">All the goods are loaded on the checklist have been checked</td>
                        </tr>
                        <tr>
                            <td width="33%" align="center">{{ trans('operational/fields.checker') }}</td>
                            <td width="33%" align="center">{{ trans('operational/fields.assistant') }}</td>
                            <td width="33%" align="center">{{ trans('operational/fields.warehouse-admin') }}</td>
                        </tr>
                        <tr>
                            <td width="99%" height="40px" align="center"></td>
                        </tr>
                        <tr>
                            <td width="33%" align="center">(.................................)</td>
                            <td width="33%" align="center">( {{ !empty($model->driverAssistant) ? $model->driverAssistant->driver_name : '.................................' }} )</td>
                            <td width="33%" align="center">(.................................)</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td width="50%" align="center">
                <table class="table" cellspacing="0" cellpadding="2">
                    <tbody>
                        <tr>
                            <td width="99%" align="center" style="border-top:1px solid red; border-left:1px solid red; border-right:1px solid red;">{{ $city->city_name }}, {{ $date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td width="99%" align="center" style="border-left:1px solid red; border-right:1px solid red; color:red;">Knowing and Approved</td>
                        </tr>
                        <tr>
                            <td width="33%" align="center" style="border-left:1px solid red;">{{ trans('operational/fields.warehouse-manager') }}</td>
                            <td width="33%" align="center" >{{ trans('operational/fields.head-of-loading') }}</td>
                            <td width="33%" align="center" style="border-right:1px solid red;">{{ trans('operational/fields.driver') }}</td>
                        </tr>
                        <tr>
                            <td width="99%" height="40px" align="center" style="border-right:1px solid red; border-left:1px solid red;"></td>
                        </tr>
                        <tr>
                            <td width="33%" align="center" style="border-bottom:1px solid red; border-left:1px solid red;">(.................................)</td>
                            <td width="33%" align="center" style="border-bottom:1px solid red; solid red;">(.................................)</td>
                            <td width="33%" align="center" style="border-bottom:1px solid red; border-right:1px solid red;">( {{ $model->driver->driver_name }} )</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
@endsection
