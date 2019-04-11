@extends('layouts.master')

@section('title', trans('payable/menu.purchase-order-credit'))

<?php 
    use App\Service\Terbilang;
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
 ?>
@section('header')
@parent
<style type="text/css">
    .modal-data-title{
        background-color: #eee;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('payable/menu.purchase-order') }}</h2>
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
                        <div class="col-sm-6 portlets">
                            <div class="form-group" id="formDriver">
                                <label for="supplier" class="col-sm-4 control-label">{{ trans('payable/fields.supplier') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="supplierId" name="supplierId" value="{{ !empty($filters['supplierId']) ? $filters['supplierId'] : '' }}">
                                        <input type="text" class="form-control" id="supplierName" name="supplierName" value="{{ !empty($filters['supplierName']) ? $filters['supplierName'] : '' }}" readonly>
                                        <span class="btn input-group-addon" id="modalSupplier" data-toggle="modal" data-target="#modal-supplier"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('supplierName'))
                                    <span class="help-block">{{ $errors->first('supplierName') }}</span>
                                    @endif
                                </div>
                            </div>
                             <div class="form-group">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="address" name="address" class="form-control" value="{{ !empty($filters['address']) ? $filters['address'] : '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="description" name="description" class="form-control" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
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
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>    
                                    <th>{{ trans('payable/fields.invoice-number') }}</th>
                                    <th>{{ trans('purchasing/fields.po-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
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
                                @foreach($invoices as $invoice)
                                <?php 
                                $date = !empty($invoice->created_date) ? new \DateTime($invoice->created_date) : null;
                                $model = InvoiceHeader::find($invoice->header_id); 
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $model->getPoNumber() }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
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
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalInvoice) }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalPayment) }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalRemain) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>   
                </div>
                <div class="col-sm-12">
                    <strong>Remain    : {{ number_format($totalRemain) }}</strong><br>
                    <strong>Terbilang : {{ $totalRemain < 0 ? '(Min)' : '' }} {{ trim(ucwords(Terbilang::rupiah(abs($totalRemain)))) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-invoice" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Invoice List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-invoice" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('payable/fields.invoice-number') }}</th>
                            <th>{{ trans('purchasing/fields.po-number') }}</th>
                            <th>{{ trans('payable/fields.vendor-name') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('payable/fields.total-invoice') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionInvoice as $invoice)
                        <tr style="cursor: pointer;" data-invoice="{{ json_encode($invoice) }}">
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->po_number }}</td>
                            <td>{{ $invoice->vendor_name }}</td>
                            <td>{{ $invoice->description }}</td>
                            <td class="text-right">{{ number_format($invoice->total_invoice) }}</td>
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
<div id="modal-supplier" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Supplier List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-supplier" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('payable/fields.vendor-code') }}</th>
                            <th>{{ trans('payable/fields.supplier') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.phone') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionSupplier as $supplier)
                        <tr style="cursor: pointer;" data-supplier="{{ json_encode($supplier) }}">
                            <td>{{ $supplier->vendor_code }}</td>
                            <td>{{ $supplier->vendor_name }}</td>
                            <td>{{ $supplier->address }}</td>
                            <td>{{ $supplier->phone_number }}</td>
                            <td>{{ $supplier->description }}</td>
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
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $("#datatables-invoice").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-invoice tbody').on('click', 'tr', function () {
            var invoice = $(this).data('invoice');

            $('#invoiceId').val(invoice.header_id);
            $('#invoiceNumber').val(invoice.invoice_number);
            $('#poNumber').val(invoice.po_number);
            $('#totalInvoice').val(invoice.total_invoice.formatMoney(0));

            $('#modal-invoice').modal('hide');
        });

        $("#datatables-supplier").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-supplier tbody').on('click', 'tr', function () {
            var supplier = $(this).data('supplier');

            $('#supplierId').val(supplier.vendor_id);
            $('#supplierName').val(supplier.vendor_name);
            $('#address').val(supplier.address);
            $('#description').val(supplier.description);

            $('#modal-supplier').modal('hide');
        });
    });
</script>
@endsection
