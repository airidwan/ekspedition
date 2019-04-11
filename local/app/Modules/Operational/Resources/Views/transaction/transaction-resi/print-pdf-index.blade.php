<?php 
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
?>

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
                @if (!empty($filters['customer']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.customer') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['customer'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['sender']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.sender') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['sender'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['receiver']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.receiver') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['receiver'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['route']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.route') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['route'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['route']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.route') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['route'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateFrom']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateTo']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateTo'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['payment']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.payment') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['payment'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['insurance']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.insurance') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['insurance'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['status']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.status') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['status'] }}</td>
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
            <th width="4%" rowspan="3">{{ trans('shared/common.num') }}</th>
            <th width="8%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="15%">{{ trans('operational/fields.customer') }}</th>
            <th width="15%">{{ trans('operational/fields.customer') }}</th>
            <th width="6%">{{ trans('operational/fields.route') }}</th>
            <th width="13%">{{ trans('operational/fields.item-name') }}</th>
            <th width="3%" rowspan="3">{{ trans('operational/fields.coly') }}</th>
            <th width="6%">{{ trans('operational/fields.weight') }}</th>
            <th width="6%">{{ trans('operational/fields.volume') }}</th>
            <th width="6%">{{ trans('operational/fields.qty-unit') }}</th>
            <th width="6%">{{ trans('operational/fields.total-amount') }}</th>
            <th width="6%" rowspan="3">{{ trans('shared/common.description') }}</th>
            <th width="6%" rowspan="3">{{ trans('shared/common.status') }}</th>
        </tr>
        <tr>
            <th rowspan="2">{{ trans('shared/common.date') }}</th>
            <th>{{ trans('operational/fields.sender') }}</th>
            <th>{{ trans('operational/fields.receiver') }}</th>
            <th>{{ trans('operational/fields.payment') }}</th>
            <th rowspan="2">{{ trans('operational/fields.item-unit') }}</th>
            <th rowspan="2">{{ trans('operational/fields.total-price') }}</th>
            <th rowspan="2">{{ trans('operational/fields.total-price') }}</th>
            <th rowspan="2">{{ trans('operational/fields.total-price') }}</th>
            <th>{{ trans('operational/fields.discount') }}</th>
        </tr>
        <tr>
            <th>{{ trans('shared/common.address') }}</th>
            <th>{{ trans('shared/common.address') }}</th>
            <th>{{ trans('operational/fields.insurance') }}</th>
            <th>{{ trans('shared/common.total') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($models as $resi)
        <?php
             $model    = TransactionResiHeader::find($resi->resi_header_id);
             $resiDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
         ?>
        <tr>
            <td width="4%" align="center" rowspan="3">{{ $no++ }}</td>
            <td width="8%">{{ $model->resi_number }}</td>
            <td width="15%">{{ !empty($model->customer) ? $model->customer->customer_name : '' }}</td>
            <td width="15%">{{ !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : '' }}</td>
            <td width="6%">{{ $model->route !== null ? $model->route->route_code : '' }}</td>
            <td width="13%">{{ $model->itemName() }}</td>
            <td width="3%" rowspan="3" align="right">{{ number_format($model->totalColy()) }}</td>
            <td width="6%" align="right">{{ number_format($model->totalWeight(), 2) }}</td>
            <td width="6%" align="right">{{ number_format($model->totalVolume(), 2) }}</td>
            <td width="6%" align="right">{{ number_format($model->totalUnit(), 2) }}</td>
            <td width="6%" align="right">{{ number_format($model->totalAmount()) }}</td>
            <td width="6%" rowspan="3">{{ $model->desciption }}</td>
            <td width="6%" rowspan="3">{{ $model->status }}</td>
        </tr>
        <tr>
            <td rowspan="2">{{ !empty($resiDate) ? $resiDate->format('d-M-Y') : '' }}</td>
            <td>{{ $model->sender_name }}</td>
            <td>{{ $model->receiver_name }}</td>
            <td>{{ $model->getSingkatanPayment() }}</td>
            <td rowspan="2">{{ $model->itemUnit() }}</td>
            <td rowspan="2" align="right">{{ number_format($model->totalWeightPrice()) }}</td>
            <td rowspan="2" align="right">{{ number_format($model->totalVolumePrice()) }}</td>
            <td rowspan="2" align="right">{{ number_format($model->totalUnitPrice()) }}</td>
            <td align="right">{{ number_format($model->discount) }}</td>
        </tr>
        <tr>
            <td>{{ $model->sender_address }}</td>
            <td>{{ $model->receiver_address }}</td>
            <td><i class="fa {{ $model->insurance ? 'fa-check' : 'fa-remove' }}"></i></td>
            <td align="right">{{ number_format($model->total()) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
