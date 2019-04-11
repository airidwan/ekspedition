@extends('layouts.master')

@section('title', trans('operational/menu.pickup-form'))

<?php
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;
use App\Modules\Operational\Model\Transaction\PickupFormHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.pickup-form') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->pickup_form_header_id }}">
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
                                    <div class="form-group {{ $errors->has('pickupNumber') ? 'has-error' : '' }}">
                                        <label for="pickupNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="pickupNumber" name="pickupNumber" value="{{ count($errors) > 0 ? old('pickupNumber') : $model->pickup_form_number }}" readonly>
                                            @if($errors->has('pickupNumber'))
                                            <span class="help-block">{{ $errors->first('pickupNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                                    } else {
                                        $date = !empty($model->pickup_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_time) :  TimezoneDateConverter::getClientDateTime();
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                        <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('date'))
                                            <span class="help-block">{{ $errors->first('date') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                                    } else {
                                        $date = !empty($model->pickup_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_time) :  TimezoneDateConverter::getClientDateTime();
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                                        <label for="time" class="col-sm-4 control-label">{{ trans('shared/common.time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;" >
                                            <select class="form-control" id="hours" name="hours" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <?php $hoursFormat = $date !== null ? $date->format('H') : -1 ; ?>
                                                <?php $hours = count($errors) > 0 ? old('hours') : $hoursFormat ; ?>
                                                @for ($i = 0; $i < 24; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hours == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                            @if($errors->has('hours'))
                                            <span class="help-block">{{ $errors->first('hours') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="minute" name="minute" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <?php $minuteFormat = $date !== null ? $date->format('i') : -1 ; ?>
                                                <?php $minute = count($errors) > 0 ? old('minute') : $minuteFormat ; ?>
                                                @for ($i = 0; $i < 60; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                            @if($errors->has('minute'))
                                            <span class="help-block">{{ $errors->first('minute') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="status" name="status" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <?php $statusString = count($errors) > 0 ? old('status') : $model->status ?>
                                                @foreach($optionStatus as $status)
                                                <option value="{{ $status }}" {{ $statusString == $status ? 'selected' : '' }}>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('noteHeader') ? 'has-error' : '' }}">
                                        <label for="noteHeader" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="3" id="noteHeader" name="noteHeader" {{ !$model->isOpen() ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('noteHeader') : $model->note }}</textarea>
                                            @if($errors->has('noteHeader'))
                                            <span class="help-block">{{ $errors->first('noteHeader') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverId    = !empty($modelDriver) ? $modelDriver->driver_id : '' ; 
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="{{ !$model->isOpen() ? '' : 'modal' }}" data-target="#modal-driver"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverName'))
                                            <span class="help-block">{{ $errors->first('driverName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelTruck   = $model->truck;
                                        $truckId      = !empty($modelTruck) ? $modelTruck->truck_id : '' ; 
                                        $policeNumber = !empty($modelTruck) ? $modelTruck->police_number : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('truckId') ? 'has-error' : '' }}">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.truck') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" readonly>
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="{{ !$model->isOpen() ? '' : 'modal' }}" data-target="#modal-truck"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('truckId'))
                                            <span class="help-block">{{ $errors->first('truckId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('deliveryArea') ? 'has-error' : '' }}">
                                        <label for="deliveryArea" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="deliveryArea" name="deliveryArea" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                            <?php $deliveryAreaId = count($errors) > 0 ? old('deliveryArea') : $model->delivery_area_id; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionDeliveryArea as $deliveryArea)
                                                <option value="{{ $deliveryArea->delivery_area_id }}" {{ $deliveryArea->delivery_area_id == $deliveryAreaId ? 'selected' : '' }}>{{ $deliveryArea->delivery_area_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if($model->isOpen())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line">
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
                                                    <th>{{ trans('marketing/fields.pickup-request-number') }}</th>
                                                    <th>{{ trans('operational/fields.customer') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('shared/common.note') }}</th>
                                                    <th>{{ trans('operational/fields.address') }}</th>
                                                    <th>{{ trans('shared/common.telepon') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}</th>
                                                    <th>{{ trans('operational/fields.dimension') }}</th>
                                                    <th>{{ trans('operational/fields.pickup-cost') }}</th>
                                                    <th>{{ trans('shared/common.note') }}</th>
                                                    <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('pickupRequestNumber')[$i] }} </td>
                                                    <td > {{ old('customerName')[$i] }} </td>
                                                    <td > {{ old('itemName')[$i] }} </td>
                                                    <td > {{ old('note')[$i] }} </td>
                                                    <td > {{ old('address')[$i] }} </td>
                                                    <td > {{ old('phoneNumber')[$i] }} </td>
                                                    <td class="text-right"> {{ old('totalColy')[$i] }} </td>
                                                    <td class="text-right"> {{ old('weight')[$i] }} </td>
                                                    <td class="text-right"> {{ old('dimension')[$i] }} </td>
                                                    <td class="text-right"> {{ old('pickupCost')[$i] }} </td>
                                                    <td > {{ old('note')[$i] }} </td>
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <a href=" {{ URL($urlRequest). '/print-pdf-detail/'. old('pickupRequestId')[$i] }}" target="_blank" data-toggle="tooltip" class="btn btn-success btn-xs print-line" ><i class="fa fa-print"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="pickupRequestNumber[]" value="{{ old('pickupRequestNumber')[$i] }}">
                                                        <input type="hidden" name="pickupRequestId[]" value="{{ old('pickupRequestId')[$i] }}">
                                                        <input type="hidden" name="resiId[]" value="{{ old('resiId')[$i] }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="callersName[]" value="{{ old('callersName')[$i] }}">
                                                        <input type="hidden" name="customerName[]" value="{{ old('customerName')[$i] }}">
                                                        <input type="hidden" name="address[]" value="{{ old('address')[$i] }}">
                                                        <input type="hidden" name="phoneNumber[]" value="{{ old('phoneNumber')[$i] }}">
                                                        <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                        <input type="hidden" name="totalColy[]" value="{{ old('totalColy')[$i] }}">
                                                        <input type="hidden" name="weight[]" value="{{ old('weight')[$i] }}">
                                                        <input type="hidden" name="dimensionL[]" value="{{ old('dimensionL')[$i] }}">
                                                        <input type="hidden" name="dimensionW[]" value="{{ old('dimensionW')[$i] }}">
                                                        <input type="hidden" name="dimensionH[]" value="{{ old('dimensionH')[$i] }}">
                                                        <input type="hidden" name="dimension[]" value="{{ old('dimension')[$i] }}">
                                                        <input type="hidden" name="pickupCost[]" value="{{ old('pickupCost')[$i] }}">
                                                        <input type="hidden" name="note[]" value="{{ old('note')[$i] }}">
                                                        <input type="hidden" name="noteAdd[]" value="{{ old('noteAdd')[$i] }}">
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>

                                                @endfor
                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $request = $line->pickupRequest;
                                                    $resi    = $line->resi;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $request !== null ? $request->pickup_request_number : '' }} </td>
                                                    <!-- <td > {{ $resi !== null ? $resi->resi_number : '' }} </td> -->
                                                    <td > {{ $request !== null ? $request->customer_name : '' }} </td>
                                                    <td > {{ $request !== null ? $request->item_name : '' }} </td>
                                                    <td > {{ $request !== null ? $request->note : '' }} </td>
                                                    <td > {{ $request !== null ? $request->address : '' }} </td>
                                                    <td > {{ $request !== null ? $request->phone_number : '' }} </td>
                                                    <td class="text-right"> {{ $request !== null ? number_format($request->total_coly) : '' }} </td>
                                                    <td class="text-right"> {{ $request !== null ? number_format($request->weight, 2) : '' }} </td>
                                                    <td class="text-right"> {{ $request !== null ? number_format($request->dimension, 6) : '' }} </td>
                                                    <td class="text-right"> {{ $request !== null ? number_format($request->pickup_cost) : '' }} </td>
                                                    <td > {{ $request !== null ? $request->note_add : '' }} </td>
                                                    <td class="text-center">
                                                    @if($model->isOpen())
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                    @endif
                                                        @if($model->isOpen() || $model->isClosed())
                                                        <a href="{{ URL($urlRequest . '/print-pdf-detail/' . $line->pickup_request_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                                            <i class="fa fa-print"></i>
                                                        </a>
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->pickup_form_line_id }}">
                                                        <input type="hidden" name="pickupRequestId[]" value="{{ $request !== null ? $request->pickup_request_id : '' }}">
                                                        <input type="hidden" name="pickupRequestNumber[]" value="{{ $request !== null ? $request->pickup_request_number : '' }}">
                                                        <input type="hidden" name="resiId[]" value="{{ $resi !== null ? $resi->resi_header_id : '' }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ $resi !== null ? $resi->resi_number : '' }}">
                                                        <input type="hidden" name="callersName[]" value="{{ $request !== null ? $request->callers_name : '' }}">
                                                        <input type="hidden" name="customerName[]" value="{{ $request !== null ? $request->customer_name : '' }}">
                                                        <input type="hidden" name="address[]" value="{{ $request !== null ? $request->address : '' }}">
                                                        <input type="hidden" name="phoneNumber[]" value="{{ $request !== null ? $request->phone_number : '' }}">
                                                        <input type="hidden" name="itemName[]" value="{{ $request !== null ? $request->item_name : '' }}">
                                                        <input type="hidden" name="totalColy[]" value="{{ $request !== null ? $request->total_coly : '' }}">
                                                        <input type="hidden" name="weight[]" value="{{ $request !== null ? $request->weight : '' }}">
                                                        <input type="hidden" name="dimensionL[]" value="{{ $request !== null ? $request->dimension_long : '' }}">
                                                        <input type="hidden" name="dimensionW[]" value="{{ $request !== null ? $request->dimension_width : '' }}">
                                                        <input type="hidden" name="dimensionH[]" value="{{ $request !== null ? $request->dimension_height : '' }}">
                                                        <input type="hidden" name="dimension[]" value="{{ $request !== null ? $request->dimension : '' }}">
                                                        <input type="hidden" name="pickupCost[]" value="{{ $request !== null ? $request->pickup_cost : '' }}">
                                                        <input type="hidden" name="note[]" value="{{ $request !== null ? $request->note : '' }}">
                                                        <input type="hidden" name="noteAdd[]" value="{{ $request !== null ? $request->note_add : '' }}">
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
                                @if($model->isOpen())
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if(($model->isOpen() && !empty($model->pickup_form_header_id)) || $model->isClosed())
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->pickup_form_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
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

@section('modal')
@parent
<div id="modal-form-line" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url('operasional/master/master-truk-kendaraan/save') }}">
                                {{ csrf_field() }}
                                <div class="col-sm-6 portlets">
                                    <input type="hidden" name="dataIndexForm" id="dataIndexForm" value="">
                                    <input type="hidden" name="idDetail" id="idDetail" value="">
                                    <input type="hidden" name="lineId" id="lineId" value="">
                                    <div class="form-group {{ $errors->has('pickupRequest') ? 'has-error' : '' }}">
                                        <label for="pickupRequest" class="col-sm-4 control-label">{{ trans('marketing/fields.pickup-request-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="pickupRequestId" id="pickupRequestId" value="">
                                                <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" readonly>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-pickup"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="callersName" class="col-sm-4 control-label">{{ trans('marketing/fields.callers-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="callersName" name="callersName" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="0" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemName" name="itemName" value="" readonly>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="note" name="note" value="0" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalColy" name="totalColy" value="0" readonly>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="weight" class="col-sm-4 control-label">{{ trans('operational/fields.weight') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control decimal text-right" id="weight" name="weight" value="0" readonly>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="volume" class="col-sm-4 control-label">{{ trans('operational/fields.dimension') }} (Cm)</label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control decimal text-right" id="dimensionL" placeholder="L" name="dimensionL" value="" readonly>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control decimal text-right" id="dimensionW" placeholder="W" name="dimensionW" value="" readonly>
                                        </div>    
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control decimal text-right" id="dimensionH" placeholder="H" name="dimensionH" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="dimension" class="col-sm-4 control-label">{{ trans('operational/fields.dimension') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control decimal text-right" id="dimension" name="dimension" value="0" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupCost" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-cost') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="pickupCost" name="pickupCost" value="0" >
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="noteAdd" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="noteAdd" name="noteAdd" value="0">
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
<div id="modal-resi" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">List of Resi</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.resi-number') }}</th>
                            <th>{{ trans('operational/fields.customer-name') }}</th>
                            <th>{{ trans('operational/fields.address') }}</th>
                            <th>{{ trans('operational/fields.phone') }}</th>
                            <th>{{ trans('inventory/fields.item') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('operational/fields.kode-rute') }}</th>
                            <th>{{ trans('operational/fields.kota-asal') }}</th>
                            <th>{{ trans('operational/fields.kota-tujuan') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-pickup" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">List of Pickup Request</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-pickup" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('marketing/fields.pickup-request-number') }}</th>
                            <th>{{ trans('marketing/fields.callers-name') }}</th>
                            <th>{{ trans('operational/fields.customer') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.telepon') }}</th>
                            <th>{{ trans('operational/fields.item-name') }}</th>
                            <th>{{ trans('operational/fields.total-coly') }}</th>
                            <th>{{ trans('operational/fields.pickup-cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionPickup as $pickup)
                        <tr style="cursor: pointer;" data-pickup="{{ json_encode($pickup) }}">
                            <td>{{ $pickup->pickup_request_number }}</td>
                            <td>{{ $pickup->callers_name }}</td>
                            <td>{{ $pickup->customer_name }}</td>
                            <td>{{ $pickup->address }}</td>
                            <td>{{ $pickup->phone_number }}</td>
                            <td>{{ $pickup->item_name }}</td>
                            <td class="text-right">{{ number_format($pickup->total_coly) }}</td>
                            <td class="text-right">{{ number_format($pickup->pickup_cost) }}</td>
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
                            <th>{{ trans('operational/fields.nickname') }}</th>
                            <th>{{ trans('shared/common.category') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionDriver as $driver)
                        <tr style="cursor: pointer;" data-driver="{{ json_encode($driver) }}">
                            <td>{{ $driver->driver_code }}</td>
                            <td>{{ $driver->driver_name }}</td>
                            <td>{{ $driver->driver_nickname }}</td>
                            <td>{{ $driver->driver_category }}</td>
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

<div id="modal-truck" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Truck List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-truck" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.truck-code') }}</th>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('operational/fields.owner-name') }}</th>
                            <th>{{ trans('operational/fields.brand') }}</th>
                            <th>{{ trans('operational/fields.type') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionTruck as $truck)
                        <tr style="cursor: pointer;" data-truck="{{ json_encode($truck) }}">
                            <td>{{ $truck->truck_code }}</td>
                            <td>{{ $truck->police_number }}</td>
                            <td>{{ $truck->owner_name }}</td>
                            <td>{{ $truck->truck_brand }}</td>
                            <td>{{ $truck->truck_type }}</td>
                            <td>{{ $truck->description }}</td>
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
    var dataIndex = {{ $dataIndex }};
    var urlRequest = '{{ URL($urlRequest) }}';
    $(document).on('ready', function(){
        $('#dimensionL').on('keyup', calculateVolume);
        $('#dimensionW').on('keyup', calculateVolume);
        $('#dimensionH').on('keyup', calculateVolume);
        
        $('#save-line').on('click', saveLine);
        $('.delete-line').on('click', deleteLine);
        $('.edit-line').on('click', editLine);
        $('#cancel-save-line').on('click', cancelSaveLine);
        $('#clear-lines').on('click', clearLines);


        $("#datatables-driver").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-driver tbody').on('click', 'tr', function () {
            var driver = $(this).data('driver');

            $('#driverId').val(driver.driver_id);
            $('#driverName').val(driver.driver_name);
            
            $('#modal-driver').modal('hide');
        });

        $("#datatables-truck").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-truck tbody').on('click', 'tr', function () {
            var truck = $(this).data('truck');

            $('#truckId').val(truck.truck_id);
            $('#policeNumber').val(truck.police_number);
            
            $('#modal-truck').modal('hide');
        });
    
        $('.add-line').on('click', addLine);

        $("#datatables-resi").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-resi tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            $('#resiId').val(resi.resi_header_id);
            $('#resiNumber').val(resi.resi_number);

            $('#modal-resi').modal('hide');
        });

        $("#datatables-pickup").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-pickup tbody').on('click', 'tr', function () {
            var pickup = $(this).data('pickup');

            if(checkPickupExist(pickup.pickup_request_id)){
                $('#modal-alert').find('.alert-message').html('{{ trans('operational/fields.pickup-exist') }}');
                $('#modal-alert').modal('show');
                return;
            }

            $('#pickupRequestId').val(pickup.pickup_request_id);
            $('#pickupRequestNumber').val(pickup.pickup_request_number);
            $('#callersName').val(pickup.callers_name);
            $('#customerName').val(pickup.customer_name);
            $('#address').val(pickup.address);
            $('#phoneNumber').val(pickup.phone_number);
            $('#itemName').val(pickup.item_name);
            $('#totalColy').val(pickup.total_coly);
            $('#weight').val(pickup.weight);
            $('#dimensionL').val(pickup.dimension_long);
            $('#dimensionW').val(pickup.dimension_width);
            $('#dimensionH').val(pickup.dimension_height);
            $('#dimension').val(pickup.dimension);
            $('#note').val(pickup.note);
            $('#noteAdd').val(pickup.note_add);
            $('#pickupCost').val(pickup.pickup_cost);

            $('#totalColy').autoNumeric('update', {mDec: 0});
            $('#weight').autoNumeric('update', {mDec: 2});
            $('#dimensionL').autoNumeric('update', {mDec: 2});
            $('#dimensionW').autoNumeric('update', {mDec: 2});
            $('#dimensionH').autoNumeric('update', {mDec: 2});
            $('#dimension').autoNumeric('update', {mDec: 6});
            $('#pickupCost').autoNumeric('update', {mDec: 0});

            $('#modal-pickup').modal('hide');
        });

    });

    var calculateVolume = function() {
        var dimensionL = parseInt($('#dimensionL').val().split(',').join(''));
        var dimensionW = parseInt($('#dimensionW').val().split(',').join(''));
        var dimensionH = parseInt($('#dimensionH').val().split(',').join(''));
        var convertM3 = 1000000;
        var dimension = dimensionL * dimensionW * dimensionH / convertM3;

        $('#dimension').val(dimension).autoNumeric('update', {mDec: 6});
    };

    var checkPickupExist = function(requestId) {
        var exist = false;
        $('#table-line tbody tr').each(function (i, row) {
            var pickupRequestId = parseFloat($(row).find('[name="pickupRequestId[]"]').val().split(',').join(''));
            if (requestId == pickupRequestId) {
                exist = true;
            }
        });
        return exist;
    };

    var cancelSaveLine = function() {
        $('#modal-form-line').modal("hide");
    };

    var clearLines = function() {
        $('#table-line tbody').html('');
        calculateTotal();
    };

    var addLine = function() {
        clearFormLine();
        $('#title-modal-line-detail').html('{{ trans('shared/common.add') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.add') }}');

        $('#modal-form-line').modal("show");
    };

    var clearFormLine = function() {
        $('#dataIndexForm').val('');
        $('#lineId').val('');
        $('#resiId').val('');
        $('#resiNumber').val('');
        $('#pickupRequestId').val('');
        $('#pickupRequestNumber').val('');
        $('#callersName').val('');
        $('#customerName').val('');
        $('#address').val('');
        $('#itemName').val('');
        $('#note').val('');
        $('#noteAdd').val('');
        $('#phoneNumber').val('');
        $('#totalColy').val('');
        $('#weight').val('');
        $('#dimensionL').val('');
        $('#dimensionW').val('');
        $('#dimensionH').val('');
        $('#dimension').val('');
        $('#pickupCost').val('');

        $('#pickupRequestNumber').parent().parent().parent().removeClass('has-error');
        $('#pickupRequestNumber').parent().parent().find('span.help-block').html('');
        $('#pickupCost').parent().parent().removeClass('has-error');
        $('#pickupCost').parent().find('span.help-block').html('');
        $('#noteAdd').parent().parent().removeClass('has-error');
        $('#noteAdd').parent().find('span.help-block').html('');
    };

    var saveLine = function() {
        var dataIndexForm = $('#dataIndexForm').val();
        var lineId = $('#lineId').val();
        var pickupRequestId = $('#pickupRequestId').val();
        var pickupRequestNumber = $('#pickupRequestNumber').val();
        var resiId = $('#resiId').val();
        var resiNumber = $('#resiNumber').val();
        var callersName = $('#callersName').val();
        var customerName = $('#customerName').val();
        var address = $('#address').val();
        var phoneNumber = $('#phoneNumber').val();
        var itemName = $('#itemName').val();
        var totalColy = $('#totalColy').val();
        var weight = $('#weight').val();
        var dimensionL = $('#dimensionL').val();
        var dimensionW = $('#dimensionW').val();
        var dimensionH = $('#dimensionH').val();
        var dimension = $('#dimension').val();
        var note = $('#note').val();
        var noteAdd = $('#noteAdd').val();
        var pickupCost = $('#pickupCost').val();
        var error = false;

        if (pickupRequestNumber == '' || pickupRequestId == '') {
            $('#pickupRequestNumber').parent().parent().parent().addClass('has-error');
            $('#pickupRequestNumber').parent().parent().find('span.help-block').html('Choose pickup request first');
            error = true;
        } else {
            $('#pickupRequestNumber').parent().parent().parent().removeClass('has-error');
            $('#pickupRequestNumber').parent().parent().find('span.help-block').html('');
        }

        if (address == '') {
            $('#address').parent().parent().addClass('has-error');
            $('#address').parent().find('span.help-block').html('Quantity is required');
            error = true;
        } else {
            $('#address').parent().parent().removeClass('has-error');
            $('#address').parent().find('span.help-block').html('');
        }

        if (phoneNumber == '') {
            $('#phoneNumber').parent().parent().addClass('has-error');
            $('#phoneNumber').parent().find('span.help-block').html('Quantity is required');
            error = true;
        } else {
            $('#phoneNumber').parent().parent().removeClass('has-error');
            $('#phoneNumber').parent().find('span.help-block').html('');
        }

        if (noteAdd == '') {
            $('#noteAdd').parent().parent().addClass('has-error');
            $('#noteAdd').parent().find('span.help-block').html('Note is required');
            error = true;
        } else {
            $('#noteAdd').parent().parent().removeClass('has-error');
            $('#noteAdd').parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        var htmlTr = '<td >' + pickupRequestNumber + '</td>' +
            // '<td >' + resiNumber + '</td>' +
            '<td >' + customerName + '</td>' +
            '<td >' + itemName + '</td>' +
            '<td >' + note + '</td>' +
            '<td >' + address + '</td>' +
            '<td >' + phoneNumber + '</td>' +
            '<td class="text-right">' + totalColy + '</td>' +
            '<td class="text-right">' + weight + '</td>' +
            '<td class="text-right">' + dimension + '</td>' +
            '<td class="text-right">' + pickupCost + '</td>' +
            '<td >' + noteAdd + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<a href="'+ urlRequest +'/print-pdf-detail/'+ pickupRequestId +'" target="_blank" data-toggle="tooltip" class="btn btn-success btn-xs print-line" ><i class="fa fa-print"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="pickupRequestId[]" value="' + pickupRequestId + '">' +
            '<input type="hidden" name="pickupRequestNumber[]" value="' + pickupRequestNumber + '">' +
            '<input type="hidden" name="resiId[]" value="' + resiId + '">' +
            '<input type="hidden" name="resiNumber[]" value="' + resiNumber + '">' +
            '<input type="hidden" name="callersName[]" value="' + callersName + '">' +
            '<input type="hidden" name="customerName[]" value="' + customerName + '">' +
            '<input type="hidden" name="address[]" value="' + address + '">' +
            '<input type="hidden" name="phoneNumber[]" value="' + phoneNumber + '">' +
            '<input type="hidden" name="itemName[]" value="' + itemName + '">' +
            '<input type="hidden" name="totalColy[]" value="' + totalColy + '">' +
            '<input type="hidden" name="weight[]" value="' + weight + '">' +
            '<input type="hidden" name="dimensionL[]" value="' + dimensionL + '">' +
            '<input type="hidden" name="dimensionW[]" value="' + dimensionW + '">' +
            '<input type="hidden" name="dimensionH[]" value="' + dimensionH + '">' +
            '<input type="hidden" name="dimension[]" value="' + dimension + '">' +
            '<input type="hidden" name="note[]" value="' + note + '">' +
            '<input type="hidden" name="noteAdd[]" value="' + noteAdd + '">' +
            '<input type="hidden" name="pickupCost[]" value="' + pickupCost + '">' +
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

        dataIndex++;
        $('#modal-form-line').modal("hide");
    };

    var editLine = function() {
        var dataIndexForm = $(this).parent().parent().data('index');
        var lineId = $(this).parent().parent().find('[name="lineId[]"]').val();
        var pickupRequestId = $(this).parent().parent().find('[name="pickupRequestId[]"]').val();
        var pickupRequestNumber = $(this).parent().parent().find('[name="pickupRequestNumber[]"]').val();
        var resiId = $(this).parent().parent().find('[name="resiId[]"]').val();
        var resiNumber = $(this).parent().parent().find('[name="resiNumber[]"]').val();
        var callersName = $(this).parent().parent().find('[name="callersName[]"]').val();
        var customerName = $(this).parent().parent().find('[name="customerName[]"]').val();
        var address = $(this).parent().parent().find('[name="address[]"]').val();
        var phoneNumber = $(this).parent().parent().find('[name="phoneNumber[]"]').val();
        var itemName = $(this).parent().parent().find('[name="itemName[]"]').val();
        var totalColy = $(this).parent().parent().find('[name="totalColy[]"]').val();
        var weight = $(this).parent().parent().find('[name="weight[]"]').val();
        var dimensionL = $(this).parent().parent().find('[name="dimensionL[]"]').val();
        var dimensionW = $(this).parent().parent().find('[name="dimensionW[]"]').val();
        var dimensionH = $(this).parent().parent().find('[name="dimensionH[]"]').val();
        var dimension = $(this).parent().parent().find('[name="dimension[]"]').val();
        var note = $(this).parent().parent().find('[name="note[]"]').val();
        var noteAdd = $(this).parent().parent().find('[name="noteAdd[]"]').val();
        var pickupCost = $(this).parent().parent().find('[name="pickupCost[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#pickupRequestId').val(pickupRequestId);
        $('#pickupRequestNumber').val(pickupRequestNumber);
        $('#resiId').val(resiId);
        $('#resiNumber').val(resiNumber);
        $('#callersName').val(callersName);
        $('#customerName').val(customerName);
        $('#address').val(address);
        $('#phoneNumber').val(phoneNumber);
        $('#itemName').val(itemName);
        $('#totalColy').val(totalColy);
        $('#weight').val(weight);
        $('#dimensionL').val(dimensionL);
        $('#dimensionW').val(dimensionW);
        $('#dimensionH').val(dimensionH);
        $('#dimension').val(dimension);
        $('#note').val(note);
        $('#noteAdd').val(noteAdd);
        $('#pickupCost').val(pickupCost);

        $('#totalColy').autoNumeric('update', {mDec: 0});
        $('#weight').autoNumeric('update', {mDec: 2});
        $('#dimensionL').autoNumeric('update', {mDec: 2});
        $('#dimensionW').autoNumeric('update', {mDec: 2});
        $('#dimensionH').autoNumeric('update', {mDec: 2});
        $('#dimension').autoNumeric('update', {mDec: 6});
        $('#pickupCost').autoNumeric('update', {mDec: 0});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
    };

</script>
@endsection
