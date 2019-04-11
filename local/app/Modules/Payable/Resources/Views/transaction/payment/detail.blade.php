<?php
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\Payment;
?>

@extends('layouts.master')

@section('title', trans('payable/menu.payment'))

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
                        <input type="hidden" id="id" name="id" value="{{ $model->payment_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="paymentNumber" class="col-sm-4 control-label">{{ trans('payable/fields.payment-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="paymentNumber" name="paymentNumber"  value="{{ $model->payment_number }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="status" name="status"  value="{{ $model->status }}" disabled>
                                </div>
                            </div>
                            <?php
                            $type          = !empty($invoice) ? $invoice->type : null;
                            $typeId        = !empty($type) ? $type->type_id : '';
                            $typeName      = !empty($type) ? $type->type_name : '';
                            $invoiceId     = !empty($invoice) ? $invoice->header_id : '';
                            $invoiceNumber = !empty($invoice) ? $invoice->invoice_number : '';
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
                            <div class="form-group">
                                <label for="vendinvoiceIdorName" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ $invoiceNumber }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="typeName" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <input type="hidden" class="form-control " id="typeId" name="typeId" value="{{ $typeId }}" disabled>
                                    <input type="text" class="form-control " id="typeName" name="typeName" value="{{ $typeName }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorCode" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="hidden" class="form-control " id="vendorId" name="vendorId" value="{{ $vendorId }}" disabled>
                                    <input type="text" class="form-control " id="vendorCode" name="vendorCode" value="{{ $vendorCode }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorName" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendorName" name="vendorName" value="{{ $vendorName }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendorAddress" name="vendorAddress" value="{{ $vendorAddress }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php $paymentMethod = $model->receipt_method; ?>
                            <div class="form-group">
                                <label for="paymentMethod" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-method') }}</label>
                                <div class="col-sm-8">
                                    <select id="paymentMethod" name="paymentMethod" class="form-control" disabled>
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
                            <div class="form-group {{ $paymentMethod != Payment::TRANSFER ? 'hidden' : '' }}" id="form-group-bank">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="bankName" name="bankName" value="{{ $bankName }}" disabled>
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
                            <div class="form-group">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.total-amount') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ $totalAmount }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="totalInterest" class="col-sm-4 control-label">{{ trans('payable/fields.total-interest') }}  </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalInterest" name="totalInterest" value="{{ $totalInterest }}" disabled>
                                </div>
                            </div>
                            <?php
                            $totalPayment = $model->total_amount + $model->total_interest;
                            ?>
                            <div class="form-group">
                                <label for="totalPayment" class="col-sm-4 control-label">{{ trans('payable/fields.total-payment') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalPayment" name="totalPayment" value="{{ $totalPayment }}" disabled }} >
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="3" disabled>{{ $model->note }}</textarea>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
