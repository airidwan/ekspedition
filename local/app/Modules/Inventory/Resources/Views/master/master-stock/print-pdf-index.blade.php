<?php use App\Modules\Operational\Model\Transaction\ResiStock; ?>

@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['itemCode']))
                    <tr>
                        <td width="18%">{{ trans('inventory/fields.item-code') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['itemCode'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['description']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['description'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['warehouse']))
                    <tr>
                        <td width="18%">{{ trans('inventory/fields.warehouse') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['warehouse'] }}</td>
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
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="10%">{{ trans('inventory/fields.item-code') }}</th>
            <th width="25%">{{ trans('shared/common.description') }}</th>
            <th width="5%">{{ trans('inventory/fields.uom') }}</th>
            <th width="10%">{{ trans('shared/common.category') }}</th>
            <th width="10%">{{ trans('inventory/fields.warehouse-code') }}</th>
            <th width="20%">{{ trans('inventory/fields.warehouse') }}</th>
            <th width="5%">{{ trans('inventory/fields.stock') }}</th>
            <th width="10%">{{ trans('inventory/fields.average-cost') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($models as $model)
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="10%" >{{ $model->item_code }}</td>
            <td width="25%" >{{ $model->item_description }}</td>
            <td width="5%" >{{ $model->uom_code }}</td>
            <td width="10%" >{{ $model->category_description }}</td>
            <td width="10%" >{{ $model->wh_code }}</td>
            <td width="20%">{{ $model->warehouse_description }}</td>
            <td width="5%" align ="right">{{ number_format($model->stock) }}</td>
            <td width="10%" align ="right">{{ number_format($model->average_cost) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
