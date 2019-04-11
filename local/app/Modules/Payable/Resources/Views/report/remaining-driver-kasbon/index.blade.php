@extends('layouts.master')

@section('title', trans('payable/menu.remaining-driver-kasbon'))

<?php 
    use App\Service\Terbilang;
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
                <h2><i class="fa fa-truck"></i> {{ trans('payable/menu.remaining-driver-kasbon') }}</h2>
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
                             <div class="form-group">
                                <label for="driverCode" class="col-sm-4 control-label">{{ trans('operational/fields.driver-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="driverCode" name="driverCode" class="form-control" value="{{ !empty($filters['driverCode']) ? $filters['driverCode'] : '' }}" readonly>
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
                <div class="col-sm-5">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>    
                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.amount') }}</th>
                                    <th>{{ trans('payable/fields.remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountManifest = 0; ?>
                                @foreach($manifests as $manifest)
                                <?php 
                                $date = !empty($manifest->shipment_date) ? new \DateTime($manifest->shipment_date) : null;
                                if ($manifest->driver_id == $filters['driverId']) {
                                    $manifestAmount = $manifest->driver_salary;
                                }else{
                                    $manifestAmount = $manifest->driver_assistant_salary;
                                }

                                if (empty($manifestAmount)) {
                                    continue;
                                }else{
                                    $totalAmountManifest += $manifest->total_remain;
                                }
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $manifest->manifest_number }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td class="text-right">{{ number_format($manifestAmount) }}</td>
                                    <td class="text-right">{{ number_format($manifest->total_remain) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountManifest) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>    
                                    <th>{{ trans('operational/fields.pickup-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.amount') }}</th>
                                    <th>{{ trans('payable/fields.remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountPickup = 0; ?>
                                @foreach($pickups as $pickup)
                                <?php 
                                $date = !empty($pickup->pickup_time) ? new \DateTime($pickup->pickup_time) : null;
                                if ($pickup->driver_id == $filters['driverId']) {
                                    $pickupAmount = $pickup->driver_salary;
                                }else{
                                    $pickupAmount = $pickup->driver_assistant_salary;
                                }

                                if (empty($pickupAmount)) {
                                    continue;
                                }else{
                                    $totalAmountPickup += $pickup->total_remain;
                                }
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $pickup->pickup_form_number }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td class="text-right">{{ number_format($pickupAmount) }}</td>
                                    <td class="text-right">{{ number_format($pickup->total_remain) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountPickup) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>    
                                    <th>{{ trans('operational/fields.do-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.amount') }}</th>
                                    <th>{{ trans('payable/fields.remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; $totalAmountDo = 0; ?>
                                @foreach($dos as $do)
                                <?php 
                                $date = !empty($do->delivery_start_time) ? new \DateTime($do->delivery_start_time) : null;
                                if ($do->driver_id == $filters['driverId']) {
                                    $doAmount = $do->driver_salary;
                                }else{
                                    $doAmount = $do->driver_assistant_salary;
                                }

                                if (empty($doAmount)) {
                                    continue;
                                }else{
                                    $totalAmountDo += $do->total_remain;
                                }
                                ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $do->delivery_order_number }}</td>
                                    <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                    <td class="text-right">{{ number_format($doAmount) }}</td>
                                    <td class="text-right">{{ number_format($do->total_remain) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalAmountDo) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-sm-7">
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
                                $date = !empty($kasbon->approved_date) ? new \DateTime($kasbon->approved_date) : null;
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
                    <?php $remain = $totalAmountManifest + $totalAmountPickup + $totalAmountDo - $totalAmountKasbon ?>
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
@endsection

@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $("#datatables-driver").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-driver tbody').on('click', 'tr', function () {
            var driver = $(this).data('driver');

            $('#driverId').val(driver.driver_id);
            $('#driverName').val(driver.driver_name);
            $('#driverCode').val(driver.driver_code);
            
            $('#modal-driver').modal('hide');
        });
    });
</script>
@endsection
