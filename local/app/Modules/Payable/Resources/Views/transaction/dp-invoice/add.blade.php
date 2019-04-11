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
                            $line     = $model->lineOne()->first();
                            $po       = !empty($line) ? $line->po : null;
                            $poNumber = !empty($po) ? $po->po_number : '';
                            $poHeaderId = !empty($po) ? $po->header_id : '';
                            ?>
                            <div class="form-group {{ $errors->has('poNumber') ? 'has-error' : '' }}">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ count($errors) > 0 ? old('poNumber') : $poNumber }}" readonly>
                                        <input type="hidden" id="poHeaderId" name="poHeaderId" value="{{ count($errors) > 0 ? old('poHeaderId') : $poHeaderId }}">
                                        <span class="btn input-group-addon" data-toggle="{{ $model->status == InvoiceHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-po"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('poNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php 
                            $descriptionPo = !empty($po) ? $po->description : '';
                            ?>
                            <div class="form-group">
                                <label for="descriptionPo" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="descriptionPo" name="descriptionPo" value="{{ count($errors) > 0 ? old('descriptionPo') : $descriptionPo }}" readonly>
                                </div>
                            </div>
                            <?php 
                            $amountPo = !empty($po) ? $po->total : '';
                            ?>
                            <div class="form-group">
                                <label for="amountPo" class="col-sm-4 control-label">{{ trans('shared/common.total-po') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="amountPo" name="amountPo" value="{{ count($errors) > 0 ? old('amountPo') : $amountPo }}" readonly>
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
                                    <input type="hidden" class="form-control " id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $vendorId }}" readonly>
                                    <input type="text" class="form-control " id="vendor" name="vendor" value="{{ count($errors) > 0 ? old('vendor') : $vendorName }}" readonly>
                                </div>
                            </div>
                            <?php 
                                $address = !empty($vendor) ? $vendor->address : '';
                            ?>
                            <div class="form-group">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $address }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 portlets">
                            <?php
                                $amount = !empty($line) ? $line->amount : '';
                            ?>
                            <div class="form-group {{ $errors->has('totalAmount') ? 'has-error' : '' }}">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.amount') }}  <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? old('totalAmount') : $amount }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
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
                                <label for="fixAmount" class="col-sm-4 control-label">{{ trans('shared/common.total-dp') }}  <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="fixAmount" name="fixAmount" value="{{ count($errors) > 0 ? old('fixAmount') : $fixAmount }}" readonly>
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
<div id="modal-po" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('inventory/fields.list-po') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('purchasing/fields.po-number') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('inventory/fields.vendor-code') }}</th>
                            <th>{{ trans('inventory/fields.vendor-name') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.total-po') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionPo as $po)
                    <tr style="cursor: pointer;" data-po="{{ json_encode($po) }}">
                        <td>{{ $po->po_number }}</td>
                        <td>{{ $po->description }}</td>
                        <td>{{ $po->vendor_code }}</td>
                        <td>{{ $po->vendor_name }}</td>
                        <td>{{ $po->address }}</td>
                        <td class="text-right">{{ number_format($po->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
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

        $("#totalAmount").on('keyup', calculateFixAmount);
         $("#tax").on('change', calculateFixAmount);

        $("#datatables-lov").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var dataPO = $(this).data('po');

            $('#poNumber').val(dataPO.po_number);
            $('#poHeaderId').val(dataPO.header_id);
            $('#descriptionPo').val(dataPO.description);
            $('#address').val(dataPO.address);
            $('#vendor').val(dataPO.vendor_name);
            $('#vendorId').val(dataPO.supplier_id);
            $('#amountPo').val(dataPO.total);

            $('#totalAmount').autoNumeric('update', {mDec: 0, vMax: dataPO.total});


            $('.currency').autoNumeric('update', {mDec: 0});
            $('#amountPo').autoNumeric('update', {mDec: 0});

            $('#modal-po').modal("hide");
        });
    });

    var calculateFixAmount = function(){
        var amount    = currencyToInt($('#totalAmount').val());
        var tax       = currencyToInt($('#tax').val());
        var fixAmount = amount + (tax / 100 * amount);
        
        $("#fixAmount").val(fixAmount);
        $('#fixAmount').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection
