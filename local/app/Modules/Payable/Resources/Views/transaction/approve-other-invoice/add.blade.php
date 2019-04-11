@extends('layouts.master')

@section('title', trans('payable/menu.approve-other-invoice'))

<?php 
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>
@section('header')
@parent
<style type="text/css">
    #table-lov-vendor tbody tr{
        cursor: pointer;
    }
    #table-lov-driver tbody tr{
        cursor: pointer;
    }
    #table-lov-account tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.approve-other-invoice') }}</h2>
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
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabApprove">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                        <label for="poNumber" class="col-sm-4 control-label">Note about Approve or Reject <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="note" name="note" rows="4">{{ count($errors) > 0 ? old('note') : '' }}</textarea>
                                            @if($errors->has('note'))
                                            <span class="help-block">{{ $errors->first('note') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
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
                                    if ($model->type_id == InvoiceHeader::OTHER_VENDOR) {
                                        $vendor     = $model->vendor;
                                        $vendorId   = !empty($vendor) ? $vendor->vendor_id : '';
                                        $vendorCode = !empty($vendor) ? $vendor->vendor_code : '';
                                        $vendorName = !empty($vendor) ? $vendor->vendor_name : '';
                                        $address    = !empty($vendor) ? $vendor->address : '';
                                    }elseif($model->type_id == InvoiceHeader::OTHER_DRIVER) {
                                        $driver     = $model->driver;
                                        $vendorId   = !empty($driver) ? $driver->driver_id : '';
                                        $vendorCode = !empty($driver) ? $driver->driver_code : '';
                                        $vendorName = !empty($driver) ? $driver->driver_name : '';
                                        $address    = !empty($driver) ? $driver->address : '';
                                    }else{
                                        $vendorId   = '';
                                        $vendorCode = '';
                                        $vendorName = '';
                                        $address    = '';
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('vendorId') ? 'has-error' : '' }}">
                                        <label for="vendorId" class="col-sm-4 control-label">{{ trans('payable/fields.vendor') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" class="form-control" id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $vendorId }}" readonly>
                                                <input type="text" class="form-control" id="vendorCode" name="vendorCode" value="{{ count($errors) > 0 ? old('vendorCode') : $vendorCode }}" readonly>
                                                <span  class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'modalVendor' : '' }}"><i class="fa fa-search"></i></span>
                                                @if($model->isIncomplete())
                                                <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'modalDriver' : '' }}"><i class="fa fa-search"></i></span>
                                                @endif
                                            </div>
                                            @if($errors->has('vendorId'))
                                            <span class="help-block">{{ $errors->first('vendorId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="vendorName" class="col-sm-4 control-label">{{ trans('shared/common.name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control " id="vendorName" name="vendorName" value="{{ count($errors) > 0 ? old('vendorName') : $vendorName }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $address }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('descriptionHeader') ? 'has-error' : '' }}">
                                        <label for="descriptionHeader" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
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
                                    $totalInvoice = !empty($invoice) ? $invoice->getTotalInvoice() : 0; 
                                    ?>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('shared/common.total-amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $totalAmount }}" readonly>
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
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('shared/common.account-code') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                    <th>{{ trans('payable/fields.tax') }}</th>
                                                    <th>{{ trans('payable/fields.amount-tax') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('accountCode')[$i] }} </td>
                                                    <td > {{ old('description')[$i] }} </td>
                                                    <td class="text-right"> {{ old('amount')[$i] }} </td>
                                                    <td class="text-right"> {{ old('tax')[$i] === null ? old('tax')[$i] : 0 }} </td>
                                                    <td class="text-right"> {{ old('fixAmount')[$i] }} </td>
                                                    <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                    <input type="hidden" name="accountCodeId[]" value="{{ old('accountCodeId')[$i] }}">
                                                    <input type="hidden" name="accountCode[]" value="{{ old('accountCode')[$i] }}">
                                                    <input type="hidden" name="description[]" value="{{ old('description')[$i] }}">
                                                    <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                    <input type="hidden" name="amountHidden[]" value="{{ old('amountHidden')[$i] }}">
                                                    <input type="hidden" name="fixAmount[]" value="{{ old('fixAmount')[$i] }}">
                                                    <input type="hidden" name="remain[]" value="{{ old('remain')[$i] }}">
                                                    <input type="hidden" name="tax[]" value="{{ old('tax')[$i] }}">
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
                                                    $fixAmount       = $line->amount + ($line->tax / 100 * $line->amount);
                                                    $amountHidden = $line->amount + $line->interest_bank ;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $account !== null ? $account->coa_code : '' }} </td>
                                                    <td > {{ $line->description }} </td>
                                                    <td class="text-right"> {{ number_format($line->amount) }} </td>
                                                    <td class="text-right"> {{ number_format($line->tax) }} </td>
                                                    <td class="text-right"> {{ number_format($fixAmount) }} </td>
                                                    <input type="hidden" name="lineId[]" value="{{ $line->line_id }}">
                                                    <input type="hidden" name="accountCodeId[]" value="{{ $account !== null ? $account->coa_id : '' }}">
                                                    <input type="hidden" name="accountCode[]" value="{{ $account !== null ? $account->coa_code : '' }}">
                                                    <input type="hidden" name="description[]" value="{{ $line->description }}">
                                                    <input type="hidden" name="amount[]" value="{{ number_format($line->amount) }}">
                                                    <input type="hidden" name="amountHidden[]" value="{{ number_format($amountHidden) }}">
                                                    <input type="hidden" name="fixAmount[]" value="{{ number_format($fixAmount) }}">
                                                    <input type="hidden" name="remain[]" value="{{$po !== null ? $po->getTotalRemain() : ''}}">
                                                    <input type="hidden" name="tax[]" value="{{ $line->tax }}">
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
                                @if(Gate::check('access', [$resource, 'reject']) && $model->isInprocess())
                                <button type="submit" name="btn-reject" class="btn btn-sm btn-danger">
                                    <i class="fa fa-remove"></i> {{ trans('shared/common.reject') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->isInprocess())
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
                                    <div id="formAccount" class="form-group {{ $errors->has('account') ? 'has-error' : '' }}">
                                        <label for="po" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-code') }} <span id="spanPO" class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="accountCodeId" id="accountCodeId" value="">
                                                <input type="text" class="form-control" id="accountCode" name="accountCode" readonly>
                                                <span class="btn input-group-addon" id="show-lov-account"><i class="fa fa-search"></i></span>
                                            </div>
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
                                        <label for="fixAmount" class="col-sm-4 control-label">{{ trans('purchasing/fields.amount') }} <span class="required">*</span></label>
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
                                    <th>{{ trans('shared/common.category') }}</th>
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
                                    <th>{{ trans('shared/common.position') }}</th>
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


<div id="modal-lov-account" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.account-code') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchAccount" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchAccount" name="searchAccount">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-account" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
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

        enableModal();
        $("#type").on('change', function(){
            clearForm();
            enableModal();
        });

        $('#modalVendor').on('click', showLovVendor);
        $('#searchVendor').on('keyup', loadLovVendor);
        $('#table-lov-vendor tbody').on('click', 'tr', selectVendor);

        $('#modalDriver').on('click', showLovDriver);
        $('#searchDriver').on('keyup', loadLovDriver);
        $('#table-lov-driver tbody').on('click', 'tr', selectDriver);

        $('.add-line').on('click', addLine);
        $('#show-lov-account').on('click', showLovAccount);
        $('#searchAccount').on('keyup', loadLovAccount);
        $('#table-lov-account tbody').on('click', 'tr', selectAccount);
        $("#tax").on('change', calculateAmountLine);
        $("#amount").on('keyup', calculateAmountLine);
        $("#save-line").on('click', saveLine);
        $('.edit-line').on('click', editLine);
        $('.delete-line').on('click', deleteLine);

        $('#type').on('change', function(){
            clearLines();
            clearFormLine();
        });

    });

    var enableModal = function(){
        $('#modalVendor').addClass('disabled');
        $('#modalDriver').addClass('disabled');
        $('#modalVendor').removeClass('hidden');
        $('#modalDriver').addClass('hidden');

        if ($('#type').val() == {{ InvoiceHeader::OTHER_VENDOR }}) { // Kas Bon Employee
            $('#modalVendor').removeClass('disabled');
        }
        else if($('#type').val() == {{ InvoiceHeader::OTHER_DRIVER }}) { // Kas Bon Driver
            $('#modalVendor').addClass('hidden');
            $('#modalDriver').removeClass('disabled');
            $('#modalDriver').removeClass('hidden');
        }
    };

    var clearForm = function(){
        $('#vendorId').val('');
        $('#vendorCode').val('');
        $('#vendorName').val('');
        $('#address').val('');
    };

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
                            <td>' + item.position_meaning + '</td>\
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

    var selectDriver = function() {
        var data = $(this).data('json');
        $('#vendorId').val(data.driver_id);
        $('#vendorCode').val(data.driver_code);
        $('#vendorName').val(data.driver_name);
        $('#address').val(data.address);
        $('#table-line tbody').html('');


        $('#modal-lov-driver').modal('hide');
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
                            <td>' + item.category_meaning + '</td>\
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
        $('#vendorCode').val(data.vendor_code);
        $('#vendorName').val(data.vendor_name);
        $('#address').val(data.address);
        $('#table-line tbody').html('');

        $('#modal-lov-vendor').modal('hide');
    };

    var showLovAccount = function() {
        $('#searchAccount').val('');
        loadLovAccount(function() {
            $('#modal-lov-account').modal('show');
        });
    };

    var xhrAccount;
    var loadLovAccount = function(callback) {
        if(xhrAccount && xhrAccount.readyState != 4){
            xhrAccount.abort();
        }
        xhrAccount = $.ajax({
            url: '{{ URL($url.'/get-json-account') }}',
            data: {search: $('#searchAccount').val(), vendorId: $('#vendorId').val(), id: $('#id').val(), type: $('#type').val()},
            success: function(data) {
                $('#table-lov-account tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-account tbody').append(
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

    var selectAccount = function() {
        var data = $(this).data('json');

        $('#accountCodeId').val(data.coa_id);
        $('#accountCode').val(data.coa_code);
        $('#description').val(data.description);
        $('#tax').val(data.tax);
        calculateAmountLine();

        $('#modal-lov-account').modal('hide');
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
        var description    = $('#description').val();
        var amount         = $('#amount').val();
        var fixAmount      = $('#fixAmount').val();
        var tax            = $('#tax').val();
        var accountCodeId  = $('#accountCodeId').val();
        var accountCode    = $('#accountCode').val();
      
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
        
        if (accountCodeId == '' || accountCode == '') { // tagihan PO Standart
            $('#AccountCode').parent().parent().parent().addClass('has-error');
            $('#AccountCode').parent().parent().find('span.help-block').html('Choose Account Code first');
            error = true;
        } else {
            $('#AccountCode').parent().parent().parent().removeClass('has-error');
            $('#AccountCode').parent().parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        if (tax ===null) { tax = 0;}

        var htmlTr = '<td >' + accountCode + '</td>' +
            '<td >' + description + '</td>' +
            '<td class="text-right">' + amount + '</td>' +
            '<td class="text-right">' + tax + '</td>' +
            '<td class="text-right">' + fixAmount + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="accountCodeId[]" value="' + accountCodeId + '">' +
            '<input type="hidden" name="accountCode[]" value="' + accountCode + '">' +
            '<input type="hidden" name="description[]" value="' + description + '">' +
            '<input type="hidden" name="amount[]" value="' + amount + '">' +
            '<input type="hidden" name="fixAmount[]" value="' + fixAmount + '">' +
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
        var description    = $(this).parent().parent().find('[name="description[]"]').val();
        var accountCodeId  = $(this).parent().parent().find('[name="accountCodeId[]"]').val();
        var accountCode    = $(this).parent().parent().find('[name="accountCode[]"]').val();
        var tax            = $(this).parent().parent().find('[name="tax[]"]').val();
        var amount         = $(this).parent().parent().find('[name="amount[]"]').val();
        var amountHidden   = $(this).parent().parent().find('[name="amountHidden[]"]').val();
        var fixAmount      = $(this).parent().parent().find('[name="fixAmount[]"]').val();
        var remain         = $(this).parent().parent().find('[name="remain[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#description').val(description);
        $('#accountCodeId').val(accountCodeId);
        $('#accountCode').val(accountCode);
        $('#amount').val(amount);
        $('#amountHidden').val(amountHidden);
        $('#fixAmount').val(fixAmount);
        $('#tax').val(tax);
   
        $('#amount').autoNumeric('update', {mDec: 0});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var calculateAmountLine = function() {
        var amount = currencyToInt($('#amount').val());
        var tax = currencyToInt($('#tax').val());
        var fixAmount = amount + (tax * amount / 100);

        $('#amount').val(amount.formatMoney(0));
        $('#tax').val(tax.formatMoney(0));
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
        var totalInvoice = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var amount = parseFloat($(row).find('[name="amount[]"]').val().split(',').join(''));
            var fixAmount = parseFloat($(row).find('[name="fixAmount[]"]').val().split(',').join(''));
            var tax = parseFloat($(row).find('[name="tax[]"]').val().split(',').join(''));
            totalAmount += amount;
            totalTax += amount * tax / 100;
            totalInvoice += fixAmount;
        });

        $('#totalAmount').val(totalAmount);
        $('#totalAmount').autoNumeric('update', {mDec: 0});
        $('#totalTax').val(totalTax);
        $('#totalTax').autoNumeric('update', {mDec: 0});
        $('#totalInvoice').val(totalInvoice);
        $('#totalInvoice').autoNumeric('update', {mDec: 0});
    };

    var clearFormLine = function() {
        $('#dataIndexForm').val('');
        $('#lineId').val('');
        $('#accountCodeId').val();
        $('#accountCode').val('');
        $('#description').val('');
        $('#amount').val('');
        $('#fixAmount').val('');
        $('#tax').val('');

        $('#accountCode').parent().parent().parent().removeClass('has-error');
        $('#accountCode').parent().parent().find('span.help-block').html('');
        $('#description').parent().parent().removeClass('has-error');
        $('#description').parent().find('span.help-block').html('');
        $('#amount').parent().parent().removeClass('has-error');
        $('#amount').parent().find('span.help-block').html('');
        $('#fixAmount').parent().parent().removeClass('has-error');
        $('#fixAmount').parent().find('span.help-block').html('');
    };

</script>
@endsection

