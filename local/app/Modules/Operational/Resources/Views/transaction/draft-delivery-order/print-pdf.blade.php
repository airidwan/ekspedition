<?php use App\Modules\Operational\Model\Transaction\ResiStock; ?>

@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.draft-do-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $models->draft_delivery_order_number }}</td>
                    </tr>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.truck') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $models->truck->police_number }}</td>
                    </tr>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.driver') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $models->driver->driver_name }}</td>
                    </tr>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.assistant') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ !empty($models->assistant) ? $models->assistant->driver_name : '' }}</td>
                    </tr>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.status') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $models->status }}</td>
                    </tr>
            </table>
            <br/>
        </td>
        <td width="25%" cellpadding="0" cellspacing="0">
            <table>
                <?php $date = new \DateTime(); ?>
                <tr>
                    <td width="25%">{{ trans('shared/common.date') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ $date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.user') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Auth::user()->full_name }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.branch') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Session::get('currentBranch')->branch_name }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="3%" rowspan="3">{{ trans('shared/common.num') }}</th>
            <th width="10%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="10%">{{ trans('operational/fields.route') }}</th>
            <th width="10%" rowspan="3">{{ trans('operational/fields.item-name') }}</th>
            <th width="10%" rowspan="3">{{ trans('operational/fields.receiver') }}</th>
            <th width="22%">{{ trans('operational/fields.address') }}</th>
            <th width="5%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="10%">{{ trans('operational/fields.total-weight') }}</th>
            <th width="10%">{{ trans('operational/fields.delivery-area') }}</th>
            <th width="10%" rowspan="3">{{ trans('shared/common.note') }}</th>
        </tr>
        <tr>
            <th>{{ trans('operational/fields.date') }}</th>
            <th>{{ trans('operational/fields.region') }}</th>
            <th rowspan="2">{{ trans('shared/common.telepon') }}</th>
            <th rowspan="2">{{ trans('operational/fields.coly-wh') }}</th>
            <th>{{ trans('operational/fields.total-volume') }}</th>
            <th rowspan="2">{{ trans('operational/fields.is-ready') }}</th>
        </tr>
        <tr>
            <th>{{ trans('operational/fields.payment') }}</th>
            <th>{{ trans('operational/fields.driver') }}</th>
            <th>{{ trans('operational/fields.total-unit') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1; 
         $totalTonasa     = 0; 
         $totalCubication = 0; 
         $totalUnits = 0; 
         ?>
         @foreach($models->lines as $model)
         <?php
         $resi  = $model->resi;
         $area  = !empty($resi) ? $resi->deliveryArea : null;
         $resiDate = $model->resi !== null ? new \DateTime($model->resi->resi_date) : null;
         $route = $model->resi !== null ? $model->resi->route : null;
         $endCity = $route !== null ? $route->cityEnd : null;
         $region = $endCity !== null ? $endCity->region() : null;
         $stock = ResiStock::where('resi_header_id', $model->resi_header_id)->where('branch_id', $models->branch_id)->first();
         $totalTonasa     += $resi->totalWeight();
         $totalCubication += $resi->totalVolume();
         $totalUnits      += $resi->totalUnit();

         $manifest = \DB::table('op.trans_manifest_header')
                        ->select('mst_driver.driver_name')
                        ->join('op.mst_driver', 'mst_driver.driver_id', '=', 'trans_manifest_header.driver_id')
                        ->join('op.trans_manifest_line', 'trans_manifest_line.manifest_header_id', '=', 'trans_manifest_header.manifest_header_id')
                        ->where('resi_header_id', $model->resi_header_id)
                        ->where('trans_manifest_header.arrive_branch_id', $models->branch_id)
                        ->orderBy('trans_manifest_header.arrive_date', 'desc')
                        ->first();
         ?>
        <tr>
            <td width="3%" rowspan="3" align="center">{{ $no++ }}</td>
            <td width="10%" align="center">{{ $model->resi !== null ? $model->resi->resi_number : '' }}</td>
            <td width="10%">{{ $route !== null ? $route->route_code : '' }}</td>
            <td width="10%" rowspan="3">{{ $model->resi !== null ? $model->resi->itemName() : '' }}</td>
            <td width="10%" rowspan="3">{{ $model->resi !== null ? $resi->receiver_name : '' }}</td>
            <td width="22%">{{ $model->resi !== null ? $resi->receiver_address : '' }}</td>
            <td width="5%" align="right">{{ $model->resi !== null ? $model->resi->totalColy() : '' }}</td>
            <td width="10%" align="right">{{ number_format($resi->totalWeight(), 2) }}</td>
            <td width="10%" align="center">{{  $area !== null ? $area->delivery_area_name : '' }}</td>
            <td width="10%" rowspan="3">{{ $model->resi !== null ? $resi->wdl_note : '' }}</td>
        </tr>
        <tr>
            <td align="center">{{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}</td>
            <td >{{ $region !== null ? $region->region_name : '' }}</td>
            <td rowspan="2">{{ $model->resi !== null ? $resi->receiver_phone : '' }}</td>
            <td rowspan="2" align="right">{{ !empty($stock) ? $stock->coly : '' }}</td>
            <td align="right">{{ number_format($resi->totalVolume(), 6) }}</td>
            <td rowspan="2" align="center">{{ !empty($stock) && $stock->is_ready_delivery ? 'v' : 'x' }}</td>
        </tr>
        <tr>
            <td align="center">{{ $resi->getSingkatanPayment() }}</td>  
            <td >{{ $manifest->driver_name }}</td>  
            <td align="right">
                @foreach($resi->lineUnit as $index => $lineUnit)
                    {{ $lineUnit->coly }} {{ $lineUnit->item_name }} {{ $index == $resi->lineUnit->count() -1 ? '' : '<br/>' }}
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<br><br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="10%">{{ trans('operational/fields.total-weight') }}</td>
            <td width="2%">:</td>
            <td width="38%">{{ number_format($totalTonasa) }} Kg</td>

        </tr>
        <tr>
            <td width="10%">{{ trans('operational/fields.total-volume') }}</td>
            <td width="2%">:</td>
            <td width="38%">{{ number_format($totalCubication, 4) }} M<sup>3</sup></td>
        </tr>
        <tr>
            <td width="10%">{{ trans('operational/fields.total-unit') }}</td>
            <td width="2%">:</td>
            <td width="38%">{{ number_format($totalUnits) }}</td>
        </tr>
    </tbody>
</table>
@endsection