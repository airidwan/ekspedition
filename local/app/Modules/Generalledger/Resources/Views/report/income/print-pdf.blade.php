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
                @if (!empty($filters['branchId']))
                    <?php $branch = \DB::table('op.mst_branch')->where('branch_id',$filters['branchId'])->first(); ?>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.branch') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $branch->branch_name }}</td>
                    </tr>
                @else
                    <tr>
                        <td width="18%">{{ trans('operational/fields.branch') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">All Branch</td>
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
<h4>Main Revenue</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetRevenue   = 0; 
         $totalCreditRevenue  = 0; 
         $totalBalanceRevenue = 0; 
         ?>
         @foreach($revenue as $model)
         <?php  
         $balanceRevenue       = $model->debet - $model->credit;
         $totalDebetRevenue   += $model->debet;
         $totalCreditRevenue  += $model->credit;
         $totalBalanceRevenue += $balanceRevenue;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->credit) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalanceRevenue = $totalDebetRevenue-$totalCreditRevenue; ?>
            <td width="25%" align="right"><strong>{{ $totalCreditRevenue < 0 ? '(' : '' }}{{ number_format($totalCreditRevenue) }}{{ $totalCreditRevenue < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Deduction</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetDeduction   = 0; 
         $totalCreditDeduction  = 0; 
         $totalBalanceDeduction = 0; 
         ?>
         @foreach($deduction as $model)
         <?php  
         $balanceDeduction       = $model->debet - $model->credit;
         $totalDebetDeduction   += $model->debet;
         $totalCreditDeduction  += $model->credit;
         $totalBalanceDeduction += $balanceDeduction;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalanceDeduction = $totalDebetDeduction-$totalCreditDeduction; ?>
            <td width="25%" align="right"><strong>{{ $totalDebetDeduction < 0 ? '(' : '' }}{{ number_format(abs($totalDebetDeduction)) }}{{ $totalDebetDeduction < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Total Gross Income {{ number_format($totalCreditRevenue - $totalDebetDeduction) }}</h4>
<hr>
<br>
<h4>Operational Expense</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetBebanOperasional   = 0; 
         $totalCreditBebanOperasional  = 0; 
         $totalBalanceBebanOperasional = 0; 
         ?>
         @foreach($bebanOperasional as $model)
         <?php  
         $balanceBebanOperasional       = $model->debet - $model->credit;
         $totalDebetBebanOperasional   += $model->debet;
         $totalCreditBebanOperasional  += $model->credit;
         $totalBalanceBebanOperasional += $balanceBebanOperasional;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalanceBebanOperasional = $totalDebetBebanOperasional-$totalCreditBebanOperasional; ?>
            <td width="25%" align="right"><strong>{{ $totalDebetBebanOperasional < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanOperasional)) }}{{ $totalDebetBebanOperasional < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Administration Expense</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetBebanAdministrasi   = 0; 
         $totalCreditBebanAdministrasi  = 0; 
         $totalBalanceBebanAdministrasi = 0; 
         ?>
         @foreach($bebanAdministrasi as $model)
         <?php  
         $balanceBebanAdministrasi       = $model->debet - $model->credit;
         $totalDebetBebanAdministrasi   += $model->debet;
         $totalCreditBebanAdministrasi  += $model->credit;
         $totalBalanceBebanAdministrasi += $balanceBebanAdministrasi;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalanceBebanAdministrasi = $totalDebetBebanAdministrasi-$totalCreditBebanAdministrasi; ?>
            <td width="25%" align="right"><strong>{{ $totalDebetBebanAdministrasi < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanAdministrasi)) }}{{ $totalDebetBebanAdministrasi < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Other Expense</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetBebanLain   = 0; 
         $totalCreditBebanLain  = 0; 
         $totalBalanceBebanLain = 0; 
         ?>
         @foreach($bebanLain as $model)
         <?php  
         $balanceBebanLain       = $model->debet - $model->credit;
         $totalDebetBebanLain   += $model->debet;
         $totalCreditBebanLain  += $model->credit;
         $totalBalanceBebanLain += $balanceBebanLain;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalanceBebanLain = $totalDebetBebanLain-$totalCreditBebanLain; ?>
            <td width="25%" align="right"><strong>{{ $totalDebetBebanLain < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanLain)) }}{{ $totalDebetBebanLain < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Tax Expense</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetBebanPajak   = 0; 
         $totalCreditBebanPajak  = 0; 
         $totalBalanceBebanPajak = 0; 
         ?>
         @foreach($bebanPajak as $model)
         <?php  
         $balanceBebanPajak       = $model->debet - $model->credit;
         $totalDebetBebanPajak   += $model->debet;
         $totalCreditBebanPajak  += $model->credit;
         $totalBalanceBebanPajak += $balanceBebanPajak;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalanceBebanPajak = $totalDebetBebanPajak-$totalCreditBebanPajak; ?>
            <td width="25%" align="right"><strong>{{ $totalDebetBebanPajak < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanPajak)) }}{{ $totalDebetBebanPajak < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<?php $totalDebetExpense = $totalDebetBebanOperasional + $totalDebetBebanAdministrasi + $totalDebetBebanLain + $totalDebetBebanPajak; ?>
<h4>Total Expense {{ number_format($totalDebetExpense) }}</h4>
<hr>
<br>
<h4>Other Revenue</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetPendapatanLain   = 0; 
         $totalCreditPendapatanLain  = 0; 
         $totalBalancePendapatanLain = 0; 
         ?>
         @foreach($pendapatanLain as $model)
         <?php  
         $balancePendapatanLain       = $model->debet - $model->credit;
         $totalDebetPendapatanLain   += $model->debet;
         $totalCreditPendapatanLain  += $model->credit;
         $totalBalancePendapatanLain += $balancePendapatanLain;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->credit) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" align="right"><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <?php $totalBalancePendapatanLain = $totalDebetPendapatanLain-$totalCreditPendapatanLain; ?>
            <td width="25%" align="right"><strong>{{ $totalCreditPendapatanLain < 0 ? '(' : '' }}{{ number_format($totalCreditPendapatanLain) }}{{ $totalCreditPendapatanLain < 0 ? ')' : '' }}</strong></td>
        </tr>
    </tbody>
</table>
<hr><br><br><br>
<?php 
    $profitLoss = $totalCreditRevenue - $totalDebetDeduction - $totalDebetExpense + $totalCreditPendapatanLain;
?>
@if($profitLoss >= 0)
<h3 style="background-color : #ACFA58; padding: 5px;">
@else
<h3 style="background-color : #ff9999; padding: 5px;">
@endif
{{ $profitLoss >= 0 ? 'Profit Rp. '.number_format($profitLoss) : 'Loss Rp. '.number_format($profitLoss) }}
</h3>
@endsection
