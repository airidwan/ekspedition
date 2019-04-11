@extends('layouts.master')

@section('title', trans('general-ledger/menu.account-post'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.account-post') }}</h2>
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
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.debet') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.credit') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebet  = 0;
                            $totalCredit = 0;
                            ?>
                            @foreach($models as $key => $model)
                            <?php
                            $period      = !empty($model->period) ? new \DateTime($model->period) : null;
                            $totalDebet  += $model->debet;
                            $totalCredit += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                                <td class="text-right">{{ number_format(abs($model->debet - $model->credit)) }}</td>
                            </tr>
                            @endforeach
                            @if($totalDebet != 0 && $totalCredit != 0)
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebet) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalCredit) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format(abs($totalDebet - $totalCredit)) }}</strong></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
