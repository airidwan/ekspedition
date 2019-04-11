@extends('layouts.master')

@section('title', trans('general-ledger/menu.income'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.income') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="accountCode" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="accountCode" name="accountCode" value="{{ !empty($filters['accountCode']) ? $filters['accountCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="accountDescription" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="accountDescription" name="accountDescription" value="{{ !empty($filters['accountDescription']) ? $filters['accountDescription'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="period" class="col-sm-4 control-label">{{ trans('shared/common.period') }}</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="periodMonth" id="periodMonth">
                                        <option value="">ALL</option>
                                        @foreach($optionPeriodMonth as $value => $label)
                                            <option value="{{ $value }}" {{ !empty($filters['periodMonth']) && $filters['periodMonth'] == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <select class="form-control" name="periodYear" id="periodYear">
                                        <option value="">ALL</option>
                                        @foreach($optionPeriodYear as $option)
                                            <option value="{{ $option }}" {{ !empty($filters['periodYear']) && $filters['periodYear'] == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                            $branchId  = '';
                            if (!empty(old('branchId'))) {
                                $branchId = old('branchId');
                            } elseif (!empty($filters['branchId'])) {
                                $branchId = $filters['branchId'];
                            }
                            ?>
                            <div class="form-group {{ $errors->has('branchId') ? 'has-error' : '' }}">
                                <label for="branchId" class="col-sm-4 control-label">Cabang </label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="branchId" id="branchId">
                                        <option value="" >All Branch</option>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ $branchId == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('branchId'))
                                        <span class="help-block">{{ $errors->first('branchId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">{{ trans('shared/common.date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateFrom']) ? $filters['dateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateTo']) ? $filters['dateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-pdf') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i> {{ trans('shared/common.print-pdf') }}
                                </a>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <h4>Main Revenue</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetRevenue   = 0;
                            $totalCreditRevenue  = 0;
                            $totalBalanceRevenue = 0;
                            ?>
                            @foreach($revenue as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceRevenue       = $model->debet - $model->credit;
                            $totalBalanceRevenue += $balanceRevenue;
                            $totalDebetRevenue   += $model->debet;
                            $totalCreditRevenue  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalanceRevenue = $totalDebetRevenue-$totalCreditRevenue; ?>
                                <td class="text-right"><strong>{{ $totalCreditRevenue < 0 ? '(' : '' }}{{ number_format($totalCreditRevenue) }}{{ $totalCreditRevenue < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Deduction</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetDeduction   = 0;
                            $totalCreditDeduction  = 0;
                            $totalBalanceDeduction = 0;
                            ?>
                            @foreach($deduction as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceDeduction       = $model->debet - $model->credit;
                            $totalBalanceDeduction += $balanceDeduction;
                            $totalDebetDeduction   += $model->debet;
                            $totalCreditDeduction  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalanceDeduction = $totalDebetDeduction-$totalCreditDeduction; ?>
                                <td class="text-right"><strong>{{ $totalDebetDeduction < 0 ? '(' : '' }}{{ number_format($totalDebetDeduction) }}{{ $totalDebetDeduction < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <h3>Total Gross Income {{ number_format($totalCreditRevenue - $totalDebetDeduction) }}</h3>
                <div class="clearfix"></div>
                <hr>
                <h4>Operational Expense</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetBebanOperasional   = 0;
                            $totalCreditBebanOperasional  = 0;
                            $totalBalanceBebanOperasional = 0;
                            ?>
                            @foreach($bebanOperasional as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceBebanOperasional       = $model->debet - $model->credit;
                            $totalBalanceBebanOperasional += $balanceBebanOperasional;
                            $totalDebetBebanOperasional   += $model->debet;
                            $totalCreditBebanOperasional  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalanceBebanOperasional = $totalDebetBebanOperasional-$totalCreditBebanOperasional; ?>
                                <td class="text-right"><strong>{{ $totalDebetBebanOperasional < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanOperasional)) }}{{ $totalDebetBebanOperasional < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Administration Expense</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetBebanAdministrasi   = 0;
                            $totalCreditBebanAdministrasi  = 0;
                            $totalBalanceBebanAdministrasi = 0;
                            ?>
                            @foreach($bebanAdministrasi as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceBebanAdministrasi       = $model->debet - $model->credit;
                            $totalBalanceBebanAdministrasi += $balanceBebanAdministrasi;
                            $totalDebetBebanAdministrasi   += $model->debet;
                            $totalCreditBebanAdministrasi  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalanceBebanAdministrasi = $totalDebetBebanAdministrasi-$totalCreditBebanAdministrasi; ?>
                                <td class="text-right"><strong>{{ $totalDebetBebanAdministrasi < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanAdministrasi)) }}{{ $totalDebetBebanAdministrasi < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Other Expense</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetBebanLain   = 0;
                            $totalCreditBebanLain  = 0;
                            $totalBalanceBebanLain = 0;
                            ?>
                            @foreach($bebanLain as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceBebanLain       = $model->debet - $model->credit;
                            $totalBalanceBebanLain += $balanceBebanLain;
                            $totalDebetBebanLain   += $model->debet;
                            $totalCreditBebanLain  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalanceBebanLain = $totalDebetBebanLain-$totalCreditBebanLain; ?>
                                <td class="text-right"><strong>{{ $totalDebetBebanLain < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanLain)) }}{{ $totalDebetBebanLain < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Tax Expense</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetBebanPajak   = 0;
                            $totalCreditBebanPajak  = 0;
                            $totalBalanceBebanPajak = 0;
                            ?>
                            @foreach($bebanPajak as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceBebanPajak       = $model->debet - $model->credit;
                            $totalBalanceBebanPajak += $balanceBebanPajak;
                            $totalDebetBebanPajak   += $model->debet;
                            $totalCreditBebanPajak  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalanceBebanPajak = $totalDebetBebanPajak-$totalCreditBebanPajak; ?>
                                <td class="text-right"><strong>{{ $totalDebetBebanPajak < 0 ? '(' : '' }}{{ number_format(abs($totalDebetBebanPajak)) }}{{ $totalDebetBebanPajak < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php $totalDebetExpense =  $totalDebetBebanOperasional + $totalDebetBebanAdministrasi + $totalDebetBebanLain + $totalDebetBebanPajak; ?>
                <h3>Total Expense {{ number_format($totalDebetExpense) }}</h3>
                <div class="clearfix"></div>
                <hr>
                <h4>Other Revenue</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetPendapatanLain   = 0;
                            $totalCreditPendapatanLain  = 0;
                            $totalBalancePendapatanLain = 0;
                            ?>
                            @foreach($pendapatanLain as $key => $model)
                            <?php
                            $period                      = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balancePendapatanLain       = $model->debet - $model->credit;
                            $totalBalancePendapatanLain += $balancePendapatanLain;
                            $totalDebetPendapatanLain   += $model->debet;
                            $totalCreditPendapatanLain  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <?php $totalBalancePendapatanLain = $totalDebetPendapatanLain-$totalCreditPendapatanLain; ?>
                                <td class="text-right"><strong>{{ $totalCreditPendapatanLain < 0 ? '(' : '' }}{{ number_format($totalCreditPendapatanLain) }}{{ $totalCreditPendapatanLain < 0 ? ')' : '' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                </div>
                <div class="clearfix"></div>
                <hr>
                
                <?php 
                    $profitLoss = $totalCreditRevenue - $totalDebetDeduction - $totalDebetExpense + $totalCreditPendapatanLain;
                ?>
                @if($profitLoss >= 0)
                <h2 style="background-color : #ACFA58; padding: 5px;">
                @else
                <h2 style="background-color : #ff9999; padding: 5px;">
                @endif
                {{ $profitLoss >= 0 ? 'Profit Rp. '.number_format($profitLoss) : 'Loss Rp. '.number_format($profitLoss) }}
                </h2>
            </div>
        </div>
    </div>
</div>
@endsection
