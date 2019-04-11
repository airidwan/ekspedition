<?php use App\Modules\Operational\Model\Transaction\ResiStock; ?>

@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['resiNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.resi-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['resiNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['deliveryArea']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.delivery-area') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['deliveryArea'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['route']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.route') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['route'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['status']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.is-ready') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['status'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['region']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.region') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['region'] }}</td>
                    </tr>
                @endif
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
            <th width="12%">{{ trans('operational/fields.address') }}</th>
            <th width="10%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="10%">{{ trans('operational/fields.total-weight') }}</th>
            <th width="10%" rowspan="3">{{ trans('operational/fields.delivery-area') }}</th>
            <th width="5%" rowspan="3">{{ trans('operational/fields.is-ready') }}</th>
            <th width="10%" rowspan="3">{{ trans('shared/common.note') }}</th>
        </tr>
        <tr>
            <th>{{ trans('operational/fields.date') }}</th>
            <th rowspan="2">{{ trans('operational/fields.region') }}</th>
            <th rowspan="2">{{ trans('shared/common.telepon') }}</th>
            <th rowspan="2">{{ trans('operational/fields.coly-wh') }}</th>
            <th>{{ trans('operational/fields.total-volume') }}</th>
        </tr>
        <tr>
            <th>{{ trans('operational/fields.payment') }}</th>
            <th>{{ trans('operational/fields.total-unit') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($models as $model)
         <?php
         $model = ResiStock::find($model->stock_resi_id);
         $resi  = $model->resi;
         $area  = !empty($resi) ? $resi->deliveryArea : null;
         $resiDate = $model->resi !== null ? new \DateTime($model->resi->resi_date) : null;
         $warehouse = $model->warehouse;
         $route = $model->resi !== null ? $model->resi->route : null;
         $endCity = $route !== null ? $route->cityEnd : null;
         $region = $endCity !== null ? $endCity->region() : null;
         ?>
        <tr>
            <td width="3%" rowspan="3" align="center">{{ $no++ }}</td>
            <td width="10%" align="center">{{ $model->resi !== null ? $model->resi->resi_number : '' }}</td>
            <td width="10%">{{ $route !== null ? $route->route_code : '' }}</td>
            <td width="10%" rowspan="3">{{ $model->resi !== null ? $model->resi->itemName() : '' }}</td>
            <td width="10%" rowspan="3">{{ $model->resi !== null ? $resi->receiver_name : '' }}</td>
            <td width="12%">{{ $model->resi !== null ? $resi->receiver_address : '' }}</td>
            <td width="10%" align="right">{{ $model->resi !== null ? $model->resi->totalColy() : '' }}</td>
            <td width="10%" align="right">{{ number_format($resi->totalWeight(), 2) }}</td>
            <td width="10%" rowspan="3" align="center">{{  $area !== null ? $area->delivery_area_name : '' }}</td>
            <td width="5%" rowspan="3" align="center">{{ ($model->is_ready_delivery) ? 'v' : 'x' }}</td>
            <td width="10%" rowspan="3">{{ $model->resi !== null ? $resi->wdl_note : '' }}</td>
        </tr>
        <tr>
            <td align="center">{{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}</td>
            <td rowspan="2">{{ $region !== null ? $region->region_name : '' }}</td>
            <td rowspan="2">{{ $model->resi !== null ? $resi->receiver_phone : '' }}</td>
            <td rowspan="2" align="right">{{ $model->coly }}</td>
            <td align="right">{{ number_format($resi->totalVolume(), 6) }}</td>
        </tr>
        <tr>
            <td align="center">{{ $resi->getSingkatanPayment() }}</td>
            <td align="right">
                @foreach($resi->lineUnit as $index => $lineUnit)
                    {{ $lineUnit->coly }} {{ $lineUnit->item_name }} {{ $index == $resi->lineUnit->count() -1 ? '' : '<br/>' }}
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection