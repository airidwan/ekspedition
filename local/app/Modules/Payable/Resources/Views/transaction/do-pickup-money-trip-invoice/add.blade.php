@extends('layouts.master')

@section('title', trans('payable/menu.do-pickup-money-trip-invoice'))

@section('header')
@parent
<style type="text/css">
    #table-lov-driver tbody tr{
        cursor: pointer;
    }
    #table-lov-do tbody tr{
        cursor: pointer;
    }
    #table-lov-pickup tbody tr{
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
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.do-pickup-money-trip-invoice') }}</h2>
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
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                        <option value="">{{ trans('shared/common.please-select') }} {{ trans('shared/common.type') }}</option>
                                        <?php $typeId = count($errors) > 0 ? old('type') : $model->type_id ?>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type->type_id }}" {{ $type->type_id == $typeId ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
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
                                $line         = $model->lineOne;
                                $amount       = !empty($line) ? $line->amount : '';
                                $do           = !empty($line) ? $line->deliveryOrder : null;
                                $doId         = !empty($do) ? $do->delivery_order_header_id : '';
                                $doNumber     = !empty($do) ? $do->delivery_order_number : '';
                                $pickup       = !empty($line) ? $line->pickup : null;
                                $pickupId     = !empty($pickup) ? $pickup->pickup_form_header_id : null;
                                $pickupNumber = !empty($pickup) ? $pickup->pickup_form_number : null;
                            ?>
                            <div id ="formDo" class="form-group {{ $errors->has('doId') ? 'has-error' : '' }}">
                                <label for="doId" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-order') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="doId" name="doId" value="{{ count($errors) > 0 ? old('doId') : $doId }}" readonly>
                                        <input type="text" class="form-control" id="doNumber" name="doNumber" value="{{ count($errors) > 0 ? old('doNumber') : $doNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-do' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('doId'))
                                    <span class="help-block">{{ $errors->first('doId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div id ="formPickup" class="form-group {{ $errors->has('pickupId') ? 'has-error' : '' }}">
                                <label for="pickupId" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-form') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="pickupId" name="pickupId" value="{{ count($errors) > 0 ? old('pickupId') : $pickupId }}" readonly>
                                        <input type="text" class="form-control" id="pickupNumber" name="pickupNumber" value="{{ count($errors) > 0 ? old('pickupNumber') : $pickupNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-pickup' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('pickupId'))
                                    <span class="help-block">{{ $errors->first('pickupId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('totalAmount') ? 'has-error' : '' }}">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.amount') }}  <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $amount }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
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
                                    <th>{{ trans('operational/fields.nickname') }}</th>
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

<div id="modal-lov-pickup" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.pickup') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchPickup" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchPickup" name="searchPickup">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-pickup" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.pickup-number') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
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

<div id="modal-lov-do" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.delivery-order') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchDo" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchDo" name="searchDo">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-do" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.do-number') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
                                    <th>{{ trans('operational/fields.driver-assistant') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('shared/common.note') }}</th>
                                    <th>{{ trans('operational/fields.money-trip') }}</th>
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

        $('#show-lov-do').on('click', showLovDo);
        $('#searchDo').on('keyup', loadLovDo);
        $('#table-lov-do tbody').on('click', 'tr', selectDo);

        $('#show-lov-pickup').on('click', showLovPickup);
        $('#searchPickup').on('keyup', loadLovPickup);
        $('#table-lov-pickup tbody').on('click', 'tr', selectPickup);

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
                            <td>' + item.driver_nickname + '</td>\
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

        clearFormDOPickup();
        enableModal();
        $('#show-lov-do').removeClass('disabled');
        $('#show-lov-pickup').removeClass('disabled');

        $('#modal-lov-driver').modal('hide');
    };

    var showLovDo = function() {
        $('#searchDo').val('');
        loadLovDo(function() {
            $('#modal-lov-do').modal('show');
        });
    };

    var xhrDo;
    var loadLovDo = function(callback) {
        if(xhrDo && xhrDo.readyState != 4){
            xhrDo.abort();
        }
        xhrDo = $.ajax({
            url: '{{ URL($url.'/get-json-do') }}',
            data: {search: $('#searchDo').val(), driverId: $('#driverId').val()},
            success: function(data) {
                $('#table-lov-do tbody').html('');
                data.forEach(function(item) {
                    var policeNumber    = item.police_number || '';
                    var driverAssistant = item.driver_assistant_name || '';
                    $('#table-lov-do tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.delivery_order_number + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + driverAssistant + '</td>\
                            <td>' + policeNumber + '</td>\
                            <td>' + item.created_date + '</td>\
                            <td>' + item.note + '</td>\
                            <td align="right">' + parseInt(item.money_trip).formatMoney(0) + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectDo = function() {
        var data = $(this).data('json');
        $('#doId').val(data.delivery_order_header_id);
        $('#doNumber').val(data.delivery_order_number);
        $('#totalAmount').val(parseInt(data.money_trip).formatMoney(0));
        $('#table-line tbody').html('');

        $('#modal-lov-do').modal('hide');
    };

    var showLovPickup = function() {
        $('#searchPickup').val('');
        loadLovPickup(function() {
            $('#modal-lov-pickup').modal('show');
        });
    };

    var xhrPickup;
    var loadLovPickup = function(callback) {
        if(xhrPickup && xhrPickup.readyState != 4){
            xhrPickup.abort();
        }
        xhrPickup = $.ajax({
            url: '{{ URL($url.'/get-json-pickup') }}',
            data: {search: $('#searchPickup').val(), driverId: $('#driverId').val()},
            success: function(data) {
                $('#table-lov-pickup tbody').html('');
                data.forEach(function(item) {
                    var policeNumber = item.police_number || '';
                    $('#table-lov-pickup tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.pickup_form_number + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + policeNumber + '</td>\
                            <td>' + item.created_date + '</td>\
                            <td>' + item.note + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectPickup = function() {
        var data = $(this).data('json');
        $('#pickupId').val(data.pickup_form_header_id);
        $('#pickupNumber').val(data.pickup_form_number);
        $('#table-line tbody').html('');

        $('#modal-lov-pickup').modal('hide');
    };

    var clearForm = function(){
        $('#driverId').val('');
        $('#driverCode').val('');
        $('#driverName').val('');
        $('#driverAddress').val('');
        $('#doId').val('');
        $('#doNumber').val('');
        $('#pickupId').val('');
        $('#pickupNumber').val('');
        $('#totalAmount').val('');
    };

    var clearFormDOPickup = function(){
        $('#doId').val('');
        $('#doNumber').val('');
        $('#pickupId').val('');
        $('#pickupNumber').val('');
        $('#totalAmount').val('');
    };

    var enableModal = function(){
        $('#show-lov-do').addClass('disabled');
        $('#show-lov-pickup').addClass('disabled');
        $('#formPickup').addClass('hidden');
        $('#formDo').addClass('hidden');

        if ($('#type').val() == {{ InvoiceHeader::DO_MONEY_TRIP }}) { // Kas Bon Employee
            $('#formDo').removeClass('hidden');
            $('#formDo').removeClass('disabled');
            if ($('#driverId').val() != '') {
                $('#show-lov-do').removeClass('disabled');
            }
        }
        else if($('#type').val() == {{ InvoiceHeader::PICKUP_MONEY_TRIP }}) { // Kas Bon Driver
            $('#formPickup').removeClass('hidden');
            $('#formPickup').removeClass('disabled');
            if ($('#driverId').val() != '') {
                $('#show-lov-pickup').removeClass('disabled');
            }
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
