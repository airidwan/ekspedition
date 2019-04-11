@extends('layouts.master')

@section('title', trans('payable/menu.dp-invoice'))

<?php use App\Modules\Payable\Model\Transaction\InvoiceHeader; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.dp-invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->header_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber"  value="{{ !empty($model->invoice_number) ? $model->invoice_number : '' }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="status" name="status"  value="{{ !empty($model->status) ? $model->status : '' }}" disabled>
                                </div>
                            </div>
                            <?php 
                            $line     = $model->lineOne()->first();
                            $po       = !empty($line) ? $line->po : null;
                            $poNumber = !empty($po) ? $po->po_number : '';
                            $poHeaderId = !empty($po) ? $po->header_id : '';
                            ?>
                            <div class="form-group">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ $poNumber }}" disabled>
                                        <input type="hidden" id="poHeaderId" name="poHeaderId" value="{{ $poHeaderId }}">
                                        <span class="btn input-group-addon {{ $model->status == InvoiceHeader::INCOMPLETE ? 'md-trigger' : '' }}" data-modal="modal-po"><i class="fa fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                            <?php $descriptionPo = !empty($po) ? $po->description : ''; ?>
                            <div class="form-group">
                                <label for="descriptionPo" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="descriptionPo" name="descriptionPo" value="{{ $descriptionPo }}" disabled>
                                </div>
                            </div>
                            <?php $amountPo = !empty($po) ? $po->total : ''; ?>
                            <div class="form-group">
                                <label for="amountPo" class="col-sm-4 control-label">{{ trans('shared/common.total-po') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="amountPo" name="amountPo" value="{{ $amountPo }}" disabled>
                                </div>
                            </div>
                            <?php 
                                $vendor = !empty($po)? $po->vendor : null;
                                $vendorId   = !empty($vendor) ? $vendor->vendor_id : '';
                                $vendorName = !empty($vendor) ? $vendor->vendor_name : '';
                            ?>
                            <div class="form-group">
                                <label for="vendor" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="vendor" name="vendor" value="{{ $vendorName }}" disabled>
                                </div>
                            </div>
                            <?php $address = !empty($vendor) ? $vendor->address : ''; ?>
                            <div class="form-group">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="address" name="address" value="{{ $address }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 portlets">
                            <?php $amount = !empty($line) ? $line->amount : ''; ?>
                            <div class="form-group">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.amount') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ $amount }}" disabled>
                                </div>
                            </div>
                            <?php $taxLine = !empty($line) ? $line->tax : ''; ?>
                            <?php $tax = $taxLine ;?>
                            <div class="form-group">
                                <label for="tax" class="col-sm-4 control-label">{{ trans('payable/fields.tax') }} </label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="tax" name="tax" disabled>
                                        <option value="">{{ trans('shared/common.please-select') }} {{ trans('payable/fields.tax') }}</option>
                                        @foreach($optionTax as $row)
                                            <option value="{{ $row }}" {{ $row == $tax ? 'selected' : '' }}>{{ $row }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <?php $fixAmount = !empty($line) ? $line->amount + ($line->tax / 100 * $line->amount) : ''; ?>
                            <div class="form-group">
                                <label for="fixAmount" class="col-sm-4 control-label">{{ trans('shared/common.total-dp') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="fixAmount" name="fixAmount" value="{{ $fixAmount }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="description" name="description" rows="3" disabled>{{ $model->description }}</textarea>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
