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
                <h2><i class="fa fa-truck"></i> {{ trans('payable/menu.purchase-order-credit') }}</h2>
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
                                <label for="invoice" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="invoiceId" name="invoiceId" value="{{ !empty($filters['invoiceId']) ? $filters['invoiceId'] : '' }}">
                                        <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ !empty($filters['invoiceNumber']) ? $filters['invoiceNumber'] : '' }}" readonly>
                                        <span class="btn input-group-addon" id="modalDriver" data-toggle="modal" data-target="#modal-invoice"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('invoiceNumber'))
                                    <span class="help-block">{{ $errors->first('invoiceNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                             <div class="form-group">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="poNumber" name="poNumber" class="form-control" value="{{ !empty($filters['poNumber']) ? $filters['poNumber'] : '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('payable/fields.total-invoice') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="totalInvoice" name="totalInvoice" class="form-control currency" value="{{ !empty($filters['totalInvoice']) ? $filters['totalInvoice'] : '' }}" readonly>
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
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.payment-number') }}</th>
                                    <th>{{ trans('payable/fields.total-amount') }}</th>
                                    <th>{{ trans('payable/fields.total-interest') }}</th>
                                    <th>{{ trans('payable/fields.payment-amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountPayment = 0; ?>
                                @foreach($payments as $payment)
                                <?php 
                                $date = !empty($payment->created_date) ? new \DateTime($payment->created_date) : null;
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td>{{ $payment->payment_number }}</td>
                                    <td class="text-right">{{ number_format($payment->total_amount) }}</td>
                                    <td class="text-right">{{ number_format($payment->total_interest) }}</td>
                                    <td class="text-right">{{ number_format($payment->total_amount + $payment->total_interest) }}</td>
                                </tr>
                                <?php $totalAmountPayment += ($payment->total_amount + $payment->total_interest); ?>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountPayment) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>   
                </div>
                <div class="col-sm-12">
                    <?php 
                    $totalInvoice = !empty($filters['totalInvoice']) ? $filters['totalInvoice'] : 0;

                    $remain       = str_replace(',', '', $totalInvoice) - $totalAmountPayment;
                    ?>
                    <strong>Remain    : {{ number_format($remain) }}</strong><br>
                    <strong>Terbilang : {{ $remain < 0 ? '(Min)' : '' }} {{ trim(ucwords(Terbilang::rupiah(abs($remain)))) }}</strong>
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
    });
</script>
@endsection
