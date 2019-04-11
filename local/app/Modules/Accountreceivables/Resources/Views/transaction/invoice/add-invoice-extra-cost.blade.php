<?php
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.invoice') }} {{ trans('accountreceivables/fields.extra-cost') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save-add-invoice-extra-cost') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="id" name="id" value="{{ $model->invoice_id }}">
                        <div class="col-sm-8 portlets">
                            <div class="form-group">
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="invoiceNumber" name="invoiceNumber" value="{{ $model->invoice_number }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="createdDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <?php $createdDate = new \DateTime($model->created_date); ?>
                                        <input type="text" id="createdDate" name="createdDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $createdDate->format('d-m-Y') }}" disabled>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <?php $resiNumber = !empty($model->resi) ? $model->resi->resi_number : ''; ?>
                            <div class="form-group {{ $errors->has('resiId') ? 'has-error' : '' }}">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="resiId" name="resiId" value="{{ count($errors) > 0 ? old('resiId') : $model->resi_header_id }}">
                                        <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->invoice_id) ? 'show-lov-resi' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('resiId'))
                                        <span class="help-block">{{ $errors->first('resiId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $coa = !empty($model->coa) ? $model->coa->coa_code.' - '.$model->coa->description : ''; ?>
                            <div class="form-group {{ $errors->has('coaId') ? 'has-error' : '' }}" id="form-group-coa">
                                <label for="coa" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="coaId" name="coaId" value="{{ count($errors) > 0 ? old('coaId') : $model->coa_id }}">
                                        <input type="text" class="form-control" id="coa" name="coa" value="{{ count($errors) > 0 ? old('coa') : $coa }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->invoice_id) ? 'show-lov-coa' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('coaId'))
                                        <span class="help-block">{{ $errors->first('coaId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                                <label for="amount" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.amount') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control currency' id="amount" name="amount" value="{{ count($errors) > 0 ? str_replace(',', '', old('amount')) : $model->amount }}" {{ !empty($model->invoice_id) ? 'readonly' : '' }}>
                                    @if($errors->has('amount'))
                                        <span class="help-block">{{ $errors->first('amount') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="description" name="description" {{ !empty($model->invoice_id) ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                        <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
<div id="modal-lov-resi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.resi') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchResi" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchResi" name="searchResi">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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

<div id="modal-lov-coa" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.account') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchCoa" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchCoa" name="searchCekGiro">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-coa" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('general-ledger/fields.account-code') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
$(document).on('ready', function(){
    $('#show-lov-resi').on('click', showLovResi);
    $('#searchResi').on('keyup', loadLovResi);
    $('#table-lov-resi tbody').on('click', 'tr', selectResi);

    $('#show-lov-coa').on('click', showLovCoa);
    $('#searchCoa').on('keyup', loadLovCoa);
    $('#table-lov-coa tbody').on('click', 'tr', selectCoa);
});

var showLovResi = function() {
    $('#searchResi').val('');
    loadLovResi(function() {
        $('#modal-lov-resi').modal('show');
    });
};

var xhrResi;
var loadLovResi = function(callback) {
    if(xhrResi && xhrResi.readyState != 4){
        xhrResi.abort();
    }
    xhrResi = $.ajax({
        url: '{{ URL($url.'/get-json-resi') }}',
        data: {search: $('#searchResi').val()},
        success: function(data) {
            $('#table-lov-resi tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-resi tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.resi_number + '<hr/>' + item.route_code + '</td>\
                        <td>' + item.customer_sender_name + '<hr/>' + item.sender_name + '</td>\
                        <td>' + item.customer_receiver_name + '<hr/>' + item.receiver_name + '</td>\
                        <td>' + item.total_coly + '</td>\
                        <td>' + item.item_name + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectResi = function() {
    var data = $(this).data('json');
    $('#resiId').val(data.resi_header_id);
    $('#resiNumber').val(data.resi_number);

    $('#modal-lov-resi').modal('hide');
};

var showLovCoa = function() {
    $('#searchCoa').val('');
    loadLovCoa(function() {
        $('#modal-lov-coa').modal('show');
    });
};

var xhrCoa;
var loadLovCoa = function(callback) {
    if(xhrCoa && xhrCoa.readyState != 4){
        xhrCoa.abort();
    }
    xhrCoa = $.ajax({
        url: '{{ URL($url.'/get-json-coa') }}',
        data: {search: $('#searchCoa').val()},
        success: function(data) {
            $('#table-lov-coa tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-coa tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.coa_code + '</td>\
                        <td>' + item.description + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectCoa = function() {
    var data = $(this).data('json');
    $('#coaId').val(data.coa_id);
    $('#coa').val(data.coa_code + ' - ' + data.description);

    $('#modal-lov-coa').modal('hide');
};
</script>
@endsection
