@extends('layouts.master')

<?php
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@section('title', trans('purchasing/menu.purchase-order-outstanding'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('purchasing/menu.purchase-order-outstanding') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ !empty($filters['poNumber']) ? $filters['poNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('purchasing/fields.supplier') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="supplier" name="supplier">
                                        <option value="">ALL</option>
                                        @foreach($optionsSupplier as $supplier)
                                        <option value="{{ $supplier->vendor_id }}" {{ !empty($filters['supplier']) && $filters['supplier'] == $supplier->vendor_id ? 'selected' : '' }}>{{ $supplier->vendor_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('purchasing/fields.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <option value="">ALL</option>
                                        @foreach($optionsType as $type)
                                        <option value="{{ $type->type_id }}" {{ !empty($filters['type']) && $filters['type'] == $type->type_id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionsStatus as $status)
                                        <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">{{ trans('shared/common.date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateFrom']) ? $filters['dateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateTo']) ? $filters['dateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <h4>Oustanding Quantity</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('purchasing/fields.po-number') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('purchasing/fields.supplier') }}</th>
                                <th>{{ trans('purchasing/fields.item') }}</th>
                                <th>{{ trans('purchasing/fields.item-description') }}</th>
                                <th>{{ trans('purchasing/fields.uom') }}</th>
                                <th>{{ trans('purchasing/fields.qty-need') }}</th>
                                <th>{{ trans('purchasing/fields.price') }}</th>
                                <th>{{ trans('purchasing/fields.total-price') }}</th>
                                <th>{{ trans('purchasing/fields.remain') }}</th>
                                <th>{{ trans('purchasing/fields.receiving') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modelsQty as $model)
                            <?php
                            $no = 1;
                            $poDate = !empty($model->po_date) ? new \DateTime($model->po_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->po_number }}</td>
                                <td>{{ $poDate !== null ? $poDate->format('d-m-Y') : '' }}</td>
                                <td>{{ $model->vendor_name }}</td>
                                <td>{{ $model->item_code }}</td>
                                <td>{{ $model->item_description }}</td>
                                <td>{{ $model->uom_code }}</td>
                                <td class="text-right">{{ number_format($model->quantity_need) }}</td>
                                <td class="text-right">{{ number_format($model->unit_price) }}</td>
                                <td class="text-right">{{ number_format($model->total_price) }}</td>
                                <td class="text-right">{{ number_format($model->quantity_remain) }}</td>
                                <td class="text-right">{{ number_format($model->quantity_need - $model->quantity_remain) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <hr>
                    <h4>Oustanding Invoice</h4>
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('purchasing/fields.po-number') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('payable/fields.invoice-number') }}</th>
                                <th>{{ trans('purchasing/fields.supplier') }}</th>
                                <th>{{ trans('payable/fields.total-invoice') }}</th>
                                <th>{{ trans('payable/fields.total-payment') }}</th>
                                <th>{{ trans('payable/fields.total-remain') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php 
                                $no = 1; 
                                $totalInvoice = 0; 
                                $totalPayment = 0; 
                                $totalRemain = 0; 
                                ?>
                                @foreach($modelsInvoice as $invoice)
                                <?php 
                                $date = !empty($invoice->created_date) ? new \DateTime($invoice->created_date) : null;
                                $model = InvoiceHeader::find($invoice->header_id); 
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $invoice->po_number }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->vendor_name }}</td>
                                    <td class="text-right">{{ number_format($model->getTotalInvoice()) }}</td>
                                    <td class="text-right">{{ number_format($model->getTotalPayment()) }}</td>
                                    <td class="text-right">{{ number_format($model->getTotalRemain()) }}</td>
                                </tr>
                                <?php 
                                $totalInvoice += $model->getTotalInvoice(); 
                                $totalPayment += $model->getTotalPayment(); 
                                $totalRemain += $model->getTotalRemain(); 
                                ?>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalInvoice) }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalPayment) }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalRemain) }}</strong></td>
                                </tr>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

