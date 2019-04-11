<?php 
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@extends('layouts.master')

@section('title', trans('payable/menu.other-invoice'))

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
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.other-invoice') }}</h2>
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
                                            <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber"  value="{{ $model->invoice_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status"  value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                    <?php $typeId = $model->type_id ?>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" disabled>
                                                <option value="">{{ trans('shared/common.please-select') }} {{ trans('shared/common.type') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type->type_id }}" {{ $type->type_id == $typeId ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                                @endforeach
                                            </select>
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
                                    <div class="form-group">
                                        <label for="vendorId" class="col-sm-4 control-label">{{ trans('payable/fields.vendor') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="vendorCode" name="vendorCode" value="{{ $vendorCode }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="vendorName" class="col-sm-4 control-label">{{ trans('shared/common.name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control " id="vendorName" name="vendorName" value="{{ $vendorName }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $address }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="descriptionHeader" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="descriptionHeader" name="descriptionHeader" rows="3" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php $branch = $model->branch()->first() !== null ? $model->branch()->first() : Session::get('currentBranch'); ?>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.cabang') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="branch" name="branch"  value="{{ $branch->branch_name }}" disabled>
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
                                            <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ $totalAmount }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalTax" class="col-sm-4 control-label">{{ trans('shared/common.total-tax') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalTax" name="totalTax" value="{{ $totalTax }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('shared/common.total-invoice') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalInvoice" name="totalInvoice" value="{{ $totalInvoice }}" disabled>
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
                                                @foreach($model->lines()->get() as $line)
                                                    <?php
                                                        $po = App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader::find($line->po_header_id);
                                                        $dp = App\Modules\Payable\Model\Transaction\DpInvoice::find($line->po_header_id);
                                                        $combination = App\Modules\Generalledger\Model\Master\MasterAccountCombination::find($line->account_comb_id);
                                                        $account = $combination->account;
                                                        $fixAmount       = $line->amount + ($line->tax / 100 * $line->amount);
                                                        $amountHidden = $line->amount + $line->interest_bank ;
                                                    ?>
                                                    <tr>
                                                        <td > {{ $account !== null ? $account->coa_code : '' }} </td>
                                                        <td > {{ $line->description }} </td>
                                                        <td class="text-right"> {{ number_format($line->amount) }} </td>
                                                        <td class="text-right"> {{ number_format($line->tax) }} </td>
                                                        <td class="text-right"> {{ number_format($fixAmount) }} </td>
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

                                @if($model->status == InvoiceHeader::APPROVED || $model->status == InvoiceHeader::CLOSED)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->header_id) }}" class="button btn btn-sm btn-success" target="_blank">
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
