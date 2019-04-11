@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['assetNumber']))
                    <tr>
                        <td width="18%">{{ trans('asset/fields.asset-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['assetNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['assetName']))
                    <tr>
                        <td width="18%">{{ trans('assset/fields.asset-name') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['assetName'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['policeNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.police-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['policeNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['employee']))
                    <tr>
                        <td width="18%">{{ trans('assset/fields.employee-name') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['employee'] }}</td>
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
<h4>{{ trans('purchasing/menu.purchase-order') }}</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="3%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="13%">{{ trans('asset/fields.asset-number') }}</th>
            <th width="8%">{{ trans('operational/fields.truck-code') }}</th>
            <th width="22%">{{ trans('asset/fields.service-number') }}</th>
            <th width="7%" rowspan="2">{{ trans('asset/fields.finish-date') }}</th>
            <th width="10%">{{ trans('purchasing/fields.po-number') }}</th>
            <th width="20%">{{ trans('inventory/fields.item-code') }}</th>
            <th width="6%" rowspan="2">{{ trans('inventory/fields.wh') }}</th>
            <th width="5%">{{ trans('inventory/fields.qty-need') }}</th>
            <th width="6%" rowspan="2">{{ trans('inventory/fields.cost') }}</th>
        </tr>
        <tr>
            <th>{{ trans('asset/fields.asset-name') }}</th>
            <th>{{ trans('operational/fields.police-number') }}</th>
            <th>{{ trans('shared/common.description') }}</th>
            <th>{{ trans('purchasing/fields.po-date') }}</th>
            <th>{{ trans('inventory/fields.item-description') }}</th>
            <th>{{ trans('inventory/fields.uom') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalPurchaseOrder = 0; 
         ?>
         @foreach($purchaseOrder as $model)
         <?php  
         $totalPurchaseOrder += $model->total_price;
         $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
         $poDate     = !empty($model->po_date) ? new \DateTime($model->po_date) : null;
         ?>
        <tr>
            <td width="3%"  align="center" rowspan="2">{{ $no++ }}</td>
            <td width="13%">{{ $model->asset_number }}</td>
            <td width="8%">{{ $model->truck_code }}</td>
            <td width="22%">{{ $model->service_number }}</td>
            <td width="7%" rowspan="2">{{ !empty($finishDate) ? $finishDate->format('d-M-Y') : '' }}</td>
            <td width="10%">{{ $model->po_number }}</td>
            <td width="20%">{{ $model->item_code }}</td>
            <td width="6%" rowspan="2">{{ $model->wh_code }}</td>
            <td width="5%" align="right">{{ number_format($model->quantity_need) }}</td>
            <td width="6%" align="right" rowspan="2">{{ number_format($model->total_price) }}</td>
        </tr>
        <tr>
            <td>{{ $model->asset_name }}</td>
            <td>{{ $model->police_number }}</td>
            <td>{{ $model->service_description }}</td>
            <td>{{ !empty($poDate) ? $poDate->format('d-M-Y') : '' }}</td>
            <td>{{ $model->item_name }}</td>
            <td>{{ $model->uom }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="94%" align="right"><strong>{{ trans('shared/common.total') }}</strong></td>
            <td width="6%" align="right"><strong>{{ number_format($totalPurchaseOrder) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>{{ trans('inventory/menu.move-order') }}</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="3%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="13%">{{ trans('asset/fields.asset-number') }}</th>
            <th width="8%">{{ trans('operational/fields.truck-code') }}</th>
            <th width="22%">{{ trans('asset/fields.service-number') }}</th>
            <th width="7%" rowspan="2">{{ trans('asset/fields.finish-date') }}</th>
            <th width="10%">{{ trans('inventory/fields.mo-number') }}</th>
            <th width="10%">{{ trans('operational/fields.driver-code') }}</th>
            <th width="10%">{{ trans('inventory/fields.item-code') }}</th>
            <th width="6%" rowspan="2">{{ trans('inventory/fields.wh') }}</th>
            <th width="5%">{{ trans('inventory/fields.qty-need') }}</th>
            <th width="6%" rowspan="2">{{ trans('inventory/fields.cost') }}</th>
        </tr>
        <tr>
            <th>{{ trans('asset/fields.asset-name') }}</th>
            <th>{{ trans('operational/fields.police-number') }}</th>
            <th>{{ trans('shared/common.description') }}</th>
            <th>{{ trans('inventory/fields.mo-date') }}</th>
            <th>{{ trans('operational/fields.driver-name') }}</th>
            <th>{{ trans('inventory/fields.item-description') }}</th>
            <th>{{ trans('inventory/fields.uom') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalMoveOrder = 0; 
         ?>
         @foreach($moveOrder as $model)
         <?php  
         $totalMoveOrder += $model->cost;
         $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
         $moDate     = !empty($model->mo_date) ? new \DateTime($model->mo_date) : null;
         ?>
        <tr>
            <td width="3%"  align="center" rowspan="2">{{ $no++ }}</td>
            <td width="13%">{{ $model->asset_number }}</td>
            <td width="8%">{{ $model->truck_code }}</td>
            <td width="22%">{{ $model->service_number }}</td>
            <td width="7%" rowspan="2">{{ !empty($finishDate) ? $finishDate->format('d-M-Y') : '' }}</td>
            <td width="10%">{{ $model->mo_number }}</td>
            <td width="10%">{{ $model->driver_code }}</td>
            <td width="10%">{{ $model->item_code }}</td>
            <td width="6%" rowspan="2">{{ $model->wh_code }}</td>
            <td width="5%" align="right">{{ number_format($model->qty_need) }}</td>
            <td width="6%" align="right" rowspan="2">{{ number_format($model->cost) }}</td>
        </tr>
        <tr>
            <td>{{ $model->asset_name }}</td>
            <td>{{ $model->police_number }}</td>
            <td>{{ $model->service_description }}</td>
            <td>{{ !empty($moDate) ? $moDate->format('d-M-Y') : '' }}</td>
            <td>{{ $model->driver_name }}</td>
            <td>{{ $model->item_name }}</td>
            <td>{{ $model->uom }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="94%" align="right"><strong>{{ trans('shared/common.total') }}</strong></td>
            <td width="6%" align="right"><strong>{{ number_format($totalMoveOrder) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>{{ trans('payable/menu.service-invoice') }}</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="3%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="13%">{{ trans('asset/fields.asset-number') }}</th>
            <th width="8%">{{ trans('operational/fields.truck-code') }}</th>
            <th width="22%">{{ trans('asset/fields.service-number') }}</th>
            <th width="7%" rowspan="2">{{ trans('asset/fields.finish-date') }}</th>
            <th width="10%" rowspan="2">{{ trans('payable/fields.invoice-number') }}</th>
            <th width="10%" rowspan="2">{{ trans('payable/fields.payment-number') }}</th>
            <th width="10%" rowspan="2">{{ trans('payable/fields.payment-method') }}</th>
            <th width="10%" rowspan="2">{{ trans('payable/fields.payment-date') }}</th>
            <th width="7%" rowspan="2">{{ trans('payable/fields.total-amount') }}</th>
        </tr>
        <tr>
            <th>{{ trans('asset/fields.asset-name') }}</th>
            <th>{{ trans('operational/fields.police-number') }}</th>
            <th>{{ trans('shared/common.description') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalInvoice = 0; 
         ?>
         @foreach($invoice as $model)
         <?php  
         $totalInvoice   += $model->total_amount;
         $finishDate    = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
         $paymentDate   = !empty($model->payment_date) ? new \DateTime($model->payment_date) : null;
         ?>
        <tr>
            <td width="3%"  align="center" rowspan="2">{{ $no++ }}</td>
            <td width="13%">{{ $model->asset_number }}</td>
            <td width="8%">{{ $model->truck_code }}</td>
            <td width="22%">{{ $model->service_number }}</td>
            <td width="7%" rowspan="2">{{ !empty($finishDate) ? $finishDate->format('d-M-Y') : '' }}</td>
            <td width="10%" rowspan="2">{{ $model->invoice_number }}</td>
            <td width="10%" rowspan="2">{{ $model->payment_number }}</td>
            <td width="10%" rowspan="2">{{ $model->payment_method }}</td>
            <td width="10%" rowspan="2">{{ !empty($paymentDate) ? $paymentDate->format('d-M-Y') : '' }}</td>
            <td width="7%" rowspan="2" align="right">{{ number_format($model->total_amount) }}</td>
        </tr>
        <tr>
            <td>{{ $model->asset_name }}</td>
            <td>{{ $model->police_number }}</td>
            <td>{{ $model->service_description }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="93%" align="right"> <strong>{{ trans('shared/common.total') }}</strong></td>
            <td width="7%" align="right"><strong>{{ number_format($totalInvoice) }}</strong></td>
        </tr>
    </tbody>
</table>
<!-- <h4>Total Cost Maintenance Rp. <strong>{{ number_format($totalPurchaseOrder + $totalMoveOrder + $totalInvoice) }}</strong></h4> -->
@endsection
