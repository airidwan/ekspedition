@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['accountCode']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.account-code') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['accountCode'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['accountDescription']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.account-description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['accountDescription'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['periodMonth']) && !empty($filters['periodYear']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.period') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['periodMonth'].' - '.$filters['periodYear'] }}</td>
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
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="20%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="15%">{{ trans('general-ledger/fields.debet') }}</th>
            <th width="15%">{{ trans('general-ledger/fields.credit') }}</th>
            <th width="15%">{{ trans('general-ledger/fields.balance') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebet   = 0; 
         $totalCredit  = 0; 
         $totalBalance = 0; 
         ?>
         @foreach($models as $model)
         <?php  
         $balance       = abs($model->debet - $model->credit);
         $totalDebet   += $model->debet;
         $totalCredit  += $model->credit;
         $totalBalance += $balance;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="30%" >{{ $model->coa_description }}</td>
            <td width="20%" >{{ $model->coa_code }}</td>
            <td width="15%" align="right">{{ number_format($model->debet) }}</td>
            <td width="15%" align="right">{{ number_format($model->credit) }}</td>
            <td width="15%" align="right">{{ number_format($balance) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="30%" ></td>
            <td width="20%" >{{ trans('general-ledger/fields.balance') }}</td>
            <td width="15%" align="right">{{ number_format($totalDebet) }}</td>
            <td width="15%" align="right">{{ number_format($totalCredit) }}</td>
            <td width="15%" align="right">{{ number_format(abs($totalDebet - $totalCredit)) }}</td>
        </tr>
    </tbody>
</table>
@endsection
