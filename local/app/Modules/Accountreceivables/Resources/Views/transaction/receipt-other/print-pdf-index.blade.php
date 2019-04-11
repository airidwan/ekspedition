@extends('layouts.print')

<?php
    use App\Modules\Accountreceivables\Model\Transaction\Receipt;
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['receiptNumber']))
                    <tr>
                        <td width="18%">{{ trans('accountreceivables/fields.receipt-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['receiptNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['type']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.type') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['type'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['invoiceNumber']))
                    <tr>
                        <td width="18%">{{ trans('accountreceivables/fields.invoice-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['invoiceNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['personName']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.person-name') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['personName'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['description']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['description'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['receiptMethod']))
                    <tr>
                        <td width="18%">{{ trans('accountreceivables/fields.receipt-method') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['receiptMethod'] }}</td>
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
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="10%">{{ trans('accountreceivables/fields.receipt-number') }}</th>
            <th width="8%" rowspan="2">{{ trans('shared/common.date') }}</th>
            <th width="12%">{{ trans('accountreceivables/fields.invoice-number') }}</th>
            <th width="12%">{{ trans('asset/fields.asset-number') }}</th>
            <th width="12%" rowspan="2">{{ trans('shared/common.person-name') }}</th>
            <th width="20%" rowspan="2">{{ trans('shared/common.description') }}</th>
            <th width="13%">{{ trans('accountreceivables/fields.receipt-method') }}</th>
            <th width="8%" rowspan="2">{{ trans('accountreceivables/fields.amount') }}</th>
        </tr>
        <tr>
            <th >{{ trans('shared/common.type') }}</th>
            <th >{{ trans('payable/fields.trading') }}</th>
            <th >{{ trans('inventory/fields.item') }}</th>
            <th >{{ trans('accountreceivables/fields.cash-or-bank') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $totalAmount   = 0; 
        ?>
        @foreach($models as $model)
        <?php
            $date       = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
            $model      = Receipt::find($model->receipt_id);

        ?>
        <tr>
            <td width="5%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="10%" >{{ $model->receipt_number }}</td>
            <td width="8%" rowspan="2">{{ !empty($date) ? $date->format('d-m-Y') : '' }}</td>
            <td width="12%" >{{ !empty($model->invoiceApHeader) ? $model->invoiceApHeader->invoice_number : '' }}</td>
            <td width="12%" >{{ !empty($model->additionAsset) ? $model->additionAsset->asset_number : '' }}</td>
            <td width="12%" rowspan="2">{{ $model->person_name }}</td>
            <td width="20%" rowspan="2">{{ $model->description }}</td>
            <td width="13%" >{{ $model->receipt_method }}</td>
            <td width="8%" align="right" rowspan="2">{{ number_format($model->amount) }}</td>
        </tr>
        <tr>
            <td>{{ $model->type }}</td>
            <td>{{ !empty($model->invoiceApHeader) ? $model->invoiceApHeader->getTradingKasbonCode() . ' - ' . $model->invoiceApHeader->getTradingKasbonName() : '' }}</td>
            <td>{{ !empty($model->additionAsset) ? $model->additionAsset->item->description : '' }}</td>
            <td>{{ !empty($model->bank) ? $model->bank->bank_name : '' }}</td>
        </tr>
        <?php 
            $totalAmount   += $model->amount;
         ?>
        @endforeach
        <tr>
            <td width="92%" align="center"><strong>{{ trans('shared/common.total') }}</strong></td>
            <td width="8%" align="right"><strong>{{ number_format($totalAmount) }}</strong></td>
        </tr>
    </tbody>
</table>
@endsection
