@extends('layouts.master')

@section('title', trans('payable/menu.payment'))

 <?php use App\Modules\Payable\Model\Transaction\InvoiceHeader; ?>
 <?php use App\Modules\Payable\Model\Transaction\Payment; ?>

@section('header')
@parent
<style type="text/css">
    #table-lov-invoice tbody tr{
        cursor: pointer;
    }
    #table-lov-bank tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.payment') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->payment_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="paymentNumber" class="col-sm-4 control-label">{{ trans('payable/fields.payment-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="paymentNumber" name="paymentNumber"  value="{{ !empty($model->payment_number) ? $model->payment_number : '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="status" name="status"  value="{{ !empty($model->status) ? $model->status : '' }}" readonly>
                                </div>
                            </div>
                            <?php 
                            // var_dump($invoice);exit();
                            $type          = !empty($invoice) ? $invoice->type : null;   
                            $typeId        = !empty($type) ? $type->type_id : '';   
                            $typeName      = !empty($type) ? $type->type_name : '';   
                            $invoiceId     = !empty($invoice) ? $invoice->header_id : ''; 
                            $invoiceNumber = !empty($invoice) ? $invoice->invoice_number : '';
                            $invoiceDescription = !empty($invoice) ? $invoice->description : '';
                            $typeInvoice   = !empty($invoice) ? $invoice->type_id : '';

                            if (in_array($typeInvoice, InvoiceHeader::VENDOR_TYPE)) {
                                $vendor        = $invoice->vendor;
                                $vendorId      = !empty($vendor) ? $vendor->vendor_id : '';
                                $vendorCode    = !empty($vendor) ? $vendor->vendor_code : '';
                                $vendorName    = !empty($vendor) ? $vendor->vendor_name : '';
                                $vendorAddress = !empty($vendor) ? $vendor->address : '';
                            }elseif (in_array($typeInvoice, InvoiceHeader::DRIVER_TYPE)) {
                                $driver        = $invoice->driver;
                                $vendorId      = !empty($driver) ? $driver->driver_id : '';
                                $vendorCode    = !empty($driver) ? $driver->driver_code : '';
                                $vendorName    = !empty($driver) ? $driver->driver_name : '';
                                $vendorAddress = !empty($driver) ? $driver->address : '';
                            }else{
                                $vendorId      = '';
                                $vendorCode    = '';
                                $vendorName    = '';
                                $vendorAddress = '';
                                $vendorPhone   = '';
                            }
                            ?>
                            <div class="form-group {{ $errors->has('invoiceId') ? 'has-error' : '' }}">
                                <label for="vendinvoiceIdorName" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="invoiceId" name="invoiceId" value="{{ count($errors) > 0 ? old('invoiceId') : $invoiceId }}" readonly>
                                        <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ count($errors) > 0 ? old('invoiceNumber') : $invoiceNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-invoice' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('invoiceId'))
                                    <span class="help-block">{{ $errors->first('invoiceId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="invoiceDescription" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="invoiceDescription" name="invoiceDescription" value="{{ count($errors) > 0 ? old('invoiceDescription') : $invoiceDescription }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="typeName" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <input type="hidden" class="form-control " id="typeId" name="typeId" value="{{ count($errors) > 0 ? old('typeId') : $typeId }}" readonly>
                                    <input type="text" class="form-control " id="typeName" name="typeName" value="{{ count($errors) > 0 ? old('typeName') : $typeName }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorCode" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="hidden" class="form-control " id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $vendorId }}" readonly>
                                    <input type="text" class="form-control " id="vendorCode" name="vendorCode" value="{{ count($errors) > 0 ? old('vendorCode') : $vendorCode }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorName" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendorName" name="vendorName" value="{{ count($errors) > 0 ? old('vendorName') : $vendorName }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendorAddress" name="vendorAddress" value="{{ count($errors) > 0 ? old('vendorAddress') : $vendorAddress }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php $paymentMethod = count($errors) > 0 ? old('paymentMethod') : $model->payment_method; ?>
                            <div class="form-group">
                                <label for="paymentMethod" class="col-sm-4 control-label">{{ trans('payable/fields.payment-method') }}</label>
                                <div class="col-sm-8">
                                    <select id="paymentMethod" name="paymentMethod" class="form-control" {{ !$model->isIncomplete() ? 'readonly' : '' }}>
                                        @foreach($optionPaymentMethod as $option)
                                            <option value="{{ $option }}" {{ $option == $paymentMethod ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $bankName = old('bankName');
                            } else {
                                $bankName = !empty($model->bank) ? $model->bank->bank_name . ' - ' . $model->bank->account_number : '';
                            }
                            ?>
                            <div class="form-group {{ $errors->has('bankId') ? 'has-error' : '' }} {{ $paymentMethod != Payment::TRANSFER ? 'hidden' : '' }}" id="form-group-bank">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="bankId" name="bankId" value="{{ count($errors) > 0 ? old('bankId') : $model->bank_id }}">
                                        <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $bankName }}" readonly>
                                        <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-bank' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('bankId'))
                                        <span class="help-block">{{ $errors->first('bankId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                                if (empty($model->payment_id)) {
                                    $totalAmount   = !empty($invoice) ? $invoice->getTotalRemainAmount() : '';
                                    $totalInterest = !empty($invoice) ? $invoice->getTotalRemainInterest() : '';
                                }else{
                                    $totalAmount   = $model->total_amount;
                                    $totalInterest = $model->total_interest;
                                }
                            ?>
                            <div class="form-group {{ $errors->has('totalAmount') ? 'has-error' : '' }}">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.total-amount') }}  <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',','', old('totalAmount')) : $totalAmount }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                    @if($errors->has('totalAmount'))
                                        <span class="help-block">{{ $errors->first('totalAmount') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('totalInterest') ? 'has-error' : '' }}">
                                <label for="totalInterest" class="col-sm-4 control-label">{{ trans('payable/fields.total-interest') }}  </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalInterest" name="totalInterest" value="{{ count($errors) > 0 ? str_replace(',','', old('totalInterest')) : $totalInterest }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                    @if($errors->has('totalInterest'))
                                        <span class="help-block">{{ $errors->first('totalInterest') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                            $totalPayment = $model->total_amount + $model->total_interest;
                            ?>

                            <div class="form-group {{ $errors->has('totalPayment') ? 'has-error' : '' }}">
                                <label for="totalPayment" class="col-sm-4 control-label">{{ trans('payable/fields.total-payment') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalPayment" name="totalPayment" value="{{ count($errors) > 0 ? str_replace(',','', old('totalPayment')) : $totalPayment  }}" readonly >
                                    @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('totalPayment') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            $note = !empty($model->note) ? $model->note : $invoiceDescription;
                            ?>
                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="3" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>{{ count($errors) > 0 ? old('note') : $note }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
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
                                @if($model->status == Payment::APPROVED)
                                <a href="{{ URL($url.'/print-pdf/'.$model->payment_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(Gate::check('access', [$resource, 'insert']) && $model->status == Payment::INCOMPLETE)
                                <button type="submit" class="btn btn-sm btn-primary btn-submit">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.save') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->status == Payment::INCOMPLETE)
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info btn-submit">
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
<div id="modal-lov-invoice" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.invoice') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchInvoice" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchInvoice" name="searchInvoice">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-invoice" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('payable/fields.invoice-number') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('payable/fields.vendor-code') }}</th>
                                    <th>{{ trans('payable/fields.vendor-name') }}</th>
                                    <th>{{ trans('payable/fields.total-amount') }}</th>
                                    <th>{{ trans('payable/fields.total-interest') }}</th>
                                    <th>{{ trans('payable/fields.total-tax') }}</th>
                                    <th>{{ trans('payable/fields.total-invoice') }}</th>
                                    <th>{{ trans('payable/fields.total-remain-interest') }}</th>
                                    <th>{{ trans('payable/fields.total-remain-amount') }}</th>
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

<div id="modal-lov-bank" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.bank') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchBank" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchBank" name="searchBank">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-bank" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('general-ledger/fields.bank-name') }}</th>
                                    <th>{{ trans('general-ledger/fields.account-number') }}</th>
                                    <th>{{ trans('general-ledger/fields.account-name') }}</th>
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
@parent
<script type="text/javascript">

    $(document).on('ready', function(){
        $('#show-lov-invoice').on('click', showLovInvoice);
        $('#searchInvoice').on('keyup', loadLovInvoice);
        $('#table-lov-invoice tbody').on('click', 'tr', selectInvoice);

        $('#show-lov-bank').on('click', showLovBank);
        $('#searchBank').on('keyup', loadLovBank);
        $('#table-lov-bank tbody').on('click', 'tr', selectBank);

        $('#totalAmount').on('keyup', calculateTotalPayment);
        $('#totalInterest').on('keyup', calculateTotalPayment);

        $('#paymentMethod').on('change', changePaymentMethod);

        disableInterest();

        $('.btn-submit').on('click', function(event){
            $('.btn-submit').hide();
            $('#add-form').submit();
        });
    });

    var changePaymentMethod = function() {
        $('#bankId').val('');
        $('#bankName').val('');

        if ($('#paymentMethod').val() == '{{ Payment::CASH }}') {
            $('#form-group-bank').addClass('hidden');
        } else if ($('#paymentMethod').val() == '{{ Payment::TRANSFER }}') {
            $('#form-group-bank').removeClass('hidden');
        }
    };

    var showLovInvoice = function() {
        $('#searchInvoice').val('');
        loadLovInvoice(function() {
            $('#modal-lov-invoice').modal('show');
        });
    };

    var xhrInvoice;
    var loadLovInvoice = function(callback) {
        if(xhrInvoice && xhrInvoice.readyState != 4){
            xhrInvoice.abort();
        }
        xhrInvoice = $.ajax({
            url: '{{ URL($url.'/get-json-invoice') }}',
            data: {search: $('#searchInvoice').val()},
            success: function(data) {
                $('#table-lov-invoice tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-invoice tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.invoice_number + '</td>\
                            <td>' + item.type_name + '</td>\
                            <td>' + item.vendor_code + '</td>\
                            <td>' + item.vendor_name + '</td>\
                            <td class="text-right">' + item.total_amount.formatMoney(0) + '</td>\
                            <td class="text-right">' + item.total_interest.formatMoney(0) + '</td>\
                            <td class="text-right">' + item.total_tax.formatMoney(0) + '</td>\
                            <td class="text-right">' + item.total_invoice.formatMoney(0) + '</td>\
                            <td class="text-right">' + item.total_remain_interest.formatMoney(0) + '</td>\
                            <td class="text-right">' + item.total_remain_amount.formatMoney(0) + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectInvoice = function() {
        var data = $(this).data('json');
        $('#typeId').val(data.type_id);
        $('#typeName').val(data.type_name);
        $('#invoiceId').val(data.header_id);
        $('#invoiceNumber').val(data.invoice_number);
        $('#invoiceDescription').val(data.description);
        $('#vendorId').val(data.vendor_id);
        $('#vendorCode').val(data.vendor_code);
        $('#vendorName').val(data.vendor_name);
        $('#vendorAddress').val(data.address);
        $('#totalAmount').val(data.total_remain_amount.formatMoney(0));
        $('#totalInterest').val(data.total_remain_interest.formatMoney(0));
        $('#note').val(data.description);
        $('#table-line tbody').html('');

        disableInterest();
        calculateTotalPayment()

        $('#modal-lov-invoice').modal('hide');
    };

    var showLovBank = function() {
        $('#searchBank').val('');
        loadLovBank(function() {
            $('#modal-lov-bank').modal('show');
        });
    };

    var xhrBank;
    var loadLovBank = function(callback) {
        if(xhrBank && xhrBank.readyState != 4){
            xhrBank.abort();
        }
        xhrBank = $.ajax({
            url: '{{ URL($url.'/get-json-bank') }}',
            data: {search: $('#searchBank').val()},
            success: function(data) {
                $('#table-lov-bank tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-bank tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.bank_name + '</td>\
                            <td>' + item.account_number + '</td>\
                            <td>' + item.account_name + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectBank = function() {
        var data = $(this).data('json');
        $('#bankId').val(data.bank_id);
        $('#bankName').val(data.bank_name + ' - ' + data.account_number);

        $('#modal-lov-bank').modal('hide');
    };

    var disableInterest = function() {
        if ($('#typeId').val() == {{ InvoiceHeader::PURCHASE_ORDER_CREDIT }} && $('#status').val() == '{{ InvoiceHeader::INCOMPLETE }}' ) {
            $('#totalInterest').removeAttr('disabled', 'disabled');
        }else{
            $('#totalInterest').attr('disabled', 'disabled');
        }
    };

    var calculateTotalPayment = function(){
        totalAmount   = currencyToInt($('#totalAmount').val());
        totalInterest = currencyToInt($('#totalInterest').val());
        totalPayment  = totalAmount + totalInterest;

        $('#totalPayment').val(totalPayment.formatMoney(0));
    };
</script>
@endsection
