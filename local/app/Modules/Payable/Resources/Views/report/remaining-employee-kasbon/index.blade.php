@extends('layouts.master')

@section('title', trans('payable/menu.remaining-employee-kasbon'))

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
                <h2><i class="fa fa-truck"></i> {{ trans('payable/menu.remaining-employee-kasbon') }}</h2>
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
                            <div class="form-group">
                                <label for="vendor" class="col-sm-4 control-label">{{ trans('payable/fields.employee-name') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="vendorId" name="vendorId" value="{{ !empty($filters['vendorId']) ? $filters['vendorId'] : '' }}">
                                        <input type="text" class="form-control" id="vendorName" name="vendorName" value="{{ !empty($filters['vendorName']) ? $filters['vendorName'] : '' }}" readonly>
                                        <span class="btn input-group-addon" id="modalDriver" data-toggle="modal" data-target="#modal-vendor"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('vendorName'))
                                    <span class="help-block">{{ $errors->first('vendorName') }}</span>
                                    @endif
                                </div>
                            </div>
                             <div class="form-group">
                                <label for="vendorCode" class="col-sm-4 control-label">{{ trans('payable/fields.vendor-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="vendorCode" name="vendorCode" class="form-control" value="{{ !empty($filters['vendorCode']) ? $filters['vendorCode'] : '' }}" readonly>
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
                                    <th>{{ trans('payable/fields.kas-bon') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.is-invoice') }}</th>
                                    <th>{{ trans('payable/fields.invoice-amount') }}</th>
                                    <th>{{ trans('payable/fields.payment-amount') }}</th>
                                    <th>{{ trans('payable/fields.receipt-amount') }}</th>
                                    <th>{{ trans('payable/fields.remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountKasbon = 0; ?>
                                @foreach($kasbons as $kasbon)
                                <?php 
                                $date    = !empty($kasbon->approved_date) ? new \DateTime($kasbon->approved_date) : null;
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $kasbon->invoice_number }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td class="text-center">
                                        <i class="fa {{ $kasbon->is_invoice ? 'fa-check' : 'fa-remove' }}"></i>
                                    </td>
                                    <td class="text-right">{{ number_format($kasbon->invoice_amount) }}</td>
                                    <td class="text-right">{{ number_format($kasbon->payment_amount) }}</td>
                                    <td class="text-right">{{ number_format($kasbon->receipt_amount) }}</td>
                                    <td class="text-right">{{ number_format($kasbon->total_remain) }}</td>
                                </tr>
                                <?php $totalAmountKasbon += $kasbon->total_remain; ?>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountKasbon) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-sm-12">
                    <strong>Remain    : {{ number_format($totalAmountKasbon) }}</strong><br>
                    <strong>Terbilang : {{ $totalAmountKasbon < 0 ? '(Min)' : '' }} {{ trim(ucwords(Terbilang::rupiah(abs($totalAmountKasbon)))) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-vendor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Employee List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-vendor" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('payable/fields.vendor-code') }}</th>
                            <th>{{ trans('payable/fields.employee-name') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionEmployee as $vendor)
                        <tr style="cursor: pointer;" data-vendor="{{ json_encode($vendor) }}">
                            <td>{{ $vendor->vendor_code }}</td>
                            <td>{{ $vendor->vendor_name }}</td>
                            <td>{{ $vendor->address }}</td>
                            <td>{{ $vendor->description }}</td>
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
        $("#datatables-vendor").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-vendor tbody').on('click', 'tr', function () {
            var vendor = $(this).data('vendor');

            $('#vendorId').val(vendor.vendor_id);
            $('#vendorName').val(vendor.vendor_name);
            $('#vendorCode').val(vendor.vendor_code);
            
            $('#modal-vendor').modal('hide');
        });
    });
</script>
@endsection
