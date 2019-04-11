<?php
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receipt-other'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.receipt-other') }}</h2>
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
                        <input type="hidden" id="receiptId" name="receiptId" value="{{ $model->receipt_id }}">
                        <div class="col-sm-8 portlets">
                            <div class="form-group">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="receiptNumber" name="receiptNumber" value="{{ $model->receipt_number }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiptDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <?php $receiptDate = new \DateTime($model->created_date); ?>
                                        <input type="text" id="receiptDate" name="receiptDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $receiptDate->format('d-m-Y') }}" disabled>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('personName') ? 'has-error' : '' }}">
                                <label for="personName" class="col-sm-4 control-label">{{ trans('shared/common.person-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="personName" name="personName" value="{{ count($errors) > 0 ? old('personName') : $model->person_name }}" {{ !empty($model->receipt_id) ? 'readonly' : '' }}>
                                    @if($errors->has('personName'))
                                        <span class="help-block">{{ $errors->first('personName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $type = count($errors) > 0 ? old('type') : $model->type ?>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select id="type" name="type" class="form-control" {{ !empty($model->receipt_id) ? 'disabled' : '' }}>
                                        <option value="">Select Type</option>
                                        @foreach($optionType as $option)
                                            <option value="{{ $option }}" {{ $option == $type ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                        <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $resiNumber = !empty($model->resi) ? $model->resi->resi_number : ''; ?>
                            <div class="form-group {{ $errors->has('resiId') ? 'has-error' : '' }} {{ $type != Receipt::EXTRA_COST ? 'hidden' : '' }}" id="form-group-resi">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="resiId" name="resiId" value="{{ count($errors) > 0 ? old('resiId') : $model->resi_header_id }}">
                                        <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->receipt_id) ? 'show-lov-resi' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('resiId'))
                                        <span class="help-block">{{ $errors->first('resiId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $invoiceApNumber = !empty($model->invoiceApHeader) ? $model->invoiceApHeader->invoice_number : ''; ?>
                            <div class="form-group {{ $errors->has('invoiceApId') ? 'has-error' : '' }} {{ $type != Receipt::KASBON ? 'hidden' : '' }}" id="form-group-invoice-ap">
                                <label for="invoiceApNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="invoiceApId" name="invoiceApId" value="{{ count($errors) > 0 ? old('invoiceApId') : $model->invoice_ap_header_id }}">
                                        <input type="text" class="form-control" id="invoiceApNumber" name="invoiceApNumber" value="{{ count($errors) > 0 ? old('invoiceApNumber') : $invoiceApNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->receipt_id) ? 'show-lov-invoice-ap-header' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('invoiceApId'))
                                        <span class="help-block">{{ $errors->first('invoiceApId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $assetNumber = !empty($model->additionAsset) ? $model->additionAsset->asset_number : ''; ?>
                            <div class="form-group {{ $errors->has('assetId') ? 'has-error' : '' }} {{ $type != Receipt::ASSET_SELLING ? 'hidden' : '' }}" id="form-group-asset">
                                <label for="assetNumber" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="assetId" name="assetId" value="{{ count($errors) > 0 ? old('assetId') : $model->asset_id }}">
                                        <input type="text" class="form-control" id="assetNumber" name="assetNumber" value="{{ count($errors) > 0 ? old('assetNumber') : $assetNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->receipt_id) ? 'show-lov-asset' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('assetId'))
                                        <span class="help-block">{{ $errors->first('assetId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $coa = !empty($model->coa) ? $model->coa->coa_code.' - '.$model->coa->description : ''; ?>
                            <div class="form-group {{ $errors->has('coaId') ? 'has-error' : '' }} {{ empty($type) || $type == Receipt::KASBON ? 'hidden' : '' }}" id="form-group-coa">
                                <label for="coa" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="coaId" name="coaId" value="{{ count($errors) > 0 ? old('coaId') : $model->coa_id }}">
                                        <input type="text" class="form-control" id="coa" name="coa" value="{{ count($errors) > 0 ? old('coa') : $coa }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->receipt_id) ? 'show-lov-coa' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('coaId'))
                                        <span class="help-block">{{ $errors->first('coaId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $receiptMethod = count($errors) > 0 ? old('receiptMethod') : $model->receipt_method ?>
                            <div class="form-group">
                                <label for="receiptMethod" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-method') }}</label>
                                <div class="col-sm-8">
                                    <select id="receiptMethod" name="receiptMethod" class="form-control" {{ !empty($model->receipt_id) ? 'disabled' : '' }}>
                                        @foreach($optionReceiptMethod as $option)
                                            <option value="{{ $option }}" {{ $option == $receiptMethod ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <?php
                                $bankName = !empty($model->bank) ? $model->bank->account_number . ' - ' . $model->bank->bank_name : '';
                            ?>
                            <div class="form-group {{ $errors->has('bankId') ? 'has-error' : '' }} {{ $receiptMethod != Receipt::TRANSFER ? 'hidden' : '' }}" id="form-group-bank">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="bankId" name="bankId" value="{{ count($errors) > 0 ? old('bankId') : $model->bank_id }}">
                                        <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $bankName }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->receipt_id) ? 'show-lov-bank' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('bankId'))
                                        <span class="help-block">{{ $errors->first('bankId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            $cekGiroNumber = !empty($model->cekGiro) ? $model->cekGiro->cg_number . ' - ' . $model->cekGiro->bank_name : '';
                            ?>
                            <div class="form-group {{ $errors->has('cekGiroId') ? 'has-error' : '' }} {{ $receiptMethod != Receipt::CEK_GIRO ? 'hidden' : '' }}" id="form-group-cek-giro">
                                <label for="cekGiroNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="cekGiroId" name="cekGiroId" value="{{ count($errors) > 0 ? old('cekGiroId') : '' }}">
                                        <input type="text" class="form-control" id="cekGiroNumber" name="cekGiroNumber" value="{{ count($errors) > 0 ? old('cekGiroNumber') : $cekGiroNumber }}" readonly>
                                        <span class="btn input-group-addon" id="{{ empty($model->receipt_id) ? 'show-lov-cek-giro' : '' }}"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('cekGiroId'))
                                        <span class="help-block">{{ $errors->first('cekGiroId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                                <label for="amount" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.amount') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control currency' id="amount" name="amount" value="{{ count($errors) > 0 ? str_replace(',', '', old('amount')) : $model->amount }}" {{ !empty($model->receipt_id) ? 'readonly' : '' }}>
                                    @if($errors->has('amount'))
                                        <span class="help-block">{{ $errors->first('amount') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="description" name="description" {{ !empty($model->receipt_id) ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                        <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>

                                @if (empty($model->receipt_id))
                                    <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @else
                                    <a href="{{ URL($url . '/print-pdf/' . $model->receipt_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                    </a>
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

<div id="modal-lov-invoice-ap-header" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('payable/fields.invoice') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchInvoiceApHeader" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchInvoiceApHeader" name="searchInvoiceApHeader">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-invoice-ap-header" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('payable/fields.invoice-number') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('payable/fields.trading') }}</th>
                                    <th>{{ trans('payable/fields.total-invoice') }}</th>
                                    <th>{{ trans('payable/fields.total-remain') }}</th>
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

<div id="modal-lov-asset" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('asset/fields.asset') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchAsset" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchAsset" name="searchAsset">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-asset" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('asset/fields.asset-number') }}<hr/>{{ trans('purchasing/fields.po-number') }}</th>
                                    <th>{{ trans('shared/common.category') }}</th>
                                    <th>{{ trans('inventory/fields.item-code') }}<hr/>{{ trans('inventory/fields.item') }}</th>
                                    <th>{{ trans('asset/fields.employee') }}</th>
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
                                    <th>{{ trans('accountreceivables/fields.number') }}</th>
                                    <th>{{ trans('accountreceivables/fields.bank-name') }}</th>
                                    <th>{{ trans('accountreceivables/fields.customer') }}</th>
                                    <th>{{ trans('accountreceivables/fields.start-date') }}</th>
                                    <th>{{ trans('accountreceivables/fields.due-date') }}</th>
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
var extraCost    = '{{ Receipt::EXTRA_COST }}';
var kasbon       = '{{ Receipt::KASBON }}';
var assetSelling = '{{ Receipt::ASSET_SELLING }}';
var other        = '{{ Receipt::OTHER }}';

var cash     = '{{ Receipt::CASH }}';
var transfer = '{{ Receipt::TRANSFER }}';
var cekGiro  = '{{ Receipt::CEK_GIRO }}';

$(document).on('ready', function(){
    /** HEADER **/
    $('#type').on('change', changeType);

    $('#show-lov-resi').on('click', showLovResi);
    $('#searchResi').on('keyup', loadLovResi);
    $('#table-lov-resi tbody').on('click', 'tr', selectResi);

    $('#show-lov-invoice-ap-header').on('click', showLovInvoiceApHeader);
    $('#searchInvoiceApHeader').on('keyup', loadLovInvoiceApHeader);
    $('#table-lov-invoice-ap-header tbody').on('click', 'tr', selectInvoiceApHeader);

    $('#show-lov-coa').on('click', showLovCoa);
    $('#searchCoa').on('keyup', loadLovCoa);
    $('#table-lov-coa tbody').on('click', 'tr', selectCoa);

    $('#show-lov-asset').on('click', showLovAsset);
    $('#searchAsset').on('keyup', loadLovAsset);
    $('#table-lov-asset tbody').on('click', 'tr', selectAsset);

    $('#receiptMethod').on('change', changeReceiptMethod);

    $('#show-lov-bank').on('click', showLovBank);
    $('#searchBank').on('keyup', loadLovBank);
    $('#table-lov-bank tbody').on('click', 'tr', selectBank);

    $('#show-lov-cek-giro').on('click', showLovCekGiro);
    $('#searchCekGiro').on('keyup', loadLovCekGiro);
    $('#table-lov-cek-giro tbody').on('click', 'tr', selectCekGiro);
});

var changeType = function() {
    if ($('#type').val() == extraCost) {
        $('#form-group-resi').removeClass('hidden');
        $('#form-group-invoice-ap').addClass('hidden');
        $('#form-group-asset').addClass('hidden');
        $('#form-group-coa').removeClass('hidden');
    } else if ($('#type').val() == kasbon) {
        $('#form-group-resi').addClass('hidden');
        $('#form-group-invoice-ap').removeClass('hidden');
        $('#form-group-asset').addClass('hidden');
        $('#form-group-coa').addClass('hidden');
    } else if ($('#type').val() == assetSelling) {
        $('#form-group-resi').addClass('hidden');
        $('#form-group-invoice-ap').addClass('hidden');
        $('#form-group-asset').removeClass('hidden');
        $('#form-group-coa').addClass('hidden');
    } else if ($('#type').val() == other) {
        $('#form-group-resi').addClass('hidden');
        $('#form-group-invoice-ap').addClass('hidden');
        $('#form-group-asset').addClass('hidden');
        $('#form-group-coa').removeClass('hidden');
    } else {
        $('#form-group-resi').addClass('hidden');
        $('#form-group-invoice-ap').addClass('hidden');
        $('#form-group-asset').addClass('hidden');
        $('#form-group-coa').addClass('hidden');
    }
};

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

var showLovInvoiceApHeader = function() {
    $('#searchInvoiceApHeader').val('');
    loadLovInvoiceApHeader(function() {
        $('#modal-lov-invoice-ap-header').modal('show');
    });
};

var xhrInvoiceApHeader;
var loadLovInvoiceApHeader = function(callback) {
    if(xhrInvoiceApHeader && xhrInvoiceApHeader.readyState != 4){
        xhrInvoiceApHeader.abort();
    }
    xhrInvoiceApHeader = $.ajax({
        url: '{{ URL($url.'/get-json-invoice-ap') }}',
        data: {search: $('#searchInvoiceApHeader').val()},
        success: function(data) {
            $('#table-lov-invoice-ap-header tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-invoice-ap-header tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.invoice_number + '</td>\
                        <td>' + item.type + '</td>\
                        <td>' + item.trading_code + ' - ' + item.trading_name + '</td>\
                        <td class="text-right">' + parseInt(item.total_invoice).formatMoney(0) + '</td>\
                        <td class="text-right">' + parseInt(item.total_remaining).formatMoney(0) + '</td>\
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

var selectInvoiceApHeader = function() {
    var data = $(this).data('json');
    console.log(data);
    $('#invoiceApId').val(data.header_id);
    $('#invoiceApNumber').val(data.invoice_number);
    $('#amount').val(parseInt(data.total_remaining).formatMoney(0));

    $('#modal-lov-invoice-ap-header').modal('hide');
};

var showLovAsset = function() {
    $('#searchAsset').val('');
    loadLovAsset(function() {
        $('#modal-lov-asset').modal('show');
    });
};

var xhrAsset;
var loadLovAsset = function(callback) {
    if(xhrAsset && xhrAsset.readyState != 4){
        xhrAsset.abort();
    }
    xhrAsset = $.ajax({
        url: '{{ URL($url.'/get-json-asset') }}',
        data: {search: $('#searchAsset').val()},
        success: function(data) {
            $('#table-lov-asset tbody').html('');
            data.forEach(function(item) {
                var poNumber = item.po_number || '';
                $('#table-lov-asset tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item).replace("'", "") + '\'>\
                        <td>' + item.asset_number + '<hr/>' + poNumber + '</td>\
                        <td>' + item.category_name + '</td>\
                        <td>' + item.item_code + '<hr/>' + item.item_description + '</td>\
                        <td>' + item.employee_name + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectAsset = function() {
    var data = $(this).data('json');

    $('#assetId').val(data.asset_id);
    $('#assetNumber').val(data.asset_number);

    $('#modal-lov-asset').modal('hide');
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

var changeReceiptMethod = function() {
    if ($('#receiptMethod').val() == cash) {
        $('#form-group-bank').addClass('hidden');
        $('#form-group-cek-giro').addClass('hidden');
    } else if ($('#receiptMethod').val() == transfer) {
        $('#form-group-bank').removeClass('hidden');
        $('#form-group-cek-giro').addClass('hidden');
    } else {
        $('#form-group-bank').addClass('hidden');
        $('#form-group-cek-giro').removeClass('hidden');
    }
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
                $('#table-lov-cek-giro tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.cg_type + '</td>\
                        <td>' + item.cg_number + '</td>\
                        <td>' + item.bank_name + '</td>\
                        <td>' + item.customer_name + '</td>\
                        <td>' + item.cg_date + '</td>\
                        <td>' + item.cg_due_date + '</td>\
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
    $('#cekGiroId').val(data.cek_giro_id);
    $('#cekGiroNumber').val(data.cg_number + ' - ' + data.bank_name);

    $('#modal-lov-cek-giro').modal('hide');
};
</script>
@endsection
