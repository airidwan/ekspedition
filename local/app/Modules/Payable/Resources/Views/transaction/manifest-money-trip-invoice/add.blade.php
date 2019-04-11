@extends('layouts.master')

@section('title', trans('payable/menu.manifest-money-trip-invoice'))

@section('header')
@parent
<style type="text/css">
    #table-lov-driver tbody tr{
        cursor: pointer;
    }
    #table-lov-manifest tbody tr{
        cursor: pointer;
    }
</style>
@endsection

 <?php use App\Modules\Payable\Model\Transaction\InvoiceHeader; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.manifest-money-trip-invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->header_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber"  value="{{ !empty($model->invoice_number) ? $model->invoice_number : '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="status" name="status"  value="{{ !empty($model->status) ? $model->status : '' }}" readonly>
                                </div>
                            </div>
                            <?php 
                            $driver     = $model->driver;
                            $driverId   = !empty($driver) ? $driver->driver_id : '';
                            $driverCode = !empty($driver) ? $driver->driver_code : '';
                            $driverName = !empty($driver) ? $driver->driver_name : '';
                            $driverAddress = !empty($driver) ? $driver->address : '';
                            ?>
                            <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                <label for="driverId" class="col-sm-4 control-label">{{ trans('operational/fields.driver-code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $driverId }}" readonly>
                                        <input type="text" class="form-control" id="driverCode" name="driverCode" value="{{ count($errors) > 0 ? old('driverCode') : $driverCode }}" readonly>
                                        <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-driver' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('driverId'))
                                    <span class="help-block">{{ $errors->first('driverId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-name') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                    @if($errors->has('driverName'))
                                    <span class="help-block">{{ $errors->first('driverName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('driverAddress') ? 'has-error' : '' }}">
                                <label for="driverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.driver-address') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverAddress" name="driverAddress" value="{{ count($errors) > 0 ? old('driverAddress') : $driverAddress }}" readonly>
                                    @if($errors->has('driverAddress'))
                                    <span class="help-block">{{ $errors->first('driverAddress') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                                $manifest         = $model->manifest;
                                $manifestHeaderId = !empty($manifest) ? $manifest->manifest_header_id : '';                             
                                $manifestNumber   = !empty($manifest) ? $manifest->manifest_number : '';                                
                                $line             = $model->lineOne;
                                $amount           = !empty($line) ? $line->amount : '';

                            ?>
                            <div id ="formManifest" class="form-group {{ $errors->has('manifestHeaderId') ? 'has-error' : '' }}">
                                <label for="manifestHeaderId" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="manifestHeaderId" name="manifestHeaderId" value="{{ count($errors) > 0 ? old('manifestHeaderId') : $manifestHeaderId }}" readonly>
                                        <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" value="{{ count($errors) > 0 ? old('manifestNumber') : $manifestNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-manifest' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('manifestHeaderId'))
                                    <span class="help-block">{{ $errors->first('manifestHeaderId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('totalAmount') ? 'has-error' : '' }}">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.amount') }}  <span class="required">*</span></label>
                                <div class="col-sm-8">
                                <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $amount }}" readonly>
                                    @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('totalAmount') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                $taxLine = !empty($line) ? $line->tax : '';
                            ?>
                            <div class="form-group {{ $errors->has('tax') ? 'has-error' : '' }}">
                                <label for="tax" class="col-sm-4 control-label">{{ trans('payable/fields.tax') }} </label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="tax" name="tax" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                        <option value="">{{ trans('shared/common.please-select') }} {{ trans('payable/fields.tax') }}</option>
                                        <?php $tax = count($errors) > 0 ? old('tax') : $taxLine ;?>
                                        @foreach($optionTax as $row)
                                        <option value="{{ $row }}" {{ $row== $tax ? 'selected' : '' }}>{{ $row }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('tax'))
                                    <span class="help-block">{{ $errors->first('tax') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                $fixAmount = !empty($line) ? $line->amount + ($line->tax / 100 * $line->amount) : '';
                            ?>
                            <div class="form-group {{ $errors->has('fixAmount') ? 'has-error' : '' }}">
                                <label for="fixAmount" class="col-sm-4 control-label">{{ trans('payable/fields.total-invoice') }}  <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="fixAmount" name="fixAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('fixAmount')) : $fixAmount }}" readonly>
                                    @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('fixAmount') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="description" name="description" rows="3" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}
                                </a>
                                @if($model->status == InvoiceHeader::APPROVED || $model->status == InvoiceHeader::CLOSED)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(Gate::check('access', [$resource, 'insert']) && $model->status == InvoiceHeader::INCOMPLETE)
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.save') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->status == InvoiceHeader::INCOMPLETE)
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.approve') }}
                                </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-lov-driver" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.driver') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchDriver" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchDriver" name="searchDriver">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.driver-code') }}</th>
                                    <th>{{ trans('operational/fields.driver-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('shared/common.phone') }}</th>
                                    <th>{{ trans('shared/common.position') }}</th>
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
<div id="modal-lov-manifest" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.manifest') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchManifest" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchManifest" name="searchManifest">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-manifest" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
                                    <th>{{ trans('operational/fields.driver-assistant') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('shared/common.note') }}</th>
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
@parent
<script type="text/javascript">
    $(document).on('ready', function() {
        enableModal();
        $("#type").on('change', function(){
            clearForm();
            enableModal();
        });

        $('#show-lov-driver').on('click', showLovDriver);
        $('#searchDriver').on('keyup', loadLovDriver);
        $('#table-lov-driver tbody').on('click', 'tr', selectDriver);

        $('#show-lov-manifest').on('click', showLovManifest);
        $('#searchManifest').on('keyup', loadLovManifest);
        $('#table-lov-manifest tbody').on('click', 'tr', selectManifest);

        $("#totalAmount").on('keyup', calculateFixAmount);
         $("#tax").on('change', calculateFixAmount);
    
    });

    var showLovDriver = function() {
        $('#searchDriver').val('');
        loadLovDriver(function() {
            $('#modal-lov-driver').modal('show');
        });
    };

    var xhrDriver;
    var loadLovDriver = function(callback) {
        if(xhrDriver && xhrDriver.readyState != 4){
            xhrDriver.abort();
        }
        xhrDriver = $.ajax({
            url: '{{ URL($url.'/get-json-driver') }}',
            data: {search: $('#searchDriver').val()},
            success: function(data) {
                $('#table-lov-driver tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-driver tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.driver_code + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + item.address + '</td>\
                            <td>' + item.phone_number + '</td>\
                            <td>' + item.position + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectDriver = function() {

        var data = $(this).data('json');
        $('#driverId').val(data.driver_id);
        $('#driverCode').val(data.driver_code);
        $('#driverName').val(data.driver_name);
        $('#driverAddress').val(data.address);
        $('#table-line tbody').html('');

        clearFormManifest();
        enableModal();
        $('#show-lov-manifest').removeClass('disabled');
        $('#modal-lov-driver').modal('hide');
    };

    var showLovManifest = function() {
        $('#searchManifest').val('');
        loadLovManifest(function() {
            $('#modal-lov-manifest').modal('show');
        });
    };

    var xhrManifest;
    var loadLovManifest = function(callback) {
        if(xhrManifest && xhrManifest.readyState != 4){
            xhrManifest.abort();
        }
        xhrManifest = $.ajax({
            url: '{{ URL($url.'/get-json-manifest') }}',
            data: {search: $('#searchManifest').val(), driverId: $('#driverId').val()},
            success: function(data) {
                $('#table-lov-manifest tbody').html('');
                data.forEach(function(item) {
                    var assistant = item.driver_assistant_name || '';
                    $('#table-lov-manifest tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.manifest_number + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + assistant + '</td>\
                            <td>' + item.police_number + '</td>\
                            <td>' + item.created_date + '</td>\
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

    var selectManifest = function() {
        var data = $(this).data('json');
        $('#manifestHeaderId').val(data.manifest_header_id);
        $('#manifestNumber').val(data.manifest_number);
        $('#totalAmount').val( parseInt(data.money_trip).formatMoney(0));
        $('#table-line tbody').html('');

        calculateFixAmount();

        $('#modal-lov-manifest').modal('hide');
    };

    var clearForm = function(){
        $('#driverId').val('');
        $('#driverCode').val('');
        $('#driverName').val('');
        $('#driverAddress').val('');
        $('#manifestHeaderId').val('');
        $('#manifestNumber').val('');
    };

    var clearFormManifest = function(){
        $('#manifestHeaderId').val('');
        $('#manifestNumber').val('');
    };

    var enableModal = function(){
        $('#show-lov-manifest').addClass('disabled');
        $('#formManifest').removeClass('hidden');
        $('#formManifest').removeClass('disabled');
        if ($('#driverId').val() != '') {
            $('#show-lov-manifest').removeClass('disabled');
        }
    };

    var calculateFixAmount = function(){
        var amount    = currencyToInt($('#totalAmount').val());
        var tax       = currencyToInt($('#tax').val());
        var fixAmount = amount + (tax / 100 * amount);
        
        $("#fixAmount").val(fixAmount);
        $('#fixAmount').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection
