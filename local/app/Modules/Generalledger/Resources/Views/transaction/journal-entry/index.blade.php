@extends('layouts.master')

@section('title', trans('general-ledger/menu.journal-entry'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.journal-entry') }}</h2>
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
                                <label for="journalNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.journal-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="journalNumber" name="journalNumber" value="{{ !empty($filters['journalNumber']) ? $filters['journalNumber'] : '' }}">
                                </div>
                            </div>
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
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label"></label>
                                <div class="col-sm-8">
                                    <?php $jenis = !empty($filters['jenis']) ? $filters['jenis'] : 'headers' ?>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio1" value="headers" {{ $jenis == 'headers' ? 'checked' : '' }}> Headers
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio2" value="lines" {{ $jenis == 'lines' ? 'checked' : '' }}> Lines
                                    </label>
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
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="status" id="status">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                            <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @can('access', [$resource, 'postAll'])
                                <a href="{{ URL($url . '/post-all') }}" class="btn btn-sm btn-success"><i class="fa fa-upload"></i> {{ trans('general-ledger/fields.post-all') }}</a>
                                @endcan
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                @if (empty($filters['jenis']) || $filters['jenis'] == 'headers')
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="5%">{{ trans('shared/common.num') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.journal-number') }}<hr/>
                                    {{ trans('shared/common.category') }}</th>
                                <th width="10%">{{ trans('shared/common.date') }}<hr/>
                                    {{ trans('shared/common.period') }}</th>
                                <th width="40%">{{ trans('shared/common.description') }}</th>
                                <th width="10%">{{ trans('general-ledger/fields.dr') }}</th>
                                <th width="10%">{{ trans('general-ledger/fields.cr') }}</th>
                                <th width="5%">{{ trans('shared/common.status') }}</th>
                                <th width="5%">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <?php
                            $journal     = App\Modules\Generalledger\Model\Transaction\JournalHeader::find($model->journal_header_id);
                            $journalDate = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                            $period      = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->journal_number }}<hr/>
                                    {{ $model->category }}</td>
                                <td class="text-center">{{ $journalDate !== null ? $journalDate->format('d-m-Y') : '' }}<hr/>
                                    {{ $period !== null ? $period->format('M-Y') : '' }}</td>
                                <td>{{ $model->description }}</td>
                                <td class="text-right">{{ number_format(!empty($journal) ? $journal->totalDebet() : 0) }}</td>
                                <td class="text-right">{{ number_format(!empty($journal) ? $journal->totalCredit() : 0) }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->journal_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="5%">{{ trans('shared/common.num') }}</th>
                                <th width="15%">{{ trans('general-ledger/fields.journal-number') }}<hr/>
                                    {{ trans('shared/common.category') }}</th>
                                <th min-width="35%">{{ trans('general-ledger/fields.coa-combination') }}</th>
                                <th width="10%">{{ trans('general-ledger/fields.debet') }}</th>
                                <th width="10%">{{ trans('general-ledger/fields.credit') }}</th>
                                <th width="20%">{{ trans('shared/common.description') }}</th>
                                <th width="5%">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <?php
                            $journalDate = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                            $period      = !empty($model->period) ? new \DateTime($model->period) : null;
                            $line = JournalLine::find($model->journal_line_id);
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->journal_number }}<hr/>
                                    {{ $model->category }}</td>
                                <td>{{ $line->accountCombination->getCombinationDescription() }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                                <td>{{ $model->description }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->journal_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
