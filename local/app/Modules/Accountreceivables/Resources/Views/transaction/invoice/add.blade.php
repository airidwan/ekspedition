<?php
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->invoice_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabResi" data-toggle="tab">{{ trans('operational/fields.resi') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineDetails" data-toggle="tab">{{ trans('operational/fields.line-detail') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineUnits" data-toggle="tab">{{ trans('operational/fields.line-unit') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="invoiceNumber" name="invoiceNumber" value="{{ $model->invoice_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="type" name="type" value="{{ $model->type }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $customerName = $model->customer !== null ? $model->customer->customer_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('customerId') ? 'has-error' : '' }}">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="customerId" name="customerId" value="{{ count($errors) > 0 ? old('customerId') : $model->customer_id }}">
                                                <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $customerName }}" readonly>
                                                <span class="btn input-group-addon" id="{{ $model->isApproved() ? 'remove-customer' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" id="{{ $model->isApproved() ? 'show-lov-customer' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('customerId'))
                                                <span class="help-block">{{ $errors->first('customerId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billTo') ? 'has-error' : '' }}">
                                        <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billTo" name="billTo" value="{{ count($errors) > 0 ? old('billTo') : $model->bill_to }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() ? 'readonly' : '' }}>
                                            @if($errors->has('billTo'))
                                                <span class="help-block">{{ $errors->first('billTo') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billToAddress') ? 'has-error' : '' }}">
                                        <label for="billToAddress" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToAddress" name="billToAddress" value="{{ count($errors) > 0 ? old('billToAddress') : $model->bill_to_address }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() ? 'readonly' : '' }}>
                                            @if($errors->has('billToAddress'))
                                                <span class="help-block">{{ $errors->first('billToAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billToPhone') ? 'has-error' : '' }}">
                                        <label for="billToPhone" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-phone') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToPhone" name="billToPhone" value="{{ count($errors) > 0 ? old('billToPhone') : $model->bill_to_phone }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() ? 'readonly' : '' }}>
                                            @if($errors->has('billToPhone'))
                                                <span class="help-block">{{ $errors->first('billToPhone') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $isTagihan = count($errors) > 0 ? old('isTagihan') : $model->is_tagihan; ?>
                                                <input type="checkbox" id="isTagihan" name="isTagihan" value="1" {{ $isTagihan ? 'checked' : '' }} {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() ? 'disabled' : '' }}>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-invoice') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalInvoice" name="totalInvoice" value="{{ $model->amount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount1" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-1') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen1" name="discountPersen1" value="{{ count($errors) > 0 ? str_replace(',', '', old('discountPersen1')) : $model->discount_persen_1 }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() || $model->current_discount != 1 ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" disabled />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="discount1" name="discount1" value="{{ count($errors) > 0 ? str_replace(',', '', old('discount1')) : $model->discount_1 }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() || $model->current_discount != 1 ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount2" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-2') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen2" name="discountPersen2" value="{{ count($errors) > 0 ? str_replace(',', '', old('discountPersen2')) : $model->discount_persen_2 }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() || $model->current_discount != 2 ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" disabled />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="discount2" name="discount2" value="{{ count($errors) > 0 ? str_replace(',', '', old('discount2')) : $model->discount_2 }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() || $model->current_discount != 2 ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount3" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-3') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen3" name="discountPersen3" value="{{ count($errors) > 0 ? str_replace(',', '', old('discountPersen3')) : $model->discount_persen_3 }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() || $model->current_discount != 3 ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" disabled />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="discount3" name="discount3" value="{{ count($errors) > 0 ? str_replace(',', '', old('discount3')) : $model->discount_3 }}" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() || $model->current_discount != 3 ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->totalInvoice() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receipt" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="receipt" name="receipt" value="{{ $model->totalReceipt() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="remaining" name="remaining" value="{{ $model->remaining() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $model->status }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="requestApproveNote" class="col-sm-4 control-label">{{ trans('shared/common.request-approve-note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="requestApproveNote" name="requestApproveNote" {{ $model->isInprocess() || $model->isInprocessBatch() || $model->isCanceled() || $model->isClosed() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('requestApproveNote') : $model->req_approve_note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabResi">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="resiNumber" name="resiNumber" value="{{ !empty($model->resi) ? $model->resi->resi_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="doNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="doNumber" name="doNumber" value="{{ !empty($model->deliveryOrderLine->header) ? $model->deliveryOrderLine->header->delivery_order_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="pickupRequestNumber" name="pickupRequestNumber" value="{{ !empty($model->pickupRequest) ? $model->pickupRequest->pickup_request_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="resiType" class="col-sm-4 control-label">{{ trans('shared/common.type') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="resiType" name="resiType" value="{{ !empty($model->resi) ? $model->resi->type : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="pickupRequestNumber" name="pickupRequestNumber" value="{{ !empty($model->resi) ? $model->resi->payment : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="routeCode" class="col-sm-4 control-label">{{ trans('operational/fields.route') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="routeCode" name="routeCode" value="{{ !empty($model->resi->route) ? $model->resi->route->route_code : '' }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="customerSender" class="col-sm-4 control-label">{{ trans('shared/common.customer') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="customerSender" name="customerSender" value="{{ !empty($model->resi->customer) ? $model->resi->customer->customer_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sender" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="sender" name="sender" value="{{ !empty($model->resi) ? $model->resi->sender_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="senderAddress" name="senderAddress" value="{{ !empty($model->resi) ? $model->resi->sender_address : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerReceiver" class="col-sm-4 control-label">{{ trans('shared/common.customer') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="customerReceiver" name="customerReceiver" value="{{ !empty($model->resi->customerReceiver) ? $model->resi->customerReceiver->customer_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiver" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="receiver" name="receiver" value="{{ !empty($model->resi) ? $model->resi->receiver_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="receiverAddress" name="receiverAddress" value="{{ !empty($model->resi) ? $model->resi->receiver_address : '' }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLineDetails">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-detail" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.coly') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}</th>
                                                    <th>{{ trans('operational/fields.volume') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->resi->line()->whereNull('unit_id')->get() as $line)
                                                <tr>
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->coly) }} </td>
                                                    <td class="text-right"> {{ number_format($line->weight, 2) }} </td>
                                                    <td class="text-right"> {{ number_format($line->totalVolume(), 6) }} </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLineUnits">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-unit" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-unit') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->resi->line()->whereNotNull('unit_id')->get() as $line)
                                                <?php $line = TransactionResiLine::find($line->resi_line_id); ?>
                                                <tr>
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_unit) }} </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>

                                @if ($model->isApproved())
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if ($model->isApproved() && $model->current_discount <= 3)
                                <button type="submit" name="btn-request-approve" class="btn btn-sm btn-info"><i class="fa fa-share"></i> {{ trans('shared/common.request-approve') }}</button>
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
<div id="modal-lov-customer" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.customer') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchCustomer" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchCustomer" name="searchCustomer">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-customer" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.customer-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('shared/common.phone') }}</th>
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

$(document).on('ready', function(){
    /** HEADER **/
    $('#remove-customer').on('click', removeCustomer);
    $('#show-lov-customer').on('click', showLovCustomer);
    $('#searchCustomer').on('keyup', loadLovCustomer);
    $('#table-lov-customer tbody').on('click', 'tr', selectCustomer);

    $("#discount1").on('keyup', calculateDiscountPersen);
    $("#discount2").on('keyup', calculateDiscountPersen);
    $("#discount3").on('keyup', calculateDiscountPersen);

    $("#discountPersen1").on('keyup', calculateDiscount);
    $("#discountPersen2").on('keyup', calculateDiscount);
    $("#discountPersen3").on('keyup', calculateDiscount);
});

var removeCustomer = function() {
    $('#customerId').val('');
    $('#customerName').val('');
};

var showLovCustomer = function() {
    $('#searchCustomer').val('');
    loadLovCustomer(function() {
        $('#modal-lov-customer').modal('show');
    });
};

var xhrCustomer;
var loadLovCustomer = function(callback) {
    if(xhrCustomer && xhrCustomer.readyState != 4){
        xhrCustomer.abort();
    }
    xhrCustomer = $.ajax({
        url: '{{ URL($url.'/get-json-customer') }}',
        data: {search: $('#searchCustomer').val()},
        success: function(data) {
            $('#table-lov-customer tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-customer tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.customer_name + '</td>\
                        <td>' + item.address + '</td>\
                        <td>' + item.phone_number + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectCustomer = function() {
    var data = $(this).data('json');
    $('#customerId').val(data.customer_id);
    $('#customerName').val(data.customer_name);
    $('#billTo').val(data.customer_name);
    $('#billToAddress').val(data.address);
    $('#billToPhone').val(data.phone_number);
    $('#table-line tbody').html('');

    $('#modal-lov-customer').modal('hide');
};

var calculateDiscount = function() {
    var totalInvoice    = currencyToInt($('#totalInvoice').val());
    var discountPersen1 = currencyToInt($('#discountPersen1').val());
    var discountPersen2 = currencyToInt($('#discountPersen2').val());
    var discountPersen3 = currencyToInt($('#discountPersen3').val());

    var discount1 = discountPersen1 > 0 ? bulatkan(discountPersen1 / 100 * totalInvoice) : 0;
    var discount2 = discountPersen2 > 0 ? bulatkan(discountPersen2 / 100 * totalInvoice) : 0;
    var discount3 = discountPersen3 > 0 ? bulatkan(discountPersen3 / 100 * totalInvoice) : 0;

    $('#discount1').val(discount1.formatMoney(0));
    $('#discount2').val(discount2.formatMoney(0));
    $('#discount3').val(discount3.formatMoney(0));
};

var bulatkan = function(number) {
    var pembulatan = 100
    return number != 0 ? Math.floor(number/pembulatan) * pembulatan : 0;
}

var calculateDiscountPersen = function() {
    var totalInvoice    = currencyToInt($('#totalInvoice').val());
    var discount1 = currencyToInt($('#discount1').val());
    var discount2 = currencyToInt($('#discount2').val());
    var discount3 = currencyToInt($('#discount3').val());

    var discountPersen1 = discount1 > 0 ? discount1 / totalInvoice * 100 : 0;
    var discountPersen2 = discount2 > 0 ? discount2 / totalInvoice * 100 : 0;
    var discountPersen3 = discount3 > 0 ? discount3 / totalInvoice * 100 : 0;

    $('#discountPersen1').val(discountPersen1.formatMoney(0));
    $('#discountPersen2').val(discountPersen2.formatMoney(0));
    $('#discountPersen3').val(discountPersen3.formatMoney(0));
};

var calculateTotal = function() {
    var totalInvoice = currencyToInt($('#totalInvoice').val());
    var discount1 = currencyToInt($('#discount1').val());
    var discount2 = currencyToInt($('#discount2').val());
    var discount3 = currencyToInt($('#discount3').val());
    var total = totalInvoice - discount1 - discount2 - discount3;

    $('#total').val(total.formatMoney(0));
};

</script>
@endsection
