<?php 
use App\Modules\Inventory\Model\Transaction\MoveOrderHeader; 
use App\Modules\Operational\Model\Master\MasterCity;
?>

@extends('layouts.print')

<?php 
    $driver  = $model->driver;
    $truck   = $model->truck;
    $service = $model->service;
    $lines   = $model->lines;
    $date    = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('inventory/fields.mo-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->mo_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.type') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->type }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.description') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->description }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $date->format('d-m-Y') }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('asset/fields.service-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{  !empty($service) ? $service->service_number : ''  }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.driver') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($driver) ? $driver->driver_name : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.truck') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($truck) ? $truck->police_number : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.status') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->status }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="10%">{{ trans('inventory/fields.item-code') }}</th>
            <th width="25%">{{ trans('inventory/fields.item-name') }}</th>
            <th width="10%">{{ trans('inventory/fields.warehouse') }}</th>
            <th width="10%">{{ trans('inventory/fields.qty-need') }}</th>
            <th width="5%">{{ trans('inventory/fields.uom') }}</th>
            <th width="10%">{{ trans('general-ledger/fields.cost') }}</th>
            <th width="25%">{{ trans('shared/common.description') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($lines as $line)
         <?php  
            $item        = $line->item;
            $wh          = $line->warehouse;
            $combination = $line->coaCombination;
            $uom         = !empty($item) ? $item->uom : null;
        ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="10%" >{{ !empty($item) ? $item->item_code : '' }}</td>
            <td width="25%" >{{ !empty($item) ? $item->description : '' }}</td>
            <td width="10%" >{{ !empty($wh) ? $wh->wh_code : '' }}</td>
            <td width="10%" align="right">{{ number_format($line->qty_need) }}</td>
            <td width="5%" >{{ !empty($uom) ? $uom->uom_code : '' }}</td>
            <td width="10%" align ="right">{{ number_format($line->cost) }}</td>
            <td width="25%" >{{ $line->description }}</td>
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
            <td width="75%"></td>
            <td width="25%" align="center">{{ trans('inventory/fields.pic') }}</td>
        </tr>
        <tr>
            <td width="100%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">( {{ !empty($model->pic) ? $model->pic : '..............................' }} )</td>
        </tr>
    </tbody>
</table>
@endsection
