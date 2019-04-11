<?php
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receipt'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>Detail</strong> {{ trans('accountreceivables/menu.receipt') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->receipt_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="receiptNumber" name="receiptNumber" value="{{ $model->receipt_number }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiptType" class="col-sm-4 control-label">{{ trans('shared/common.type') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="receiptType" name="receiptType" value="{{ $model->type }}" disabled>
                                </div>
                            </div>
                            @if($model->isCekGiro())
                            <div class="form-group">
                                <label for="cekGiroNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="cekGiroNumber" name="cekGiroNumber" value="{{ $model->cekGiroLine !== null ? $model->cekGiroLine->header->cek_giro_number : '' }}" disabled>
                                </div>
                            </div>
                            @endif
                            @if($model->isBatch())
                            <div class="form-group">
                                <label for="batchInvoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.batch-invoice-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control' id="batchInvoiceNumber" name="batchInvoiceNumber" value="{{ $model->batchInvoiceLine !== null ? $model->batchInvoiceLine->header->batch_invoice_number : 'asdf' }}" disabled>
                                </div>
                            </div>
                            @endif
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
                            <div class="form-group">
                                <label for="personName" class="col-sm-4 control-label">{{ trans('shared/common.person-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="personName" name="personName" value="{{ $model->person_name }}" readonly>
                                </div>
                            </div>
                            <?php
                            $invoiceNumber = !empty($model->Invoice) ? $model->Invoice->invoice_number : '';
                            ?>
                            <div class="form-group">
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ $invoiceNumber }}" readonly>
                                </div>
                            </div>
                            <?php
                            $resiNumber = !empty($model->Invoice->resi) ? $model->Invoice->resi->resi_number : '';
                            ?>
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ $resiNumber }}" readonly>
                                </div>
                            </div>
                            <?php
                            $invoiceType = !empty($model->Invoice) ? $model->Invoice->type : '';
                            ?>
                            <div class="form-group">
                                <label for="invoiceType" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-type') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceType" name="invoiceType" value="{{ $invoiceType }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                            $customerName = !empty($model->Invoice->customer) ? $model->Invoice->customer->customer_name : '';
                            ?>
                            <div class="form-group">
                                <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customerName }}" readonly>
                                </div>
                            </div>
                            <?php
                            $billTo = !empty($model->Invoice) ? $model->Invoice->bill_to : '';
                            ?>
                            <div class="form-group">
                                <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="billTo" name="billTo" value="{{ $billTo }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiptMethod" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-method') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="receiptMethod" id="receiptMethod" value="{{ $model->receipt_method }}" readonly>
                                </div>
                            </div>
                            <?php
                                $bankName = !empty($model->bank) ? $model->bank->bank_name . ' - ' . $model->bank->account_number : '';
                            ?>
                            <div class="form-group {{ $model->receipt_method == Receipt::CEK_GIRO ? 'hidden' : '' }}" id="form-group-bank">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="bankName" name="bankName" value="{{ $bankName }}" readonly>
                                </div>
                            </div>
                            <?php
                            $cekGiroNumber = !empty($model->cekGiro) ? $model->cekGiro->bank_name . ' - ' . $model->cekGiro->cg_number : '';
                            ?>
                            <div class="form-group {{ $model->receipt_method != Receipt::CEK_GIRO ? 'hidden' : '' }}" id="form-group-cek-giro">
                                <label for="cekGiroNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="cekGiroNumber" name="cekGiroNumber" value="{{ $cekGiroNumber }}" readonly>
                                </div>
                            </div>
                            <?php
                            $totalInvoice = !empty($model->Invoice) ? $model->Invoice->totalInvoice() : 0;
                            ?>
                            <div class="form-group">
                                <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-invoice') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control currency' id="totalInvoice" name="totalInvoice" value="{{ $totalInvoice }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->amount }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class='form-control currency' id="remaining" name="remaining" value="{{ !empty($model->invoice) ? $model->invoice->remaining() : '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="description" name="description" readonly>{{ $model->description }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <a href="{{ URL($url . '/print-pdf/' . $model->receipt_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @if($model->invoice->isInvoiceResi())
                                    <a href="{{ URL($urlResi . '/print-pdf/' . $model->invoice->resi->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i> {{ trans('shared/common.print') }} {{ trans('operational/menu.resi') }}
                                    </a>
                                    <a href="{{ URL($urlResi . '/print-pdf-tanpa-biaya/' . $model->invoice->resi->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i> {{ trans('operational/fields.print-tanpa-biaya') }} {{ trans('operational/menu.resi') }}
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
