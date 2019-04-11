@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
    .number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

<?php 
$date = !empty($model->created_date) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->created_date) : null;
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.return-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest_return_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.manifest-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest->manifest_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.route') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest->route->route_code }}</td>
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
                    <td width="60%">{{ $model->manifest->driver->driver_name }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.driver-assistant') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($model->manifest->driverAssistant) ? $model->manifest->driverAssistant->driver_name : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.owner-name') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest->truck->owner_name }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.police-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest->truck->police_number }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="33%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('shared/common.note') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->note }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.status') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->manifest->status }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="0">
    <thead>
        <tr>
            <th width="5%" style="border: 1px solid black" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="13%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.resi-number') }}</th>
            <th width="20%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.item-name') }}</th>
            <th width="15%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.receiver') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('operational/fields.total-coly') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.coly-return') }}</th>
            <th width="7%" style="border: 1px solid black">{{ trans('operational/fields.weight') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('operational/fields.destination-city') }}</th>
            <th width="10%" style="border: 1px solid black" rowspan="2">{{ trans('shared/common.description') }}</th>
        </tr>
        <tr>
            <th style="border: 1px solid black">{{ trans('operational/fields.coly-send') }}</th>
            <th style="border: 1px solid black">{{ trans('operational/fields.volume') }}</th>
            
        </tr>
    </thead>
    <tbody>
     <?php $no = 1; ?>
     @foreach($model->lines as $line)
     <tr>
        <td width="5%" align="center" style="border: 1px solid black" rowspan="2">{{ $no++ }}</td>
        <td width="13%" style="border: 1px solid black" rowspan="2">{{ $line->manifestLine->resi->resi_number }}</td>
        <td width="20%" style="border: 1px solid black" rowspan="2">{{ $line->manifestLine->resi->item_name }}</td>
        <td width="15%" style="border: 1px solid black" rowspan="2">{{ $line->manifestLine->resi->receiver_name }}</td>
        <td width="10%" align="right" style="border: 1px solid black">{{ number_format($line->manifestLine->resi->totalColy()) }}</td>
        <td width="10%" align="right" style="border: 1px solid black" rowspan="2">{{ number_format($line->coly_return) }}</td>
        <td width="7%" align="right" style="border: 1px solid black">{{ number_format($line->manifestLine->resi->totalWeightAll()) }}</td>
        <td width="10%" style="border: 1px solid black" rowspan="2">{{ $line->manifestLine->resi->route->cityEnd->city_name }}</td>
        <td width="10%" style="border: 1px solid black" rowspan="2"></td>
    </tr>
    <tr>
        <td align="right" style="border: 1px solid black">{{ number_format($line->manifestLine->coly_sent) }}</td>
        <td align="right" style="border: 1px solid black">{{ number_format($line->manifestLine->resi->totalVolumeAll(), 4) }}</td>

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
                            <td width="100%" align="center"></td>
                        </tr>
                        <tr>
                            <td width="100%" align="center">All the goods are loaded on the checklist have been checked</td>
                        </tr>
                        <tr>
                            <td width="50%" align="center">{{ trans('operational/fields.checker') }}</td>
                            <td width="50%" align="center">{{ trans('operational/fields.assistant') }}</td>
                        </tr>
                        <tr>
                            <td width="100%" height="40px" align="center"></td>
                        </tr>
                        <tr>
                            <td width="50%" align="center">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
                            <td width="50%" align="center">( {{ !empty($model->driverAssistant) ? $model->manifest->driverAssistant->driver_name : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' }} )</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td width="50%" align="center">
                <table class="table" cellspacing="0" cellpadding="2">
                    <tbody>
                        <tr>
                            <td width="100%" align="center" style="border-top:1px solid red; border-left:1px solid red; border-right:1px solid red;">{{ $city->city_name }}, {{ $date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td width="100%" align="center" style="border-left:1px solid red; border-right:1px solid red; color:red;">Knowing and Approved</td>
                        </tr>
                        <tr>
                            <td width="50%" align="center" style="border-left:1px solid red;">{{ trans('operational/fields.warehouse-manager') }}</td>
                            <td width="50%" align="center" style="border-right:1px solid red;">{{ trans('operational/fields.driver') }}</td>
                        </tr>
                        <tr>
                            <td width="100%" height="40px" align="center" style="border-right:1px solid red; border-left:1px solid red;"></td>
                        </tr>
                        <tr>
                            <td width="50%" align="center" style="border-bottom:1px solid red; border-left:1px solid red;">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
                            <td width="50%" align="center" style="border-bottom:1px solid red; border-right:1px solid red;">( {{ $model->manifest->driver->driver_name }} )</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
@endsection
