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
                @if (!empty($filters['stringRoute']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.route') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ substr($filters['stringRoute'], 0, -2) }}</td>
                    </tr>
                @endif
                @if (!empty($filters['stringRegion']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.region') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ substr($filters['stringRegion'], 0, -2) }}</td>
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
            <th width="3%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="10%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="10%">{{ trans('operational/fields.route') }}</th>
            <th width="15%" rowspan="2">{{ trans('operational/fields.item-name') }}</th>
            <th width="10%">{{ trans('operational/fields.customer') }}</th>
            <th width="10%">{{ trans('operational/fields.customer') }}</th>
            <th width="7%">{{ trans('operational/fields.total-coly') }}</th>
            <th width="7%">{{ trans('operational/fields.total-weight') }}</th>
            <th width="8%" rowspan="2">{{ trans('operational/fields.total-unit') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.check') }}</th>
            <th width="10%" rowspan="2">{{ trans('shared/common.status') }}</th>
        </tr>
        <tr>
            <th>{{ trans('operational/fields.date') }}</th>
            <th>{{ trans('operational/fields.region') }}</th>
            <th>{{ trans('operational/fields.sender') }}</th>
            <th>{{ trans('operational/fields.receiver') }}</th>
            <th>{{ trans('operational/fields.coly-wh') }}</th>
            <th>{{ trans('operational/fields.total-volume') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($models as $model)
         <?php
         $modelStock = ResiStock::find($model->stock_resi_id);
         $resi       = $modelStock->resi;
         $resiDate   = new \DateTime($model->created_date);
         ?>
        <tr>
            <td width="3%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="10%" align="center">{{ $model->resi_number }}</td>
            <td width="10%">{{ $model->route_code }}</td>
            <td width="15%" rowspan="2">{{ $modelStock->resi !== null ? $modelStock->resi->itemName() : '' }}</td>
            <td width="10%" >{{ !empty($modelStock->resi->customer) ? $modelStock->resi->customer->customer_name : '' }}</td>
            <td width="10%" >{{ !empty($modelStock->resi->customerReceiver) ? $modelStock->resi->customerReceiver->customer_name : '' }}</td>
            <td width="7%" align="right">{{ $modelStock->resi !== null ? $modelStock->resi->totalColy() : '' }}</td>
            <td width="7%" align="right">{{ number_format($resi->totalWeight(), 2) }}</td>
            <td width="8%" rowspan="2" align="right">
                @foreach($resi->lineUnit as $index => $lineUnit)
                    {{ $lineUnit->coly }} {{ $lineUnit->item_name }} {{ $index == $resi->lineUnit->count() -1 ? '' : '<br/>' }}
                @endforeach
            </td>
            <td width="10%" rowspan="2" align="right"></td>
            <td width="10%" rowspan="2" align="right"></td>
        </tr>
        <tr>
            <td align="center">{{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}</td>
            <td>{{ $model->region_name }}</td>
            <td>{{ $model->sender_name }}</td>
            <td>{{ $model->receiver_name }}</td>
            <td align="right">{{ $model->coly_wh }}</td>
            <td align="right">{{ number_format($resi->totalVolume(), 6) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
