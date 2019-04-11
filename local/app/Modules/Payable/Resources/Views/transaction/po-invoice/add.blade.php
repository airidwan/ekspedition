@extends('layouts.master')

@section('title', trans('payable/menu.po-invoice'))

<?php 
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>
@section('header')
@parent
<style type="text/css">
    #table-lov-vendor tbody tr{
        cursor: pointer;
    }
    #table-lov-po tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.po-invoice') }}</h2>
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
                        <input type="hidden" name="id" id="id" value="{{ $model->header_id }}">
                        <input type="hidden" name="driverPosition" id="driverPosition" value="{{ count($errors) > 0 ? old('driverPosition') : '' }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
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
                                            <select class="form-control" id="type" name="type" {{ $model->isIncomplete() ? '' : 'disabled' }}>
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
                                    $vendor     = $model->vendor;
                                    $vendorId   = !empty($vendor) ? $vendor->vendor_id : '';
                                    $vendorName = !empty($vendor) ? $vendor->vendor_name : '';
                                    $vendorAddress = !empty($vendor) ? $vendor->address : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('vendorId') ? 'has-error' : '' }}">
                                        <label for="vendorName" class="col-sm-4 control-label">{{ trans('payable/fields.vendor') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" class="form-control" id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $vendorId }}" readonly>
                                                <input type="text" class="form-control" id="vendorName" name="vendorName" value="{{ count($errors) > 0 ? old('vendorName') : $vendorName }}" readonly>
                                                <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-vendor' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('vendorId'))
                                            <span class="help-block">{{ $errors->first('vendorId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('vendorAddress') ? 'has-error' : '' }}">
                                        <label for="vendorAddress" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="vendorAddress" name="vendorAddress" value="{{ count($errors) > 0 ? old('vendorAddress') : $vendorAddress }}" readonly>
                                            @if($errors->has('vendorAddress'))
                                            <span class="help-block">{{ $errors->first('vendorAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('descriptionHeader') ? 'has-error' : '' }}">
                                        <label for="descriptionHeader" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="descriptionHeader" name="descriptionHeader" rows="3" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>{{ count($errors) > 0 ? old('descriptionHeader') : $model->description }}</textarea>
                                            @if($errors->has('descriptionHeader'))
                                            <span class="help-block">{{ $errors->first('descriptionHeader') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        @if($errors->has('invoiceDate'))
                                        <span class="help-block">{{ $errors->first('invoiceDate') }}</span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.cabang') }}</label>
                                        <div class="col-sm-8">
                                            <?php $branch = $model->branch()->first() !== null ? $model->branch()->first() : Session::get('currentBranch'); ?>
                                            <input type="text" class="form-control" id="branch" name="branch"  value="{{ $branch->branch_name }}" readonly>
                                        </div>
                                    </div>
                                    <?php 
                                    $invoice = InvoiceHeader::find($model->header_id);
                                    $totalAmount = !empty($invoice) ? $invoice->getTotalAmount() : 0; 
                                    $totalTax = !empty($invoice) ? $invoice->getTotalTax() : 0; 
                                    $totalInterest = !empty($invoice) ? $invoice->getTotalInterest() : 0; 
                                    $totalInvoice = !empty($invoice) ? $invoice->getTotalInvoice() : 0; 
                                    ?>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('shared/common.total-amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $totalAmount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalInterest" class="col-sm-4 control-label">{{ trans('shared/common.total-interest') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalInterest" name="totalInterest" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalInterest')) : $totalInterest }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalTax" class="col-sm-4 control-label">{{ trans('shared/common.total-tax') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalTax" name="totalTax" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalTax')) : $totalTax }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('shared/common.total-invoice') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalInvoice" name="totalInvoice" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalInvoice')) : $totalInvoice }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if($model->status == InvoiceHeader::INCOMPLETE)
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line disabled" data-toggle="modal" data-target="#modal-form-line">
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
                                                    <th>{{ trans('purchasing/fields.po-number') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th>{{ trans('payable/fields.interest') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                    <th>{{ trans('payable/fields.tax') }}</th>
                                                    <th>{{ trans('payable/fields.amount-tax') }}</th>
                                                    <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('poNumber')[$i] }} </td>
                                                    <td > {{ old('description')[$i] }} </td>
                                                    <td class="text-right"> {{ old('interestBank')[$i] }} </td>
                                                    <td class="text-right"> {{ old('amount')[$i] }} </td>
                                                    <td class="text-right"> {{ old('tax')[$i] === null ? old('tax')[$i] : 0 }} </td>
                                                    <td class="text-right"> {{ old('fixAmount')[$i] }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == InvoiceHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="poHeaderId[]" value="{{ old('poHeaderId')[$i] }}">
                                                        <input type="hidden" name="poNumber[]" value="{{ old('poNumber')[$i] }}">
                                                        <input type="hidden" name="downPayment[]" value="{{ old('downPayment')[$i] }}">
                                                        <input type="hidden" name="interestBank[]" value="{{ old('interestBank')[$i] }}">
                                                        <input type="hidden" name="accountCodeId[]" value="{{ old('accountCodeId')[$i] }}">
                                                        <input type="hidden" name="accountCode[]" value="{{ old('accountCode')[$i] }}">
                                                        <input type="hidden" name="description[]" value="{{ old('description')[$i] }}">
                                                        <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                        <input type="hidden" name="amountHidden[]" value="{{ old('amountHidden')[$i] }}">
                                                        <input type="hidden" name="fixAmount[]" value="{{ old('fixAmount')[$i] }}">
                                                        <input type="hidden" name="tax[]" value="{{ old('tax')[$i] }}">
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>
                                                @endfor
                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $po = App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader::find($line->po_header_id);
                                                    $dp = App\Modules\Payable\Model\Transaction\DpInvoice::find($line->po_header_id);
                                                    $combination = App\Modules\Generalledger\Model\Master\MasterAccountCombination::find($line->account_comb_id);
                                                    $account = $combination->account;
                                                    $fixAmount    = $line->amount + ($line->tax / 100 * $line->amount);
                                                    $amountHidden = $line->amount + $line->interest_bank ;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $po !== null ? $po->po_number : '' }} </td>
                                                    <td > {{ $line->description }} </td>
                                                    <td class="text-right"> {{ number_format($line->interest_bank) }} </td>
                                                    <td class="text-right"> {{ number_format($line->amount) }} </td>
                                                    <td class="text-right"> {{ number_format($line->tax) }} </td>
                                                    <td class="text-right"> {{ number_format($fixAmount) }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == InvoiceHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->line_id }}">
                                                        <input type="hidden" name="poHeaderId[]" value="{{ $po !== null ? $po->header_id : '' }}">
                                                        <input type="hidden" name="poNumber[]" value="{{ $po !== null ? $po->po_number : '' }}">
                                                        <input type="hidden" name="downPayment[]" value="{{ $dp !== null ? $dp->total_amount : '' }}">
                                                        <input type="hidden" name="interestBank[]" value="{{ $line->interest_bank }}">
                                                        <input type="hidden" name="accountCodeId[]" value="{{ $account !== null ? $account->coa_id : '' }}">
                                                        <input type="hidden" name="accountCode[]" value="{{ $account !== null ? $account->coa_code : '' }}">
                                                        <input type="hidden" name="description[]" value="{{ $line->description }}">
                                                        <input type="hidden" name="amount[]" value="{{ number_format($line->amount) }}">
                                                        <input type="hidden" name="amountHidden[]" value="{{ number_format($amountHidden) }}">
                                                        <input type="hidden" name="fixAmount[]" value="{{ number_format($fixAmount) }}">
                                                        <input type="hidden" name="tax[]" value="{{ $line->tax }}">
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>

                                                @endforeach
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
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-form-line" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                {{ csrf_field() }}
                                <div class="col-sm-12 portlets">
                                    <input type="hidden" name="dataIndexForm" id="dataIndexForm" value="">
                                    <input type="hidden" name="lineId" id="lineId" value="">
                                    <div id="formPO" class="form-group {{ $errors->has('po') ? 'has-error' : '' }}">
                                        <label for="po" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }} <span id="spanPO" class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="poHeaderId" id="poHeaderId" value="">
                                                <input type="text" class="form-control" id="poNumber" name="poNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-po"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="formDP" class="form-group">
                                        <label for="downPayment" class="col-sm-4 control-label">{{ trans('payable/fields.down-payment') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="downPayment" name="downPayment" value="" readonly>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="description" name="description" value="">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="formInterest" class="form-group">
                                        <label for="interestBank" class="col-sm-4 control-label">{{ trans('payable/fields.interest') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="interestBank" name="interestBank" value="" >
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-4 control-label">{{ trans('purchasing/fields.amount') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control currency" id="amountHidden" name="amountHidden" value="" >
                                            <input type="text" class="form-control currency" id="amount" name="amount" value="" >
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="tax" class="col-sm-4 control-label">{{ trans('payable/fields.tax') }} </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="tax" name="tax">
                                                <option value="">{{ trans('shared/common.please-select') }} {{ trans('payable/fields.tax') }}</option>
                                                @foreach($optionTax as $tax)
                                                <option value="{{ $tax }}" >{{ $tax }} %</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label for="fixAmount" class="col-sm-4 control-label">{{ trans('payable/fields.total-amount') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="fixAmount" name="fixAmount" value="" disabled>
                                            <span class="help-block"></span>
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
<div id="modal-lov-vendor" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.vendor') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchVendor" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchVendor" name="searchVendor">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-vendor" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('payable/fields.vendor-code') }}</th>
                                    <th>{{ trans('payable/fields.vendor-name') }}</th>
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
<div id="modal-lov-po" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.po') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchPo" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchPo" name="searchPo">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-po" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('purchasing/fields.po-number') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('inventory/fields.vendor-code') }}</th>
                                    <th>{{ trans('inventory/fields.vendor-name') }}</th>
                                    <th>{{ trans('shared/common.total-amount') }}</th>
                                    <th>{{ trans('shared/common.total-dp') }}</th>
                                    <th>{{ trans('shared/common.total-remain') }}</th>
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
    var dataIndex = {{ $dataIndex }};
    $(document).on('ready', function(){

        $('#show-lov-vendor').on('click', showLovVendor);
        $('#searchVendor').on('keyup', loadLovVendor);
        $('#table-lov-vendor tbody').on('click', 'tr', selectVendor);

        $('.add-line').on('click', addLine);
        $('#show-lov-po').on('click', showLovPo);
        $('#searchPo').on('keyup', loadLovPo);
        $('#table-lov-po tbody').on('click', 'tr', selectPo);
        $("#interestBank").on('keyup', calculateAmountLine);
        $("#tax").on('change', calculateAmountLine);
        $("#amount").on('keyup', calculateAmountLine);
        $("#save-line").on('click', saveLine);
        $('.edit-line').on('click', editLine);
        $('.delete-line').on('click', deleteLine);

        $('#type').on('change', function(){
            clearLines();
            clearFormLine();
            disableForm();
        });

        disableForm();
    });

    var disableForm = function() {
            if ($('#type').val() != '' && $('#vendorName').val() != '' && $('#vendorId').val() != '' ){
                $('.add-line').removeClass('disabled');
            }else{
                $('.add-line').addClass('disabled');
            }
            $('#formInterest').addClass('hidden');
            $('#amount').attr('readonly','readonly');

            if ($('#type').val() == {{InvoiceHeader::PURCHASE_ORDER}}) { // Tagihan PO Standart
                $('#amount').removeAttr('readonly','readonly');
            }
            if ($('#type').val() == {{InvoiceHeader::PURCHASE_ORDER_CREDIT}}) { // Tagihan PO Cicilan
                $('#formInterest').removeClass('hidden');
            }
        };

    var showLovVendor = function() {
        $('#searchVendor').val('');
        loadLovVendor(function() {
            $('#modal-lov-vendor').modal('show');
        });
    };

    var xhrVendor;
    var loadLovVendor = function(callback) {
        if(xhrVendor && xhrVendor.readyState != 4){
            xhrVendor.abort();
        }
        xhrVendor = $.ajax({
            url: '{{ URL($url.'/get-json-vendor') }}',
            data: {search: $('#searchVendor').val()},
            success: function(data) {
                $('#table-lov-vendor tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-vendor tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.vendor_code + '</td>\
                            <td>' + item.vendor_name + '</td>\
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

    var selectVendor = function() {
        var data = $(this).data('json');
        $('#vendorId').val(data.vendor_id);
        $('#vendorName').val(data.vendor_name);
        $('#vendorAddress').val(data.address);
        $('#table-line tbody').html('');

        clearLines();
        disableForm();

        $('#modal-lov-vendor').modal('hide');
    };

    var showLovPo = function() {
        $('#searchPo').val('');
        loadLovPo(function() {
            $('#modal-lov-po').modal('show');
        });
    };

    var xhrPo;
    var loadLovPo = function(callback) {
        if(xhrPo && xhrPo.readyState != 4){
            xhrPo.abort();
        }
        xhrPo = $.ajax({
            url: '{{ URL($url.'/get-json-po') }}',
            data: {search: $('#searchPo').val(), vendorId: $('#vendorId').val(), id: $('#id').val(), type: $('#type').val()},
            success: function(data) {
                $('#table-lov-po tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-po tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.po_number + '</td>\
                            <td>' + item.description + '</td>\
                            <td>' + item.vendor_code + '</td>\
                            <td>' + item.vendor_name + '</td>\
                            <td class="text-right">' + parseInt(item.total_amount).formatMoney(0) + '</td>\
                            <td class="text-right">' + parseInt(item.total_dp).formatMoney(0) + '</td>\
                            <td class="text-right">' + parseInt(item.total_remain).formatMoney(0) + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectPo = function() {
        var data = $(this).data('json');

        var error = false
        $('#table-line tbody tr').each(function (i, row) {
            if (data.header_id == $(row).find('[name="poHeaderId[]"]').val()) {
                $('#modal-alert').find('.alert-message').html('Po already exist');
                $('#modal-alert').modal('show');
                error = true;
            }
        });

        if (error) {
            return;
        }

        $('#poHeaderId').val(data.header_id);
        $('#poNumber').val(data.po_number);
        $('#downPayment').val(parseInt(data.dp).formatMoney(0));
        $('#description').val(data.description);
        $('#amount').val(parseInt(data.total_remain).formatMoney(0));
        $('#tax').val(data.tax);
        calculateAmountLine();

        $('#modal-lov-po').modal('hide');
    };

    var addLine = function() {
        clearFormLine();
        $('#title-modal-line').html('{{ trans('shared/common.add') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.add') }}');
        $('#modal-line').modal('show');
        calculateAmountLine();
    };

    var saveLine = function() {
        var type           = $('#type').val();
        var dataIndexForm  = $('#dataIndexForm').val();
        var lineId         = $('#lineId').val();
        var poHeaderId     = $('#poHeaderId').val();
        var poNumber       = $('#poNumber'  ).val();
        var downPayment    = $('#downPayment'  ).val();
        var interestBank   = $('#interestBank'  ).val();
        var description    = $('#description').val();
        var amount         = $('#amount').val();
        var fixAmount      = $('#fixAmount').val();
        var tax            = $('#tax').val();
        var accountCodeId  = $('#accountCodeId').val();
        var accountCode    = $('#accountCode').val();
        var amountHidden   = amount + interestBank;;
      
        var error = false;

        if (description == '') { // all
            $('#description').parent().parent().addClass('has-error');
            $('#description').parent().find('span.help-block').html('Description is required');
            error = true;
        } else {
            $('#description').parent().parent().removeClass('has-error');
            $('#description').parent().find('span.help-block').html('');
        }

        if (amount == '' || amount <= 0) {
            $('#amount').parent().parent().addClass('has-error');
            $('#amount').parent().find('span.help-block').html('Amount is required');
            error = true;
        } else {
            $('#amount').parent().parent().removeClass('has-error');
            $('#amount').parent().find('span.help-block').html('');
        }
        
        if (type == '{{InvoiceHeader::PURCHASE_ORDER}}' && (poHeaderId == '' || poNumber == '')) { // tagihan PO Standart
            $('#poNumber').parent().parent().parent().addClass('has-error');
            $('#poNumber').parent().parent().find('span.help-block').html('Choose PO first');
            error = true;
        } else {
            $('#poNumber').parent().parent().parent().removeClass('has-error');
            $('#poNumber').parent().parent().find('span.help-block').html('');
        }

        if (type == '{{InvoiceHeader::PURCHASE_ORDER_CREDIT}}' && (poHeaderId == '' || poNumber == '')) { // tagihan PO cicilan
            $('#poNumber').parent().parent().parent().addClass('has-error');
            $('#poNumber').parent().parent().find('span.help-block').html('Choose PO first');
            error = true;
        } else {
            $('#poNumber').parent().parent().parent().removeClass('has-error');
            $('#poNumber').parent().parent().find('span.help-block').html('');
        }

        if (type == '{{InvoiceHeader::PURCHASE_ORDER_CREDIT}}' && (interestBank == '' || interestBank <= 0 )) { // tagihan PO Cicilan
            $('#interestBank').parent().parent().addClass('has-error');
            $('#interestBank').parent().find('span.help-block').html('Interst is required');
            error = true;
        } else {
            $('#interestBank').parent().parent().removeClass('has-error');
            $('#interestBank').parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        if (tax ===null) { tax = 0;}

        var htmlTr = '<td >' + poNumber + '</td>' +
            '<td >' + description + '</td>' +
            '<td class="text-right">' + interestBank + '</td>' +
            '<td class="text-right">' + amount + '</td>' +
            '<td class="text-right">' + tax + '</td>' +
            '<td class="text-right">' + fixAmount + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="poHeaderId[]" value="' + poHeaderId + '">' +
            '<input type="hidden" name="poNumber[]" value="' + poNumber + '">' +
            '<input type="hidden" name="downPayment[]" value="' + downPayment + '">' +
            '<input type="hidden" name="interestBank[]" value="' + interestBank + '">' +
            '<input type="hidden" name="accountCodeId[]" value="' + accountCodeId + '">' +
            '<input type="hidden" name="accountCode[]" value="' + accountCode + '">' +
            '<input type="hidden" name="description[]" value="' + description + '">' +
            '<input type="hidden" name="amount[]" value="' + amount + '">' +
            '<input type="hidden" name="fixAmount[]" value="' + fixAmount + '">' +
            '<input type="hidden" name="amountHidden[]" value="' + amountHidden + '">' +
            '<input type="hidden" name="tax[]" value="' + tax + '">' +
            '</td>';

        if (dataIndexForm != '') {
            $('tr[data-index="' + dataIndexForm + '"]').html(htmlTr);
            dataIndex++;
        } else {
            $('#table-line tbody').append(
                '<tr data-index="' + dataIndex + '">' + htmlTr + '</tr>'
            );
            dataIndex++;
        }

        $('.edit-line').on('click', editLine);
        $('.delete-line').on('click', deleteLine);

        clearFormLine();
        calculateTotal();

        dataIndex++;
        $('#modal-form-line').modal("hide");
    };

    var editLine = function() {
        var dataIndexForm  = $(this).parent().parent().data('index');
        var lineId         = $(this).parent().parent().find('[name="lineId[]"]').val();
        var poHeaderId     = $(this).parent().parent().find('[name="poHeaderId[]"]').val();
        var poNumber       = $(this).parent().parent().find('[name="poNumber[]"]').val();
        var downPayment    = $(this).parent().parent().find('[name="downPayment[]"]').val();
        var interestBank   = $(this).parent().parent().find('[name="interestBank[]"]').val();
        var description    = $(this).parent().parent().find('[name="description[]"]').val();
        var accountCodeId  = $(this).parent().parent().find('[name="accountCodeId[]"]').val();
        var accountCode    = $(this).parent().parent().find('[name="accountCode[]"]').val();
        var tax            = $(this).parent().parent().find('[name="tax[]"]').val();
        var amount         = $(this).parent().parent().find('[name="amount[]"]').val();
        var amountHidden   = $(this).parent().parent().find('[name="amountHidden[]"]').val();
        var fixAmount      = $(this).parent().parent().find('[name="fixAmount[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#poHeaderId').val(poHeaderId);
        $('#poNumber').val(poNumber);
        $('#downPayment').val(downPayment);
        $('#interestBank').val(interestBank);
        $('#description').val(description);
        $('#accountCodeId').val(accountCodeId);
        $('#accountCode').val(accountCode);
        $('#amount').val(amount);
        $('#amountHidden').val(amountHidden);
        $('#fixAmount').val(fixAmount);
        $('#tax').val(tax);
   
        $('#amount').autoNumeric('update', {mDec: 0});
        $('#interestBank').autoNumeric('update', {mDec: 0});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var calculateAmountLine = function() {
        var amount = currencyToInt($('#amount').val());
        var tax = currencyToInt($('#tax').val());
        var interestBank = currencyToInt($('#interestBank').val());
        var fixAmount = amount + (tax * amount / 100) + interestBank;

        $('#amount').val(amount.formatMoney(0));
        $('#tax').val(tax.formatMoney(0));
        $('#interestBank').val(interestBank.formatMoney(0));
        $('#fixAmount').val(fixAmount.formatMoney(0));
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
        calculateTotal();
    };

    var clearLines = function() {
        $('#table-line tbody').html('');
        calculateTotal();
    };

    var calculateTotal = function() {
        var totalAmount = 0;
        var totalTax = 0;
        var totalInterest = 0;
        var totalInvoice = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var amount = parseFloat($(row).find('[name="amount[]"]').val().split(',').join(''));
            var fixAmount = parseFloat($(row).find('[name="fixAmount[]"]').val().split(',').join(''));
            var interest = parseFloat($(row).find('[name="interestBank[]"]').val().split(',').join(''));
            var tax = parseFloat($(row).find('[name="tax[]"]').val().split(',').join(''));
            totalAmount   += amount;
            totalInterest += interest;
            totalTax      += amount * tax / 100;
            totalInvoice  += fixAmount;
        });

        $('#totalAmount').val(totalAmount);
        $('#totalAmount').autoNumeric('update', {mDec: 0});
        $('#totalInterest').val(totalInterest);
        $('#totalInterest').autoNumeric('update', {mDec: 0});
        $('#totalTax').val(totalTax);
        $('#totalTax').autoNumeric('update', {mDec: 0});
        $('#totalInvoice').val(totalInvoice);
        $('#totalInvoice').autoNumeric('update', {mDec: 0});
    };

    var clearFormLine = function() {
        $('#dataIndexForm').val('');
        $('#lineId').val('');
        $('#poHeaderId').val('');
        $('#poNumber').val('');
        $('#downPayment').val('');
        $('#interestBank').val('');
        $('#accountCodeId').val();
        $('#accountCode').val('');
        $('#description').val('');
        $('#amount').val('');
        $('#fixAmount').val('');
        $('#tax').val('');

        $('#poNumber').parent().parent().parent().removeClass('has-error');
        $('#poNumber').parent().parent().find('span.help-block').html('');
        $('#downPayment').parent().parent().parent().removeClass('has-error');
        $('#downPayment').parent().parent().find('span.help-block').html('');
        $('#interestBank').parent().parent().parent().removeClass('has-error');
        $('#interestBank').parent().parent().find('span.help-block').html('');
        $('#description').parent().parent().removeClass('has-error');
        $('#description').parent().find('span.help-block').html('');
        $('#amount').parent().parent().removeClass('has-error');
        $('#amount').parent().find('span.help-block').html('');
        $('#fixAmount').parent().parent().removeClass('has-error');
        $('#fixAmount').parent().find('span.help-block').html('');
    };

</script>
@endsection
