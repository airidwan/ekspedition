@extends('layouts.print')

<?php 
    use App\Modules\Operational\Model\Master\MasterCity;
    use App\User;
    $driver = $model->driver;
    $truck  = $model->truck;
    $lines  = $model->lines;
    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('inventory/fields.bt-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->bt_number }}</td>
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
            <th width="20%">{{ trans('inventory/fields.item-name') }}</th>
            <th width="5%">{{ trans('inventory/fields.qty-need') }}</th>
            <th width="10%">{{ trans('inventory/fields.from-branch') }}</th>
            <th width="10%">{{ trans('inventory/fields.from-wh') }}</th>
            <th width="10%">{{ trans('inventory/fields.to-branch') }}</th>
            <th width="10%">{{ trans('inventory/fields.to-wh') }}</th>
            <th width="20%">{{ trans('shared/common.description') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($lines as $line)
         <?php  
            $item       = $line->item;
            $uom        = !empty($item) ? $item->uom : null;
            $fromWh     = $line->fromWarehouse; 
            $fromBranch = !empty($fromWh) ? $fromWh->branch : null;
            $toWh       = $line->toWarehouse; 
            $toBranch   = !empty($toWh) ? $toWh->branch : null;
        ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="10%" >{{ !empty($item) ? $item->item_code : '' }}</td>
            <td width="20%" >{{ !empty($item) ? $item->description : '' }}</td>
            <td width="5%" >{{ number_format($line->qty_need) }}</td>
            <td width="10%" >{{ !empty($fromBranch) ? $fromBranch->branch_code : '' }}</td>
            <td width="10%" >{{ !empty($fromWh) ? $fromWh->wh_code : '' }}</td>
            <td width="10%" >{{ !empty($toBranch) ? $toBranch->branch_code : '' }}</td>
            <td width="10%" >{{ !empty($toWh) ? $toWh->wh_code : '' }}</td>
            <td width="20%" >{{ $line->description }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


<?php
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$createdDate  = new \DateTime($model->created_date);
$userCreated  = User::find($model->created_by);
?>
<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="60%"></td>
            <td width="40%" align="center">{{ $city->city_name }}, {{ $createdDate->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="40%"></td>
            <td width="20%" align="center">{{ trans('shared/common.created-by') }}</td>
            <td width="20%" align="center">{{ trans('inventory/fields.pic') }}</td>
            <td width="20%" align="center">{{ trans('operational/fields.driver') }}</td>
        </tr>
        <tr>
            <td width="100%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="40%"></td>
            <td width="20%" align="center">( {{ strtoupper($userCreated->full_name) }} )</td>
            <td width="20%" align="center">( {{ strtoupper($model->pic) }} )</td>
            <td width="20%" align="center">( {{ strtoupper(!empty($model->driver) ? $model->driver->driver_name : '................................' ) }} )</td>
        </tr>
    </tbody>
</table>
@endsection
