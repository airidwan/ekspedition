<?php
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Accountreceivables\Http\Controllers\Transaction\ReceiptController;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receipt'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.receipt') }}</h2>
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
                                <div class="col-sm-8 portlets">
                                    <div class="form-group">
                                        <label for="receiptDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $receiptDate = new \DateTime(); ?>
                                                <input type="text" id="receiptDate" name="receiptDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $receiptDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('personName') ? 'has-error' : '' }}">
                                        <label for="personName" class="col-sm-4 control-label">
                                            {{ trans('shared/common.person-name') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="personName" name="personName" value="{{ old('personName') }}" />
                                            @if($errors->has('personName'))
                                                <span class="help-block">{{ $errors->first('personName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <select id="type" name="type" class="form-control">
                                                @foreach($optionType as $option)
                                                    <option value="{{ $option }}" {{ $option == old('type') ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('batchInvoiceId') ? 'has-error' : '' }} {{ old('type') != Receipt::BATCH ? 'hidden' : '' }}" id="form-group-batch-invoice">
                                        <label for="batchInvoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.batch-invoice-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="batchInvoiceId" name="batchInvoiceId" value="{{ old('batchInvoiceId') }}">
                                                <input type="text" class="form-control" id="batchInvoiceNumber" name="batchInvoiceNumber" value="{{ old('batchInvoiceNumber') }}" readonly>
                                                <span class="btn input-group-addon" id="show-lov-batch-invoice"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('batchInvoiceId'))
                                                <span class="help-block">{{ $errors->first('batchInvoiceId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('cekGiroHeaderId') ? 'has-error' : '' }} {{ old('type') != Receipt::CEK_GIRO ? 'hidden' : '' }}" id="form-group-cek-giro">
                                        <label for="cekGiroNumber" class="col-sm-4 control-label">
                                            {{ trans('accountreceivables/fields.cek-giro-number') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="cekGiroHeaderId" name="cekGiroHeaderId" value="{{ old('cekGiroHeaderId') }}">
                                                <input type="text" class="form-control" id="cekGiroNumber" name="cekGiroNumber" value="{{ old('cekGiroNumber') }}" readonly>
                                                <span class="btn input-group-addon" id="show-lov-cek-giro"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('cekGiroHeaderId'))
                                                <span class="help-block">{{ $errors->first('cekGiroHeaderId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php $receiptMethod = old('receiptMethod') ?>
                                    <div class="form-group">
                                        <label for="receiptMethod" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-method') }}</label>
                                        <div class="col-sm-8">
                                            <select id="receiptMethod" name="receiptMethod" class="form-control">
                                                @foreach($optionReceiptMethod as $option)
                                                    <option value="{{ $option }}" {{ $option == $receiptMethod ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    ?>
                                    <div class="form-group {{ $errors->has('bankId') ? 'has-error' : '' }} {{ $receiptMethod != Receipt::TRANSFER ? 'hidden' : '' }}" id="form-group-bank">
                                        <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="bankId" name="bankId" value="{{ old('bankId') }}">
                                                <input type="text" class="form-control" id="bankName" name="bankName" value="{{ old('bankName') }}" readonly>
                                                <span class="btn input-group-addon" id="show-lov-bank"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('bankId'))
                                                <span class="help-block">{{ $errors->first('bankId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalReceipt" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-receipt') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalReceipt" name="totalReceipt" value="{{ str_replace(',', '', old('totalReceipt')) }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                    @if(old('type') != Receipt::BATCH && old('type') != Receipt::CEK_GIRO)
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action" id="toolbar-action-line">
                                                    <a id="add-line" class="btn btn-sm btn-primary">
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
                                                    <th>
                                                        {{ trans('accountreceivables/fields.invoice-number') }}<hr/>
                                                        {{ trans('shared/common.type') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.resi-number') }}<hr/>
                                                        {{ trans('operational/fields.route') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.customer') }}<hr/>
                                                        {{ trans('operational/fields.sender') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.customer') }}<hr/>
                                                        {{ trans('operational/fields.receiver') }}
                                                    </th>
                                                    <th>{{ trans('operational/fields.payment') }}</th>
                                                    <th>{{ trans('accountreceivables/fields.receipt') }}</th>
                                                    <th width="60px" class="{{ old('type') == Receipt::BATCH || old('type') == Receipt::CEK_GIRO ? 'hidden' : '' }}" id="th-action-line">
                                                        {{ trans('shared/common.action') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $indexLine = 0; ?>
                                                @if(count($errors) > 0)
                                                    @for($i = 0; $i < count(old('invoiceId', [])); $i++)
                                                        <tr data-index-line="{{ $indexLine }}">
                                                            <td > {{ old('invoiceNumber')[$i] }}<hr/>{{ old('invoiceType')[$i] }} </td>
                                                            <td > {{ old('resiNumber')[$i] }}<hr/>{{ old('route')[$i] }} </td>
                                                            <td > {{ old('customerSender')[$i] }}<hr/>{{ old('sender')[$i] }}</td>
                                                            <td > {{ old('customerReceiver')[$i] }}<hr/>{{ old('receiver')[$i] }}</td>
                                                            <td > {{ old('payment')[$i] }} </td>
                                                            <td class="text-right"> {{ number_format(intval(old('receipt')[$i])) }} </td>

                                                            <td class="text-center td-action-line {{ old('type') == Receipt::BATCH || old('type') == Receipt::CEK_GIRO ? 'hidden' : '' }}">
                                                                <a data-toggle="tooltip" class="btn btn-warning btn-xs edit-line" ><i class="fa fa-pencil"></i></a>
                                                                <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                                <input type="hidden" name="invoiceId[]" value="{{ old('invoiceId')[$i] }}">
                                                                <input type="hidden" name="invoiceNumber[]" value="{{ old('invoiceNumber')[$i] }}">
                                                                <input type="hidden" name="invoiceType[]" value="{{ old('invoiceType')[$i] }}">
                                                                <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                                <input type="hidden" name="payment[]" value="{{ old('payment')[$i] }}">
                                                                <input type="hidden" name="route[]" value="{{ old('route')[$i] }}">
                                                                <input type="hidden" name="customerSender[]" value="{{ old('customerSender')[$i] }}">
                                                                <input type="hidden" name="sender[]" value="{{ old('sender')[$i] }}">
                                                                <input type="hidden" name="customerReceiver[]" value="{{ old('customerReceiver')[$i] }}">
                                                                <input type="hidden" name="receiver[]" value="{{ old('receiver')[$i] }}">
                                                                <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                                <input type="hidden" name="remainingFix[]" value="{{ old('remainingFix')[$i] }}">
                                                                <input type="hidden" name="remaining[]" value="{{ old('remaining')[$i] }}">
                                                                <input type="hidden" name="receipt[]" value="{{ old('receipt')[$i] }}">
                                                            </td>
                                                        </tr>
                                                        <?php $indexLine++; ?>
                                                    @endfor
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

                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary btn-submit"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
<div id="modal-lov-batch-invoice" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('accountreceivables/fields.batch-invoice') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchBatchInvoice" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchBatchInvoice" name="searchBatchInvoice">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-batch-invoice" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('accountreceivables/fields.batch-invoice-number') }}</th>
                                    <th>{{ trans('accountreceivables/fields.invoice-number') }}<hr/>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                    <th>{{ trans('accountreceivables/fields.amount') }}</th>
                                    <th>{{ trans('accountreceivables/fields.remaining') }}</th>
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

<div id="modal-lov-cek-giro" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('accountreceivables/fields.cek-giro') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchCekGiro" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchCekGiro" name="searchCekGiro">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-cek-giro" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('accountreceivables/fields.type') }}</th>
                                    <th>
                                        {{ trans('accountreceivables/fields.cek-giro-number') }}<hr/>
                                        {{ trans('accountreceivables/fields.cek-giro-account-number') }}
                                    </th>
                                    <th>
                                        {{ trans('shared/common.customer') }}<hr/>
                                        {{ trans('shared/common.person-name') }}
                                    </th>
                                    <th>{{ trans('accountreceivables/fields.bank-name') }}</th>
                                    <th>{{ trans('shared/common.date') }}<hr/>{{ trans('accountreceivables/fields.clearing-date') }}</th>
                                    <th>{{ trans('accountreceivables/fields.total') }}</th>
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
                            <form  role="form" id="modal-form" class="form-horizontal" method="post">
                                <input type="hidden" name="indexFormLine" id="indexFormLine" value="">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceNumber" class="col-sm-4 control-label">
                                            {{ trans('accountreceivables/fields.invoice-number') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="invoiceId" id="invoiceId" value="">
                                                <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-invoice"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="invoiceType" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="invoiceType" name="invoiceType" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="route" class="col-sm-4 control-label">{{ trans('operational/fields.route') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="route" name="route" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="amount" name="amount" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }}</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" id="remainingFix" name="remainingFix" value="" readonly>
                                            <input type="text" class="form-control currency" id="remaining" name="remaining" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="customerSender" class="col-sm-4 control-label">{{ trans('shared/common.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerSender" name="customerSender" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sender" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="sender" name="sender" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerReceiver" class="col-sm-4 control-label">{{ trans('shared/common.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerReceiver" name="customerReceiver" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiver" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiver" name="receiver" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="payment" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="payment" name="payment" value="" readonly>
                                            <select class="form-control" id="payment-dp" name="payment-dp">
                                                <option value="{{ TransactionResiHeader::BILL_TO_SENDER }}">{{ TransactionResiHeader::BILL_TO_SENDER }}</option>
                                                <option value="{{ TransactionResiHeader::BILL_TO_RECIEVER }}">{{ TransactionResiHeader::BILL_TO_RECIEVER }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receipt" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="receipt" name="receipt" value="">
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

<div id="modal-lov-invoice" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('accountreceivables/fields.invoice-receivable') }}</h4>
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
                                    <th>{{ trans('accountreceivables/fields.invoice-number') }}<hr/>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                    <th>{{ trans('accountreceivables/fields.amount') }}</th>
                                    <th>{{ trans('accountreceivables/fields.remaining') }}</th>
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
var indexLine    = '{{ $indexLine }}';
var billToSender = '{{ TransactionResiHeader::BILL_TO_SENDER }}';
var dp           = '{{ Receipt::DP }}';
var batch        = '{{ Receipt::BATCH }}';
var cash         = '{{ Receipt::CASH }}';
var transfer     = '{{ Receipt::TRANSFER }}';
var cekGiro      = '{{ Receipt::CEK_GIRO }}';

$(document).on('ready', function(){
    /** HEADER **/
    $('#type').on('change', changeType);
    $('#receiptMethod').on('change', changeReceiptMethod);

    $('#show-lov-batch-invoice').on('click', showLovBatchInvoice);
    $('#searchBatchInvoice').on('keyup', loadLovBatchInvoice);
    $('#table-lov-batch-invoice tbody').on('click', 'tr', selectBatchInvoice);

    $('#show-lov-cek-giro').on('click', showLovCekGiro);
    $('#searchCekGiro').on('keyup', loadLovCekGiro);
    $('#table-lov-cek-giro tbody').on('click', 'tr', selectCekGiro);

    $('#show-lov-bank').on('click', showLovBank);
    $('#searchBank').on('keyup', loadLovBank);
    $('#table-lov-bank tbody').on('click', 'tr', selectBank);

    $('#clear-lines').on('click', clearLines);
    $('#add-line').on('click', addLine);
    $("#save-line").on('click', saveLine);
    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);

    $('#show-lov-invoice').on('click', showLovInvoice);
    $('#searchInvoice').on('keyup', loadLovInvoice);
    $('#table-lov-invoice tbody').on('click', 'tr', selectInvoice);

    $('#receipt').on('keyup', calculateRemaining);

    $('.btn-submit').on('click', function(event){
        $('.btn-submit').hide();
        $('#add-form').submit();
    });
});

var changeType = function() {
    if ($('#type').val() == batch) {
        $('#form-group-batch-invoice').removeClass('hidden');
        $('#form-group-cek-giro').addClass('hidden');
        $('#toolbar-action-line').addClass('hidden');
        $('#th-action-line').addClass('hidden');
        $('.td-action-line').addClass('hidden');
        $('#table-line tbody').html('');
        calculateTotalReceipt();
    } else if ($('#type').val() == cekGiro) {
        $('#form-group-batch-invoice').addClass('hidden');
        $('#form-group-cek-giro').removeClass('hidden');
        $('#toolbar-action-line').addClass('hidden');
        $('#th-action-line').addClass('hidden');
        $('.td-action-line').addClass('hidden');
        $('#table-line tbody').html('');
        calculateTotalReceipt();
    } else {
        $('#form-group-batch-invoice').addClass('hidden');
        $('#form-group-cek-giro').addClass('hidden');
        $('#toolbar-action-line').removeClass('hidden');
        $('#th-action-line').removeClass('hidden');
        $('.td-action-line').removeClass('hidden');
    }

    $('#table-line tbody').html('');
    $('#batchInvoiceId').val('');
    $('#batchInvoiceNumber').val('');
    $('#totalReceipt').val(0);
};

var changeReceiptMethod = function() {
    if ($('#receiptMethod').val() == cash) {
        $('#form-group-bank').addClass('hidden');
    } else if ($('#receiptMethod').val() == transfer) {
        $('#form-group-bank').removeClass('hidden');
    }
};

var showLovBatchInvoice = function() {
    $('#searchBatchInvoice').val('');
    loadLovBatchInvoice(function() {
        $('#modal-lov-batch-invoice').modal('show');
    });
};

var xhrBatchInvoice;
var loadLovBatchInvoice = function(callback) {
    if(xhrBatchInvoice && xhrBatchInvoice.readyState != 4){
        xhrBatchInvoice.abort();
    }
    xhrBatchInvoice = $.ajax({
        url: '{{ URL($url.'/get-json-batch-invoice') }}',
        data: {search: $('#searchBatchInvoice').val(), id: $('#id').val()},
        success: function(data) {
            $('#table-lov-batch-invoice tbody').html('');
            data.forEach(function(item) {
                var customerSenderName   = item.customer_sender_name != null ? item.customer_sender_name : '';
                var customerReceiverName = item.customer_receiver_name != null ? item.customer_receiver_name : '';
                $('#table-lov-batch-invoice tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.batch_invoice_number + '</td>\
                        <td>' + item.invoice_number + '<hr/>' + item.type + '</td>\
                        <td>' + item.resi_number + '<hr/>' + item.route_code + '</td>\
                        <td>' + customerSenderName + '<hr/>' + item.sender_name + '</td>\
                        <td>' + customerReceiverName + '<hr/>' + item.receiver_name + '</td>\
                        <td class="text-right">' + parseInt(item.amount).formatMoney(0) + '</td>\
                        <td class="text-right">' + parseInt(item.remaining).formatMoney(0) + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectBatchInvoice = function() {
    var data = $(this).data('json');
    $('#batchInvoiceId').val(data.batch_invoice_header_id);
    $('#batchInvoiceNumber').val(data.batch_invoice_number);
    $('#totalReceipt').val(data.total_remaining.formatMoney(0));

    $('#table-line tbody').html('');
    data.lines.forEach(function(line) {
        var htmlTr = '<tr>\
                    <td > ' + line.invoiceNumber + '<hr/>' + line.invoiceType + ' </td>\
                    <td > ' + line.resiNumber + '<hr/>' + line.route + ' </td>\
                    <td > ' + line.customerSender + '<hr/>' + line.sender + '</td>\
                    <td > ' + line.customerReceiver + '<hr/>' + line.receiver + '</td>\
                    <td > ' + line.payment + ' </td>\
                    <td class="text-right"> ' + parseInt(line.receipt).formatMoney(0) + ' </td>\
                    <input type="hidden" name="invoiceId[]" value="' + line.invoiceId + '">\
                    <input type="hidden" name="invoiceNumber[]" value="' + line.invoiceNumber + '">\
                    <input type="hidden" name="invoiceType[]" value="' + line.invoiceType + '">\
                    <input type="hidden" name="resiNumber[]" value="' + line.resiNumber + '">\
                    <input type="hidden" name="payment[]" value="' + line.payment + '">\
                    <input type="hidden" name="route[]" value="' + line.route + '">\
                    <input type="hidden" name="customerSender[]" value="' + line.customerSender + '">\
                    <input type="hidden" name="sender[]" value="' + line.sender + '">\
                    <input type="hidden" name="customerReceiver[]" value="' + line.customerReceiver + '">\
                    <input type="hidden" name="receiver[]" value="' + line.receiver + '">\
                    <input type="hidden" name="receipt[]" value="' + line.receipt + '">\
                </tr>';
        $('#table-line tbody').append(htmlTr);
    });

    $('#modal-lov-batch-invoice').modal('hide');
};

var showLovCekGiro = function() {
    $('#searchCekGiro').val('');
    loadLovCekGiro(function() {
        $('#modal-lov-cek-giro').modal('show');
    });
};

var xhrCekGiro;
var loadLovCekGiro = function(callback) {
    if(xhrCekGiro && xhrCekGiro.readyState != 4){
        xhrCekGiro.abort();
    }
    xhrCekGiro = $.ajax({
        url: '{{ URL($url.'/get-json-cek-giro') }}',
        data: {search: $('#searchCekGiro').val()},
        success: function(data) {
            $('#table-lov-cek-giro tbody').html('');
            data.forEach(function(item) {
                customerName = item.customer_name || '';
                $('#table-lov-cek-giro tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.type + '</td>\
                        <td>' + item.cek_giro_number + '<hr/>' + item.cek_giro_account_number + '</td>\
                        <td>' + customerName + '<hr/>' + item.person_name + '</td>\
                        <td>' + item.bank_name + '</td>\
                        <td class="text-center">' + item.date + '<hr/>' + item.clearing_date + '</td>\
                        <td class="text-right">' + parseInt(item.total_amount).formatMoney(0) + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectCekGiro = function() {
    var data = $(this).data('json');
    $('#cekGiroHeaderId').val(data.cek_giro_header_id);
    $('#cekGiroNumber').val(data.cek_giro_account_number + ' - ' + data.bank_name);
    $('#totalReceipt').val(data.total_amount.formatMoney(0));

    $('#table-line tbody').html('');
    data.lines.forEach(function(line) {
        var htmlTr = '<tr>\
                    <td > ' + line.invoiceNumber + '<hr/>' + line.invoiceType + ' </td>\
                    <td > ' + line.resiNumber + '<hr/>' + line.route + ' </td>\
                    <td > ' + line.customerSender + '<hr/>' + line.sender + '</td>\
                    <td > ' + line.customerReceiver + '<hr/>' + line.receiver + '</td>\
                    <td > ' + line.payment + ' </td>\
                    <td class="text-right"> ' + parseInt(line.receipt).formatMoney(0) + ' </td>\
                    <input type="hidden" name="invoiceId[]" value="' + line.invoiceId + '">\
                    <input type="hidden" name="invoiceNumber[]" value="' + line.invoiceNumber + '">\
                    <input type="hidden" name="invoiceType[]" value="' + line.invoiceType + '">\
                    <input type="hidden" name="resiNumber[]" value="' + line.resiNumber + '">\
                    <input type="hidden" name="payment[]" value="' + line.payment + '">\
                    <input type="hidden" name="route[]" value="' + line.route + '">\
                    <input type="hidden" name="customerSender[]" value="' + line.customerSender + '">\
                    <input type="hidden" name="sender[]" value="' + line.sender + '">\
                    <input type="hidden" name="customerReceiver[]" value="' + line.customerReceiver + '">\
                    <input type="hidden" name="receiver[]" value="' + line.receiver + '">\
                    <input type="hidden" name="receipt[]" value="' + line.receipt + '">\
                </tr>';
        $('#table-line tbody').append(htmlTr);
    });

    $('#modal-lov-cek-giro').modal('hide');
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

var clearLines = function() {
    $('#table-line tbody').html('');
    calculateTotalReceipt();
};

var addLine = function() {
    clearFormLine();
    togglePayment();
    $('#title-modal-line').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.add') }}');

    $('#modal-line').modal('show');
};

var togglePayment = function() {
    if ($('#type').val() == dp) {
        $('#payment').addClass('hidden');
        $('#payment-dp').removeClass('hidden');
    } else {
        $('#payment').removeClass('hidden');
        $('#payment-dp').addClass('hidden');
    }
};

var editLine = function() {
    clearFormLine();
    togglePayment();

    var $tr = $(this).parent().parent();
    var indexFormLine = $tr.data('index-line');
    var invoiceId = $tr.find('[name="invoiceId[]"]').val();
    var invoiceNumber = $tr.find('[name="invoiceNumber[]"]').val();
    var invoiceType = $tr.find('[name="invoiceType[]"]').val();
    var resiNumber = $tr.find('[name="resiNumber[]"]').val();
    var route = $tr.find('[name="route[]"]').val();
    var customerSender = $tr.find('[name="customerSender[]"]').val();
    var sender = $tr.find('[name="sender[]"]').val();
    var customerReceiver = $tr.find('[name="customerReceiver[]"]').val();
    var receiver = $tr.find('[name="receiver[]"]').val();
    var payment = $tr.find('[name="payment[]"]').val();
    var amount = currencyToInt($tr.find('[name="amount[]"]').val());
    var remainingFix = currencyToInt($tr.find('[name="remainingFix[]"]').val());
    var remaining = currencyToInt($tr.find('[name="remaining[]"]').val());
    var receipt = currencyToInt($tr.find('[name="receipt[]"]').val());

    $('#indexFormLine').val(indexFormLine);
    $('#invoiceId').val(invoiceId);
    $('#invoiceNumber').val(invoiceNumber);
    $('#invoiceType').val(invoiceType);
    $('#resiNumber').val(resiNumber);
    $('#route').val(route);
    $('#customerSender').val(customerSender);
    $('#sender').val(sender);
    $('#customerReceiver').val(customerReceiver);
    $('#receiver').val(receiver);
    $('#payment').val(payment);
    $('#payment-dp').val(payment);
    $('#amount').val(amount.formatMoney(0));
    $('#remainingFix').val(remainingFix.formatMoney(0));
    $('#remaining').val(remaining.formatMoney(0));
    $('#receipt').val(receipt.formatMoney(0));

    $('#title-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#modal-line').modal("show");
};

var clearFormLine = function() {
    $('#indexFormLine').val('');
    $('#invoiceId').val('');
    $('#invoiceNumber').val('');
    $('#invoiceType').val('');
    $('#resiNumber').val('');
    $('#route').val('');
    $('#customerSender').val('');
    $('#sender').val('');
    $('#customerReceiver').val('');
    $('#receiver').val('');
    $('#payment').val('');
    $('#amount').val(0);
    $('#remainingFix').val(0);
    $('#remaining').val(0);
    $('#receipt').val(0);
};

var saveLine = function() {
    var indexFormLine = $('#indexFormLine').val();
    var invoiceId     = $('#invoiceId').val();
    var receipt       = $('#receipt').val();
    var error         = false;

    if (invoiceId == '' || invoiceId <= 0) {
        $('#invoiceId').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#invoiceId').parent().parent().removeClass('has-error');
    }

    if (receipt == '' || receipt <= 0) {
        $('#receipt').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#receipt').parent().parent().removeClass('has-error');
    }

    if (error) {
        return;
    }

    var invoiceNumber = $('#invoiceNumber').val();
    var invoiceType = $('#invoiceType').val();
    var resiNumber = $('#resiNumber').val();
    var route = $('#route').val();
    var customerSender = $('#customerSender').val();
    var sender = $('#sender').val();
    var customerReceiver = $('#customerReceiver').val();
    var receiver = $('#receiver').val();
    var payment = $('#type').val() == dp ? $('#payment-dp').val() : $('#payment').val();
    var amount = currencyToInt($('#amount').val());
    var remainingFix = currencyToInt($('#remainingFix').val());
    var remaining = currencyToInt($('#remaining').val());
    var receipt = currencyToInt($('#receipt').val());

    var htmlTr = '<td >' + invoiceNumber + '<hr/>' + invoiceType + '</td>\
                    <td >' + resiNumber + '<hr/>' + route + '</td>\
                    <td >' + customerSender + '<hr/>' + sender + '</td>\
                    <td >' + customerReceiver + '<hr/>' + receiver + '</td>\
                    <td >' + payment + '</td>\
                    <td class="text-right">' + receipt.formatMoney(0) + '</td>\
                    <td class="text-center" class="td-action-line">\
                        <a data-toggle="tooltip" class="btn btn-warning btn-xs edit-line" ><i class="fa fa-pencil"></i></a>\
                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>\
                        <input type="hidden" name="invoiceId[]" value="' + invoiceId + '">\
                        <input type="hidden" name="invoiceNumber[]" value="' + invoiceNumber + '">\
                        <input type="hidden" name="invoiceType[]" value="' + invoiceType + '">\
                        <input type="hidden" name="resiNumber[]" value="' + resiNumber + '">\
                        <input type="hidden" name="payment[]" value="' + payment + '">\
                        <input type="hidden" name="route[]" value="' + route + '">\
                        <input type="hidden" name="customerSender[]" value="' + customerSender + '">\
                        <input type="hidden" name="sender[]" value="' + sender + '">\
                        <input type="hidden" name="customerReceiver[]" value="' + customerReceiver + '">\
                        <input type="hidden" name="receiver[]" value="' + receiver + '">\
                        <input type="hidden" name="amount[]" value="' + amount + '">\
                        <input type="hidden" name="remainingFix[]" value="' + remainingFix + '">\
                        <input type="hidden" name="remaining[]" value="' + remaining + '">\
                        <input type="hidden" name="receipt[]" value="' + receipt + '">\
                    </td>';

    if (indexFormLine != '') {
        $('tr[data-index-line="' + indexFormLine + '"]').html(htmlTr);
    } else {
        $('#table-line tbody').append(
            '<tr data-index-line="' + indexLine + '">' + htmlTr + '</tr>'
        );
        indexLine++;
    }

    calculateTotalReceipt();
    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);

    $('#modal-line').modal("hide");
};

var deleteLine = function() {
    $(this).parent().parent().remove();
    calculateTotalReceipt();
};

var calculateTotalReceipt = function() {
    var totalReceipt = 0;
    $('#table-line tbody tr').each(function() {
        var receipt   = currencyToInt($(this).find('[name="receipt[]"]').val());
        totalReceipt += receipt; 
    });

    $('#totalReceipt').val(totalReceipt.formatMoney(0));
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
        data: {search: $('#searchInvoice').val(), type: $('#type').val()},
        success: function(data) {
            $('#table-lov-invoice tbody').html('');
            data.forEach(function(item) {
                var customerSenderName   = item.customer_sender_name != null ? item.customer_sender_name : '';
                var customerReceiverName = item.customer_receiver_name != null ? item.customer_receiver_name : '';
                $('#table-lov-invoice tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.invoice_number + '<hr/>' + item.type + '</td>\
                        <td>' + item.resi_number + '<hr/>' + item.route_code + '</td>\
                        <td>' + customerSenderName + '<hr/>' + item.sender_name + '</td>\
                        <td>' + customerReceiverName + '<hr/>' + item.receiver_name + '</td>\
                        <td class="text-right">' + parseInt(item.amount).formatMoney(0) + '</td>\
                        <td class="text-right">' + parseInt(item.remaining).formatMoney(0) + '</td>\
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
    var error = false;
    var customerSenderName = data.customer_sender_name != null ? data.customer_sender_name : '';
    var customerReceiverName = data.customer_receiver_name != null ? data.customer_receiver_name : '';

    $('#table-line tbody tr').each(function() {
        var invoiceId = $(this).find('[name="invoiceId[]"]').val();
        if (data.invoice_id == invoiceId) {
            $('#modal-alert').find('.alert-message').html('Invoice is already exist');
            $('#modal-alert').modal('show');
            error = true;
        }
    });

    if (error) {
        return;
    }

    $('#invoiceId').val(data.invoice_id);
    $('#invoiceNumber').val(data.invoice_number);
    $('#invoiceType').val(data.type);
    $('#resiNumber').val(data.resi_number);
    $('#route').val(data.route_code);
    $('#customerSender').val(customerSenderName);
    $('#sender').val(data.sender_name);
    $('#customerReceiver').val(customerReceiverName);
    $('#receiver').val(data.receiver_name);
    $('#payment').val(data.payment);

    if ($('#payment-dp option[value="' + data.payment + '"]').length != 0) {
        $('#payment-dp').val(data.payment);
    } else {
        $('#payment-dp').val(billToSender);
    }

    $('#amount').val(parseInt(data.amount).formatMoney(0));
    $('#remainingFix').val(parseInt(data.remaining).formatMoney(0));
    $('#remaining').val(0);
    $('#receipt').val(parseInt(data.remaining).formatMoney(0));

    $('#modal-lov-invoice').modal('hide');
};

var calculateRemaining = function(){
    var remainingFix = currencyToInt($('#remainingFix').val());
    var receipt      = currencyToInt($('#receipt').val());
    var remaining    = remainingFix - receipt;

    $('#remaining').val(remaining.formatMoney(0));
};
</script>
@endsection
