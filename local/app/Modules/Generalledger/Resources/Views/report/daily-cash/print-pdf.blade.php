@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['date']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['date'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['journalNumber']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.journal-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['journalNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['description']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['description'] }}</td>
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
            <th width="15%">{{ trans('general-ledger/fields.journal-number') }}</th>
            <th width="35%">{{ trans('shared/common.description') }}</th>
            <th width="15%">{{ trans('general-ledger/fields.debet') }}</th>
            <th width="15%">{{ trans('general-ledger/fields.credit') }}</th>
            <th width="15%">{{ trans('general-ledger/fields.balance') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetExpense   = 0; 
         $totalCreditExpense  = 0; 
         $totalBalanceExpense = 0; 
         ?>
        @foreach($balance as $key => $model)
        <?php
        $balanceBalance       = $model->debet - $model->credit;
        $totalDebetExpense   += $model->debet;
        $totalCreditExpense  += $model->credit;
        $totalBalanceExpense += $balanceBalance;
        ?>
         <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="15%" ></td>
            <td width="35%" >{{ trans('general-ledger/fields.beginning-balance') }}</td>
            <td width="15%" align="right">{{ !empty($model) ? number_format($model->debet) : 0 }}</td>
            <td width="15%" align="right">{{ !empty($model) ? number_format($model->credit) : 0 }}</td>
            <td width="15%" align="right">{{ $balanceBalance < 0 ? '(' : '' }}{{ number_format(abs($balanceBalance)) }}{{ $balanceBalance < 0 ? ')' : '' }}</td>
        </tr>
        @endforeach         
        @foreach($expense as $model)
         <?php  
         $balanceExpense       = $model->debet - $model->credit;
         $totalDebetExpense   += $model->debet;
         $totalCreditExpense  += $model->credit;
         $totalBalanceExpense += $balanceExpense;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="15%" >{{ $model->journal_number }}</td>
            <td width="35%" >{{ $model->gl_header_description }}</td>
            <td width="15%" align="right">{{ number_format($model->debet) }}</td>
            <td width="15%" align="right">{{ number_format($model->credit) }}</td>
            <td width="15%" align="right">{{ $balanceExpense < 0 ? '(' : '' }}{{ number_format(abs($balanceExpense)) }}{{ $balanceExpense < 0 ? ')' : '' }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="15%" ></td>
            <td width="35%" align="right"><strong>{{ trans('general-ledger/fields.balance') }}</strong></td>
            <td width="15%" align="right"><strong>{{ number_format($totalDebetExpense) }}</strong></td>
            <td width="15%" align="right"><strong>{{ number_format($totalCreditExpense) }}</strong></td>
            <?php $totalBalanceExpense = $totalDebetExpense-$totalCreditExpense; ?>
            <td width="15%" align="right"><strong>{{ $totalBalanceExpense < 0 ? '(' : '' }}{{ number_format(abs($totalBalanceExpense)) }}{{ $totalBalanceExpense < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>

@endsection
