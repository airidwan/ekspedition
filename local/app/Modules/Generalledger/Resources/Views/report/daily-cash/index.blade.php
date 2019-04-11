@extends('layouts.master')

@section('title', trans('general-ledger/menu.daily-cash'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.daily-cash') }}</h2>
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
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['date']) ? $filters['date'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="journalNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.journal-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="journalNumber" name="journalNumber" value="{{ !empty($filters['journalNumber']) ? $filters['journalNumber'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
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
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="3%">{{ trans('shared/common.num') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.journal-number') }}</th>
                                <th width="37%">{{ trans('shared/common.description') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.debet') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.credit') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $totalDebetExpense     = 0;
                            $totalCreditExpense    = 0;
                            $totalBalanceExpense   = 0;
                            ?>
                            @foreach($balance as $model)
                            <?php
                            $balanceBalance       = $model->debet - $model->credit;
                            $totalDebetExpense   += $model->debet;
                            $totalCreditExpense  += $model->credit;

                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td> </td>
                                <td>{{ trans('general-ledger/fields.beginning-balance') }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                                <td class="text-right">{{ $balanceBalance < 0 ? '(' : '' }}{{ number_format(abs($balanceBalance)) }}{{ $balanceBalance < 0 ? ')' : '' }}</td>
                            </tr>
                            @endforeach
                            @foreach($expense as $key => $model)
                            <?php
                            $balanceExpense       = $model->debet - $model->credit;
                            $totalBalanceExpense += $balanceExpense;
                            $totalDebetExpense   += $model->debet;
                            $totalCreditExpense  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->journal_number }}</td>
                                <td>{{ $model->gl_header_description }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                                <td class="text-right">{{ $balanceExpense < 0 ? '(' : '' }}{{ number_format(abs($balanceExpense)) }}{{ $balanceExpense < 0 ? ')' : '' }}</td>
                            </tr>
                            @endforeach
                            @if($totalCreditExpense != 0)
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetExpense) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalCreditExpense) }}</strong></td>
                                <?php $totalBalanceExpense = $totalDebetExpense-$totalCreditExpense; ?>
                                <td class="text-right"><strong>{{ $totalBalanceExpense < 0 ? '(' : '' }}{{ number_format(abs($totalBalanceExpense)) }}{{ $totalBalanceExpense < 0 ? ')' : '' }}</strong></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection
