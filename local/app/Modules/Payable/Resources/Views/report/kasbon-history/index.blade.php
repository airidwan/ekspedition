@extends('layouts.master')

@section('title', trans('payable/menu.kasbon-history'))

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
                <h2><i class="fa fa-truck"></i> {{ trans('payable/menu.kasbon-history') }}</h2>
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
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = !empty($filters['type']) ? $filters['type'] : ''; ?>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type->type_id }}" {{ $type->type_id == $stringType ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group" id="formDriver">
                                <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver-or-assistant') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ !empty($filters['driverId']) ? $filters['driverId'] : '' }}">
                                        <input type="text" class="form-control" id="driverName" name="driverName" value="{{ !empty($filters['driverName']) ? $filters['driverName'] : '' }}" readonly>
                                        <span class="btn input-group-addon" id="modalDriver" data-toggle="modal" data-target="#modal-driver"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('driverName'))
                                    <span class="help-block">{{ $errors->first('driverName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group" id="formVendor">
                                <label for="vendor" class="col-sm-4 control-label">{{ trans('payable/fields.vendor') }}</label>
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
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="address" name="address" class="form-control" value="{{ !empty($filters['address']) ? $filters['address'] : '' }}" readonly>
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
                <div class="col-sm-6">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>    
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.payment-number') }}</th>
                                    <th>{{ trans('payable/fields.invoice-number') }}</th>
                                    <th>{{ trans('payable/fields.payment-amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountKasbonPayment = 0; ?>
                                @foreach($kasbonPayments as $kasbonPayment)
                                <?php 
                                $date = !empty($kasbonPayment->created_date) ? new \DateTime($kasbonPayment->created_date) : null;
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td>{{ $kasbonPayment->payment_number }}</td>
                                    <td>{{ $kasbonPayment->invoice_number }}</td>
                                    <td class="text-right">{{ number_format($kasbonPayment->total_amount) }}</td>
                                </tr>
                                <?php $totalAmountKasbonPayment += $kasbonPayment->total_amount; ?>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountKasbonPayment) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>   
                </div>
                <div class="col-sm-6">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>    
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('accountreceivables/fields.receipt-number') }}</th>
                                    <th>{{ trans('payable/fields.invoice-number') }}</th>
                                    <th>{{ trans('payable/fields.receipt-amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountKasbonReceipt = 0; ?>
                                @foreach($kasbonReceipts as $kasbonReceipt)
                                <?php 
                                $date = !empty($kasbonReceipt->created_date) ? new \DateTime($kasbonReceipt->created_date) : null;
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td>{{ $kasbonReceipt->receipt_number }}</td>
                                    <td>{{ $kasbonReceipt->invoice_number }}</td>
                                    <td class="text-right">{{ number_format($kasbonReceipt->amount) }}</td>
                                </tr>
                                <?php $totalAmountKasbonReceipt += $kasbonReceipt->amount; ?>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountKasbonReceipt) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-sm-12">
                    <?php $remain = $totalAmountKasbonReceipt - $totalAmountKasbonPayment ?>
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
<div id="modal-driver" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Driver List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.driver-code') }}</th>
                            <th>{{ trans('operational/fields.driver-name') }}</th>
                            <th>{{ trans('operational/fields.position') }}</th>
                            <th>{{ trans('shared/common.type') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionDriver as $driver)
                        <tr style="cursor: pointer;" data-driver="{{ json_encode($driver) }}">
                            <td>{{ $driver->driver_code }}</td>
                            <td>{{ $driver->driver_name }}</td>
                            <td>{{ $driver->driver_category }}</td>
                            <td>{{ $driver->type }}</td>
                            <td>{{ $driver->address }}</td>
                            <td>{{ $driver->description }}</td>
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
                            <th>{{ trans('payable/fields.vendor-name') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionVendor->get() as $vendor)
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
        disableModal();
        $("#datatables-driver").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-driver tbody').on('click', 'tr', function () {
            var driver = $(this).data('driver');

            $('#driverId').val(driver.driver_id);
            $('#driverName').val(driver.driver_name);
            $('#address').val(driver.address);
            
            $('#modal-driver').modal('hide');
        });

        $("#datatables-vendor").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-vendor tbody').on('click', 'tr', function () {
            var vendor = $(this).data('vendor');

            $('#vendorId').val(vendor.vendor_id);
            $('#vendorName').val(vendor.vendor_name);
            $('#address').val(vendor.address);
            
            $('#modal-vendor').modal('hide');
        });

        $('#type').on('change', function(){
            disableModal();
            clearForm();
        });
    });

    var clearForm = function(){
        $('#vendorId').val('');
        $('#vendorName').val('');
        $('#driverId').val('');
        $('#driverName').val('');
        $('#address').val('');
    }

    var disableModal = function(){
        var type = $('#type').val();

        $('#formDriver').addClass('hidden');
        $('#formVendor').addClass('hidden');

        if (type == '{{ InvoiceHeader::KAS_BON_DRIVER }}') {
            $('#formDriver').removeClass('hidden');
        }else if (type == '{{ InvoiceHeader::KAS_BON_EMPLOYEE }}') {
            $('#formVendor').removeClass('hidden');
        }
    };
</script>
@endsection
