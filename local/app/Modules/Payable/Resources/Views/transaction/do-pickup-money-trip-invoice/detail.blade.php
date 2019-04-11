 <?php
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@extends('layouts.master')

@section('title', trans('payable/menu.do-pickup-money-trip-invoice'))

@section('header')
@parent
<style type="text/css">
    #table-lov-driver tbody tr{
        cursor: pointer;
    }
    #table-lov-do tbody tr{
        cursor: pointer;
    }
    #table-lov-pickup tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.do-pickup-money-trip-invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->header_id }}">
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
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
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
                            $driver     = $model->driver;
                            $driverId   = !empty($driver) ? $driver->driver_id : '';
                            $driverCode = !empty($driver) ? $driver->driver_code : '';
                            $driverName = !empty($driver) ? $driver->driver_name : '';
                            $driverAddress = !empty($driver) ? $driver->address : '';
                            ?>
                            <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                <label for="driverId" class="col-sm-4 control-label">{{ trans('operational/fields.driver-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverCode" name="driverCode" value="{{ $driverCode }}" disabled>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('driverAddress') ? 'has-error' : '' }}">
                                <label for="driverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.driver-address') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverAddress" name="driverAddress" value="{{ $driverAddress }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                                $line         = $model->lineOne;
                                $amount       = !empty($line) ? $line->amount : '';
                                $do           = !empty($line) ? $line->deliveryOrder : null;
                                $doId         = !empty($do) ? $do->delivery_order_header_id : '';
                                $doNumber     = !empty($do) ? $do->delivery_order_number : '';
                                $pickup       = !empty($line) ? $line->pickup : null;
                                $pickupId     = !empty($pickup) ? $pickup->pickup_form_header_id : null;
                                $pickupNumber = !empty($pickup) ? $pickup->pickup_form_number : null;
                            ?>
                            <div id ="formDo" class="form-group {{ $errors->has('doId') ? 'has-error' : '' }}">
                                <label for="doId" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-order') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="doNumber" name="doNumber" value="{{ $doNumber }}" disabled>
                                </div>
                            </div>
                            <div id ="formPickup" class="form-group {{ $errors->has('pickupId') ? 'has-error' : '' }}">
                                <label for="pickupId" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-form') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pickupNumber" name="pickupNumber" value="{{ $pickupNumber }}" disabled>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('totalAmount') ? 'has-error' : '' }}">
                                <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.amount') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ $amount }}" disabled>
                                </div>
                            </div>
                            <?php
                            $taxLine = !empty($line) ? $line->tax : '';
                            ?>
                            <div class="form-group {{ $errors->has('tax') ? 'has-error' : '' }}">
                                <label for="tax" class="col-sm-4 control-label">{{ trans('payable/fields.tax') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="tax" name="tax" disabled>
                                        <option value="">{{ trans('shared/common.please-select') }} {{ trans('payable/fields.tax') }}</option>
                                        @foreach($optionTax as $row)
                                        <option value="{{ $row }}" {{ $row== $taxLine ? 'selected' : '' }}>{{ $row }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <?php
                            $fixAmount = !empty($line) ? $line->amount + ($line->tax / 100 * $line->amount) : '';
                            ?>
                            <div class="form-group {{ $errors->has('fixAmount') ? 'has-error' : '' }}">
                                <label for="fixAmount" class="col-sm-4 control-label">{{ trans('payable/fields.total-invoice') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="fixAmount" name="fixAmount" value="{{ $fixAmount }}" disabled>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="description" name="description" rows="3" disabled>{{ $model->description }}</textarea>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
