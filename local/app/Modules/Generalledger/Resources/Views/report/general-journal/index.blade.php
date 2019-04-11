@extends('layouts.master')

@section('title', trans('general-ledger/menu.general-journal'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.general-journal') }}</h2>
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
                        <div class="col-sm-12">
                            <div class="col-sm-6 portlets">
                                <div class="form-group">
                                    <label for="accountFrom" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-from') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="accountFrom" name="accountFrom" value="{{ !empty($filters['accountFrom']) ? $filters['accountFrom'] : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="subAccountFrom" class="col-sm-4 control-label">{{ trans('general-ledger/fields.sub-account-from') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="subAccountFrom" name="subAccountFrom" value="{{ !empty($filters['subAccountFrom']) ? $filters['subAccountFrom'] : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="futureFrom" class="col-sm-4 control-label">{{ trans('general-ledger/fields.future-from') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="futureFrom" name="futureFrom" value="{{ !empty($filters['futureFrom']) ? $filters['futureFrom'] : '' }}">
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
                            </div>
                            <div class="col-sm-6 portlets">
                                <div class="form-group">
                                    <label for="accountTo" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-to') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="accountTo" name="accountTo" value="{{ !empty($filters['accountTo']) ? $filters['accountTo'] : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="subAccountTo" class="col-sm-4 control-label">{{ trans('general-ledger/fields.sub-account-to') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="subAccountTo" name="subAccountTo" value="{{ !empty($filters['subAccountTo']) ? $filters['subAccountTo'] : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="futureTo" class="col-sm-4 control-label">{{ trans('general-ledger/fields.future-to') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="futureTo" name="futureTo" value="{{ !empty($filters['futureTo']) ? $filters['futureTo'] : '' }}">
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
                        </div>
                        <div class="col-sm-12">
                            <div class="col-sm-6 portlets">
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
                                    <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="category" id="category">
                                            <option value="">ALL</option>
                                            @foreach($optionCategory as $category)
                                                <option value="{{ $category }}" {{ !empty($filters['category']) && $filters['category'] == $category ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                <th width="5%">{{ trans('shared/common.num') }}</th>
                                <th width="12%">{{ trans('shared/common.date') }}<hr>
                                    {{ trans('general-ledger/fields.journal-number') }}</th>
                                <th width="23%">{{ trans('general-ledger/fields.account-combination') }}<hr>
                                    {{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="30%">{{ trans('shared/common.transaction-description') }}<hr>
                                    {{ trans('shared/common.description') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.debet') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.credit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(!empty($models)){
                                $no = ($models->currentPage() - 1) * $models->perPage() + 1; 
                            }
                            ?>
                            @foreach($models as $key => $model)
                            <?php
                            $period      = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->journal_number }}<hr>
                                    {{ !empty($period) ? $period->format('d-m-Y') : '' }}</td>
                                <td>{{ $model->account_combination_code }}<hr>
                                    {{ $model->coa_description }}</td>
                                <td>{{ $model->header_description }}<hr>
                                    {{ $model->line_description }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                {{ !empty($models) ? $models->render() : '' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
