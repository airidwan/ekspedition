@extends('layouts.master')

@section('title', trans('general-ledger/menu.journal-entry'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.journal-entry') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->journal_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="journalNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.journal-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="journalNumber" name="journalNumber" value="{{ $model->journal_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('journalDate') ? 'has-error' : '' }}">
                                        <label for="journalDate" class="col-sm-4 control-label">
                                            {{ trans('shared/common.tanggal') }}
                                             <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php
                                                $journalDate = new \DateTime($model->journal_date);
                                                $strJournalDate = count($errors) > 0 ? old('journalDate') : $journalDate->format('d-m-Y');
                                                ?>
                                                <input type="text" id="journalDate" name="journalDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $strJournalDate }}"  {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('journalDate'))
                                                <span class="help-block">{{ $errors->first('journalDate') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                        <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <?php $category = count($errors) > 0 ? old('category') : $model->category ?>
                                            <select class="form-control" name="category" id="category" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <option value="">Select Category</option>
                                                @foreach($optionCategory as $option)
                                                    <option value="{{ $option }}" {{ $option == $category ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('category'))
                                                <span class="help-block">{{ $errors->first('category') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="period" class="col-sm-4 control-label">{{ trans('shared/common.period') }} <span class="required">*</span></label>
                                        <?php
                                        if (count($errors) > 0) {
                                            $periodMonth = old('periodMonth');
                                            $periodYear  = old('periodYear');
                                        } else {
                                            $period      = new \DateTime($model->journal_date);
                                            $periodMonth = intval($period->format('m'));
                                            $periodYear  = intval($period->format('Y'));
                                        }
                                        ?>
                                        <div class="col-sm-4">
                                            <select class="form-control" name="periodMonth" id="periodMonth" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                @foreach($optionPeriodMonth as $value => $label)
                                                    <option value="{{ $value }}" {{ $value == $periodMonth ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-control" name="periodYear" id="periodYear" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                @foreach($optionPeriodYear as $option)
                                                    <option value="{{ $option }}" {{ $option == $periodYear ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('general-ledger/fields.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea id="description" name="description" class="form-control" {{ !$model->isOpen() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                                <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                $totalDebet  = !empty($model) ? $model->totalDebet() : 0; 
                                $totalCredit = !empty($model) ? $model->totalCredit() : 0; 
                                ?>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalDebet" class="col-sm-4 control-label">{{ trans('general-ledger/fields.total-debet') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalDebet" name="totalDebet" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalDebet')) : $totalDebet }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalCredit" class="col-sm-4 control-label">{{ trans('general-ledger/fields.total-credit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalCredit" name="totalCredit" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalCredit')) : $totalCredit }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                     @if ($model->isOpen())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line">
                                                        <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                                    </a>
                                                    <a id="clear-lines" href="#" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-remove"></i> {{ trans('shared/common.clear') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('general-ledger/fields.coa-combination') }}</th>
                                                    <th width="150px">{{ trans('general-ledger/fields.debet') }}</th>
                                                    <th width="150px">{{ trans('general-ledger/fields.credit') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>

                                                    @if($model->isOpen())
                                                        <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <?php $indexLine = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td >{{ old('accountCombinationCode')[$i] }}<hr/>{{ old('accountCombinationDescription')[$i] }}</td>
                                                    <td class="text-right">{{ number_format(old('debet')[$i]) }}</td>
                                                    <td class="text-right">{{ number_format(old('credit')[$i]) }}</td>
                                                    <td>{{ old('descriptionLine')[$i] }}</td>
                                                    @if($model->isOpen())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="accountCombinationId[]" value="{{ old('accountCombinationId')[$i] }}">
                                                        <input type="hidden" name="accountCombinationCode[]" value="{{ old('accountCombinationCode')[$i] }}">
                                                        <input type="hidden" name="accountCombinationDescription[]" value="{{ old('accountCombinationDescription')[$i] }}">
                                                        <input type="hidden" name="debet[]" value="{{ old('debet')[$i] }}">
                                                        <input type="hidden" name="credit[]" value="{{ old('credit')[$i] }}">
                                                        <input type="hidden" name="descriptionLine[]" value="{{ old('descriptionLine')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $indexLine++; ?>
                                                @endfor

                                                @else
                                                @foreach($model->lines()->orderBy('debet', 'desc')->orderBy('credit', 'desc')->get() as $line)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td >
                                                        {{ !empty($line->accountCombination) ? $line->accountCombination->getCombinationCode() : '' }}<hr/>
                                                        {{ !empty($line->accountCombination) ? $line->accountCombination->getCombinationDescription() : '' }}
                                                    </td>
                                                    <td class="text-right"> {{ number_format($line->debet) }} </td>
                                                    <td class="text-right"> {{ number_format($line->credit) }} </td>
                                                    <td> {{ $line->description }} </td>
                                                    @if($model->isOpen())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ $line->journal_line_id }}">
                                                        <input type="hidden" name="accountCombinationId[]" value="{{ $line->account_combination_id }}">
                                                        <input type="hidden" name="accountCombinationCode[]" value="{{ !empty($line->accountCombination) ? $line->accountCombination->getCombinationCode() : '' }}">
                                                        <input type="hidden" name="accountCombinationDescription[]" value="{{ !empty($line->accountCombination) ? $line->accountCombination->getCombinationDescription() : '' }}">
                                                        <input type="hidden" name="debet[]" value="{{ $line->debet }}">
                                                        <input type="hidden" name="credit[]" value="{{ $line->credit }}">
                                                        <input type="hidden" name="descriptionLine[]" value="{{ $line->description }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $indexLine++; ?>

                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if($model->isOpen())
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                    @can('access', [$resource, 'post'])
                                    <button type="submit" name="btn-post" class="btn btn-sm btn-info"><i class="fa fa-money"></i> {{ trans('general-ledger/fields.post') }}</button>
                                    @endcan
                                @elseif($model->isPost())
                                    @can('access', [$resource, 'reserve'])
                                    <button type="submit" name="btn-reserve" class="btn btn-sm btn-danger"><i class="fa fa-remove"></i> {{ trans('general-ledger/fields.reserve') }}</button>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-line" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"> <span id="title-modal-line">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                <input type="hidden" name="indexFormLine" id="indexFormLine" value="">
                                <input type="hidden" name="lineId" id="lineId" value="">
                                <div class="col-sm-12 portlets">
                                    <div class="form-group">
                                        <label for="accountCombinationCode" class="col-sm-4 control-label">
                                            {{ trans('general-ledger/fields.combination-code') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="accountCombinationId" id="accountCombinationId" value="">
                                                <input type="text" class="form-control" id="accountCombinationCode" name="accountCombinationCode" readonly>
                                                <span class="btn input-group-addon" id="show-lov-account-combination"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="accountCombinationDescription" class="col-sm-4 control-label">
                                            {{ trans('general-ledger/fields.combination-description') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="accountCombinationDescription" name="accountCombinationDescription" readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="debet" class="col-sm-4 control-label">{{ trans('general-ledger/fields.debet') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="debet" name="debet" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="credit" class="col-sm-4 control-label">{{ trans('general-ledger/fields.credit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="credit" name="credit" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="descriptionLine" class="col-sm-4 control-label">{{ trans('shared/common.description') }}<span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="descriptionLine" name="descriptionLine"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" id="cancel-save-line" data-dismiss="modal">{{ trans('shared/common.cancel') }}</button>
                <button type="button" class="btn btn-sm btn-primary" id="save-line">
                    <span id="submit-modal-line">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modal-lov-account-combination" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.account-combination') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchAccountCombination" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchAccountCombination" name="searchAccountCombination">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-account-combination" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('general-ledger/fields.combination-code') }}</th>
                                    <th>{{ trans('general-ledger/fields.combination-description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent()
<script type="text/javascript">
var indexLine = {{ $indexLine }};

$(document).on('ready', function(){
    $('#clear-lines').on('click', clearLines);
    $('.add-line').on('click', addLine);
    $('#show-lov-account-combination').on('click', showLovAccountCombination);
    $('#searchAccountCombination').on('keyup', loadLovAccountCombination);
    $('#table-lov-account-combination tbody').on('click', 'tr', selectAccountCombination);
    $("#save-line").on('click', saveLine);
    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);
});

var clearLines = function() {
    $('#table-line tbody').html('');
    calculateTotal();

};

var addLine = function() {
    clearFormLine();
    $('#title-modal-line').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.add') }}');
    $('#modal-line').modal('show');
};

var editLine = function() {
    clearFormLine();

    var $tr = $(this).parent().parent();
    var indexFormLine = $tr.data('index-line');
    var lineId = $tr.find('[name="lineId[]"]').val();
    var accountCombinationId = $tr.find('[name="accountCombinationId[]"]').val();
    var accountCombinationCode = $tr.find('[name="accountCombinationCode[]"]').val();
    var accountCombinationDescription = $tr.find('[name="accountCombinationDescription[]"]').val();
    var debet = $tr.find('[name="debet[]"]').val();
    var credit = $tr.find('[name="credit[]"]').val();
    var descriptionLine = $tr.find('[name="descriptionLine[]"]').val();

    $('#indexFormLine').val(indexFormLine);
    $('#lineId').val(lineId);
    $('#accountCombinationId').val(accountCombinationId);
    $('#accountCombinationCode').val(accountCombinationCode);
    $('#accountCombinationDescription').val(accountCombinationDescription);
    $('#debet').val(debet);
    $('#credit').val(credit);
    $('#descriptionLine').val(descriptionLine);

    $('#title-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#modal-line').modal("show");
};

var clearFormLine = function() {
    $('#indexFormLine').val('');
    $('#lineId').val('');
    $('#accountCombinationId').val('');
    $('#accountCombinationCode').val('');
    $('#accountCombinationDescription').val('');
    $('#debet').val(0);
    $('#credit').val(0);
    $('#descriptionLine').val('');
};

var showLovAccountCombination = function() {
    $('#searchAccountCombination').val('');
    loadLovAccountCombination(function() {
        $('#modal-lov-account-combination').modal('show');
    });
};

var xhrAccountCombination;
var loadLovAccountCombination = function(callback) {
    if(xhrAccountCombination && xhrAccountCombination.readyState != 4){
        xhrAccountCombination.abort();
    }
    xhrAccountCombination = $.ajax({
        url: '{{ URL($url.'/get-json-account-combination') }}',
        data: {search: $('#searchAccountCombination').val()},
        success: function(data) {
            $('#table-lov-account-combination tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-account-combination tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.account_combination_code + '</td>\
                        <td>' + item.account_combination_description + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectAccountCombination = function() {
    var data = $(this).data('json');
    $('#accountCombinationId').val(data.account_combination_id);
    $('#accountCombinationCode').val(data.account_combination_code);
    $('#accountCombinationDescription').val(data.account_combination_description);

    $('#modal-lov-account-combination').modal('hide');
};

var saveLine = function() {
    var indexFormLine        = $('#indexFormLine').val();
    var accountCombinationId = $('#accountCombinationId').val();
    var debet                = currencyToInt($('#debet').val());
    var credit               = currencyToInt($('#credit').val());
    var descriptionLine      = $('#descriptionLine').val();
    var error                = false;

    if (accountCombinationId == '' || accountCombinationId <= 0) {
        $('#accountCombinationCode').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#accountCombinationCode').parent().parent().removeClass('has-error');
    }

    if ((debet <= 0 && credit <= 0) || (debet > 0 && credit > 0)) {
        $('#debet').parent().parent().addClass('has-error');
        $('#credit').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#debet').parent().parent().removeClass('has-error');
        $('#credit').parent().parent().removeClass('has-error');
    }

    if (descriptionLine == '') {
        $('#descriptionLine').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#descriptionLine').parent().parent().removeClass('has-error');
    }

    if (error) {
        return;
    }

    var lineId                        = $('#lineId').val();
    var accountCombinationCode        = $('#accountCombinationCode').val();
    var accountCombinationDescription = $('#accountCombinationDescription').val();

    var htmlTr = '<td >' + accountCombinationCode + '<hr/>' + accountCombinationDescription + '</td>\
                    <td class="text-right">' + debet.formatMoney(0) + '</td>\
                    <td class="text-right">' + credit.formatMoney(0) + '</td>\
                    <td>' + descriptionLine + '</td>\
                    <td class="text-center">\
                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>\
                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>\
                        <input type="hidden" name="lineId[]" value="' + lineId + '">\
                        <input type="hidden" name="accountCombinationId[]" value="' + accountCombinationId + '">\
                        <input type="hidden" name="accountCombinationCode[]" value="' + accountCombinationCode + '">\
                        <input type="hidden" name="accountCombinationDescription[]" value="' + accountCombinationDescription + '">\
                        <input type="hidden" name="debet[]" value="' + debet + '">\
                        <input type="hidden" name="credit[]" value="' + credit + '">\
                        <input type="hidden" name="descriptionLine[]" value="' + descriptionLine + '">\
                    </td>';

    if (indexFormLine != '') {
        $('tr[data-index-line="' + indexFormLine + '"]').html(htmlTr);
    } else {
        $('#table-line tbody').append(
            '<tr data-index-line="' + indexLine + '">' + htmlTr + '</tr>'
        );
        indexLine++;
    }

    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);

    calculateTotal();


    $('#modal-line').modal("hide");
};

var deleteLine = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var calculateTotal = function() {
        var totalDebet  = 0;
        var totalCredit = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var debet  = parseFloat($(row).find('[name="debet[]"]').val().split(',').join(''));
            var credit = parseFloat($(row).find('[name="credit[]"]').val().split(',').join(''));

            totalDebet  += debet;
            totalCredit += credit;
        });

        $('#totalDebet').val(totalDebet);
        $('#totalDebet').autoNumeric('update', {mDec: 0});
        $('#totalCredit').val(totalCredit);
        $('#totalCredit').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection
