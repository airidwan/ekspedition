@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['accountFrom']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.account-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['accountFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['subaccountFrom']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.subaccount-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['subaccountFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['futureFrom']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.future-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['futureFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['accountTo']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.account-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['accountTo'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['subaccountTo']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.subaccount-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['subaccountTo'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['futureTo']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.future-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['futureTo'] }}</td>
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
                @if (!empty($filters['periodMonth']) && !empty($filters['periodYear']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.period') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['periodMonth'].' - '.$filters['periodYear'] }}</td>
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
            <th width="12%">{{ trans('shared/common.date') }}</th>
            <th width="23%">{{ trans('general-ledger/fields.account-combination') }}</th>
            <th width="30%">{{ trans('shared/common.transaction-description') }}</th>
            <th width="15%" rowspan="2">{{ trans('general-ledger/fields.debet') }}</th>
            <th width="15%" rowspan="2">{{ trans('general-ledger/fields.credit') }}</th>
        </tr>
        <tr>
            <th>{{ trans('general-ledger/fields.journal-number') }}</th>
            <th>{{ trans('general-ledger/fields.account-code') }}</th>
            <th>{{ trans('shared/common.description') }}</th>
            
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; $totalDebet = 0; $totalCredit = 0;?>
         @foreach($models as $model)
         <?php 
         $date = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null; 
         $totalDebet  += $model->debet;
         $totalCredit += $model->credit;
         ?>
        <tr>
            <td width="5%"  align="center" rowspan="2">{{ $no++ }}</td>
            <td width="12%" >{{ !empty($date) ? $date->format('d-m-Y') : '' }}</td>
            <td width="23%" >{{ $model->account_combination_code }}</td>
            <td width="30%" >{{ $model->header_description }}</td>
            <td width="15%" align="right" rowspan="2">{{ number_format($model->debet) }}</td>
            <td width="15%" align="right" rowspan="2">{{ number_format($model->credit) }}</td>
        </tr>
        <tr>
            <td>{{ $model->journal_number }}</td>
            <td>{{ $model->coa_description }}</td>
            <td>{{ $model->line_description }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="70%" align="center"><b>{{ trans('shared/common.total') }}</b></td>
            <td width="15%" align="right"><b>{{ number_format($totalDebet) }}</b></td>
            <td width="15%" align="right"><b>{{ number_format($totalCredit) }}</b></td>
        </tr>
    </tbody>
</table>
@endsection
