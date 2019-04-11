@extends('layouts.master')

@section('title', trans('operational/menu.delivery-order'))

<?php 
    use App\Service\Penomoran; 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader; 
    use App\Service\TimezoneDateConverter;
    use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader; 
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.delivery-order') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->delivery_order_header_id }}">
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
                                    <div class="form-group {{ $errors->has('deliveryOrderNumber') ? 'has-error' : '' }}">
                                        <label for="deliveryOrderNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ count($errors) > 0 ? old('deliveryOrderNumber') : $model->delivery_order_number }}" readonly>
                                            @if($errors->has('deliveryOrderNumber'))
                                            <span class="help-block">{{ $errors->first('deliveryOrderNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelDraftDo   = $model->draftDo;
                                        $draftDoNumber  = !empty($modelDraftDo) ? $modelDraftDo->draft_delivery_order_number : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('draftDoNumber') ? 'has-error' : '' }}">
                                        <label for="draftDoNumber" class="col-sm-4 control-label">{{ trans('operational/fields.draft-do') }} </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="draftDoHeaderId" name="draftDoHeaderId" value="{{ count($errors) > 0 ? old('draftDoHeaderId') : $model->draft_delivery_order_header_id }}">
                                            <input type="text" class="form-control" id="draftDoNumber" name="draftDoNumber" value="{{ count($errors) > 0 ? old('draftDoNumber') : $draftDoNumber }}" readonly>
                                            <span class="btn input-group-addon" id="remove-draft-do"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalDraftDo" data-toggle="modal" data-target="#modal-draft-do"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('draftDoNumber'))
                                            <span class="help-block">{{ $errors->first('draftDoNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $startTime = !empty(old('startTime')) ? new \DateTime(old('startTime')) : TimezoneDateConverter::getClientDateTime();
                                    } else {
                                        $startTime = !empty($model->delivery_start_time) ? TimezoneDateConverter::getClientDateTime($model->delivery_start_time) :  TimezoneDateConverter::getClientDateTime();
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('startTime') ? 'has-error' : '' }}">
                                        <label for="startTime" class="col-sm-4 control-label">{{ trans('operational/fields.start-date') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="startTime" name="startTime" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $startTime !== null ? $startTime->format('d-m-Y') : '' }}" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('startTime'))
                                            <span class="help-block">{{ $errors->first('startTime') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $startTime = !empty(old('startTime')) ? new \DateTime(old('startTime')) : TimezoneDateConverter::getClientDateTime();
                                    } else {
                                        $startTime = !empty($model->delivery_start_time) ? TimezoneDateConverter::getClientDateTime($model->delivery_start_time) :  TimezoneDateConverter::getClientDateTime();
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('startTime') ? 'has-error' : '' }}">
                                        <label for="startTime" class="col-sm-4 control-label">{{ trans('shared/common.start-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="startHours" name="startHours" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>
                                                <?php $hoursFormat = $startTime !== null ? $startTime->format('H') : 1 ; ?>
                                                <?php $hours = count($errors) > 0 ? old('startHours') : $hoursFormat ; ?>
                                                @for ($i = 0; $i < 24; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hours == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                            @if($errors->has('startHours'))
                                            <span class="help-block">{{ $errors->first('startHours') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="startMinute" name="startMinute" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>
                                                <?php $minuteFormat = $startTime !== null ? $startTime->format('i') : -1 ; ?>
                                                <?php $minute = count($errors) > 0 ? old('startMinute') : $minuteFormat ; ?>
                                                @for ($i = 0; $i < 60; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                            @if($errors->has('startMinute'))
                                            <span class="help-block">{{ $errors->first('startMinute') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $endTime = !empty($model->delivery_end_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->obook_time) : null;
                                    ?>
                                    <div class="form-group">
                                        <label for="endDate" class="col-sm-4 control-label">{{ trans('operational/fields.end-date') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="endDate" name="endDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $endTime !== null ? $endTime->format('d-m-Y') : '' }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="endTime" class="col-sm-4 control-label">{{ trans('shared/common.end-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endHours" name="endHours" disabled>
                                                <?php $hoursFormat = $endTime !== null ? $endTime->format('H') : -1 ; ?>
                                                @for ($i = 0; $i < 24; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hoursFormat == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endMinute" name="endMinute" disabled>
                                                <?php $minuteFormat = $endTime !== null ? $endTime->format('i') : -1 ; ?>
                                                @for ($i = 0; $i < 60; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minuteFormat == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="status" name="status" disabled>
                                                <?php $statusString = count($errors) > 0 ? old('status') : $model->status ?>
                                                @foreach($optionStatus as $status)
                                                <option value="{{ $status }}" {{ $statusString == $status ? 'selected' : '' }}>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>
                                                <?php $stringType = count($errors) > 0 ? old('type') : $model->type; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php 
                                    $partner = $model->partner;
                                    $partnerName = !empty($partner) ? $partner->vendor_name : '';
                                    ?>
                                    <div id="formPartner" class="hidden form-group {{ $errors->has('partnerName') ? 'has-error' : '' }}">
                                        <label for="partnerName" class="col-sm-4 control-label">{{ trans('operational/fields.partner-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="partnerId" name="partnerId" value="{{ count($errors) > 0 ? old('partnerId') : $model->partner_id }}">
                                            <input type="text" class="form-control" id="partnerName" name="partnerName" value="{{ count($errors) > 0 ? old('partnerName') : $partnerName }}" readonly>
                                            <span class="btn input-group-addon" id="modalPartner" data-toggle="{{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? '' : 'modal' }}" data-target="#modal-partner"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('partnerName'))
                                            <span class="help-block">{{ $errors->first('partnerName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="{{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? '' : 'modal' }}" data-target="#modal-driver"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverId'))
                                            <span class="help-block">{{ $errors->first('driverId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelAssistant = $model->assistant;
                                        $assistantName  = !empty($modelAssistant) ? $modelAssistant->driver_name : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('assistantName') ? 'has-error' : '' }}">
                                        <label for="assistantName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }} </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="assistantId" name="assistantId" value="{{ count($errors) > 0 ? old('assistantId') : $model->assistant_id }}">
                                            <input type="text" class="form-control" id="assistantName" name="assistantName" value="{{ count($errors) > 0 ? old('assistantName') : $assistantName }}" readonly>
                                            <span class="btn input-group-addon" id="remove-driver-assistant"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalAssistant" data-toggle="modal" data-target="#modal-assistant"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('assistantName'))
                                            <span class="help-block">{{ $errors->first('assistantName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelTruck = $model->truck;
                                        $policeNumber  = !empty($modelTruck) ? $modelTruck->police_number : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('truckId') ? 'has-error' : '' }}">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" readonly>
                                            <span class="btn input-group-addon" id="modalAssistant" data-toggle="{{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? '' : 'modal' }}" data-target="#modal-truck"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('truckId'))
                                            <span class="help-block">{{ $errors->first('truckId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('deliveryAreaHeader') ? 'has-error' : '' }}">
                                        <label for="deliveryAreaHeader" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="deliveryAreaHeader" name="deliveryAreaHeader" {{ !$model->isOpen() ? 'disabled' : '' }}>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                <?php $currentDeliveryArea = count($errors) > 0 ? old('deliveryAreaHeader') : $model->delivery_area_id; ?>
                                                @foreach($optionDeliveryArea as $deliveryAreaHeader)
                                                <option value="{{ $deliveryAreaHeader->delivery_area_id }}" {{ $deliveryAreaHeader->delivery_area_id == $currentDeliveryArea ? 'selected' : '' }}>{{ $deliveryAreaHeader->delivery_area_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('totalCost') ? 'has-error' : '' }}">
                                        <label for="totalCost" class="col-sm-4 control-label">{{ trans('operational/fields.total-cost') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalCost" name="totalCost" value="{{ $model->totalCost() }}" readonly>
                                            @if($errors->has('totalCost'))
                                            <span class="help-block">{{ $errors->first('totalCost') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('noteHeader') ? 'has-error' : '' }}">
                                        <label for="noteHeader" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="3" id="noteHeader" name="noteHeader" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('noteHeader') : $model->note }}</textarea>
                                            @if($errors->has('noteHeader'))
                                            <span class="help-block">{{ $errors->first('noteHeader') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if($model->status == null || $model->status == DeliveryOrderHeader::OPEN)
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
                                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.delivery-area') }}</th>
                                                    <th>{{ trans('operational/fields.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                                    <th>{{ trans('shared/common.address') }}<hr/>{{ trans('shared/common.telepon') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}<hr/>{{ trans('operational/fields.dimension') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}<hr/>{{ trans('operational/fields.total-send') }}</th>
                                                    <th>{{ trans('operational/fields.delivery-cost') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                    @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                    <tr data-index="{{ $dataIndex }}">
                                                        <td class="text-center"> {{ old('resiNumber')[$i] }} <hr/> {{ old('deliveryArea')[$i] }} </td>
                                                        <td > {{ old('customerName')[$i] }} <hr/> {{ old('receiverName')[$i] }} </td>
                                                        <td > {{ old('address')[$i] }} <hr/> {{ old('phoneNumber')[$i] }} </td>
                                                        <td > {{ old('itemName')[$i] }} </td>
                                                        <td class="text-right"> {{ old('weight')[$i] }} <hr/> {{ old('dimension')[$i] }} </td>
                                                        <td class="text-right"> {{ old('totalColy')[$i] }} <hr/> {{ old('totalSend')[$i] }} </td>
                                                        <td class="text-right"> {{ old('deliveryCost')[$i] }} </td>
                                                        <td > {{ old('descriptionLine')[$i] }} </td>
                                                        <td class="text-center">
                                                            @if($model->status == DeliveryOrderHeader::OPEN)
                                                            <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                            <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                            @endif
                                                            <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                            <input type="hidden" name="resiId[]" value="{{ old('resiId')[$i] }}">
                                                            <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                            <input type="hidden" name="deliveryArea[]" value="{{ old('deliveryArea')[$i] }}">
                                                            <input type="hidden" name="customerName[]" value="{{ old('customerName')[$i] }}">
                                                            <input type="hidden" name="receiverName[]" value="{{ old('receiverName')[$i] }}">
                                                            <input type="hidden" name="address[]" value="{{ old('address')[$i] }}">
                                                            <input type="hidden" name="phoneNumber[]" value="{{ old('phoneNumber')[$i] }}">
                                                            <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                            <input type="hidden" name="totalColy[]" value="{{ old('totalColy')[$i] }}">
                                                            <input type="hidden" name="totalReceipt[]" value="{{ old('totalReceipt')[$i] }}">
                                                            <input type="hidden" name="weight[]" value="{{ old('weight')[$i] }}">
                                                            <input type="hidden" name="dimension[]" value="{{ old('dimension')[$i] }}">
                                                            <input type="hidden" name="totalSend[]" value="{{ old('totalSend')[$i] }}">
                                                            <input type="hidden" name="deliveryCost[]" value="{{ old('deliveryCost')[$i] }}">
                                                            <input type="hidden" name="descriptionLine[]" value="{{ old('descriptionLine')[$i] }}">
                                                        </td>
                                                    </tr>
                                                    <?php $dataIndex++; ?>
                                                    @endfor
                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $resi     = $line->resi;
                                                    $customer = !empty($resi) ? $resi->customer : null;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td class="text-center">
                                                        {{ $resi !== null ? $resi->resi_number : '' }} <hr/>
                                                        {{ $resi->deliveryArea !== null ? $resi->deliveryArea->delivery_area_name : '' }}
                                                    </td>
                                                    <td >
                                                        {{ $customer !== null ? $customer->customer_name : '' }} <hr/>
                                                        {{ $resi !== null ? $resi->receiver_name : '' }}
                                                    </td>
                                                    <td >
                                                        {{ $resi !== null ? $resi->receiver_address : '' }} <hr/>
                                                        {{ $resi !== null ? $resi->receiver_phone : '' }}
                                                    </td>
                                                    <td > {{ $resi !== null ? $resi->item_name : '' }} </td>
                                                    <td class="text-right">
                                                        {{ $resi !== null ? number_format($resi->totalWeightAll(), 2) : '' }}<hr/>
                                                        {{ $resi !== null ? number_format($resi->totalVolumeAll(), 6) : '' }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $resi !== null ? number_format($resi->totalColy()) : '' }}<hr/>
                                                        {{ $line !== null ? number_format($line->total_coly) : '' }}
                                                    </td>
                                                    <td class="text-right"> {{ $line !== null ? number_format($line->delivery_cost) : '' }} </td>
                                                    <td > {{ $line !== null ? $line->description : '' }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == DeliveryOrderHeader::OPEN)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->delivery_order_line_id }}">
                                                        <input type="hidden" name="resiId[]" value="{{ $resi !== null ? $resi->resi_header_id : '' }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ $resi !== null ? $resi->resi_number : '' }}">
                                                        <input type="hidden" name="deliveryArea[]" value="{{ $resi->deliveryArea !== null ? $resi->deliveryArea->delivery_area_name : '' }}">
                                                        <input type="hidden" name="customerName[]" value="{{ $resi !== null ? $resi->customer_name : '' }}">
                                                        <input type="hidden" name="receiverName[]" value="{{ $resi !== null ? $resi->receiver_name : '' }}">
                                                        <input type="hidden" name="address[]" value="{{ $resi !== null ? $resi->receiver_address : '' }}">
                                                        <input type="hidden" name="phoneNumber[]" value="{{ $resi !== null ? $resi->receiver_phone : '' }}">
                                                        <input type="hidden" name="itemName[]" value="{{ $resi !== null ? $resi->item_name : '' }}">
                                                        <input type="hidden" name="totalColy[]" value="{{ $resi !== null ? $resi->totalColy() : '' }}">
                                                        <input type="hidden" name="totalReceipt[]" value="{{ $resi !== null ? $resi->totalReceipt() : '' }}">
                                                        <input type="hidden" name="weight[]" value="{{ $resi !== null ? $resi->totalWeightAll() : '' }}">
                                                        <input type="hidden" name="dimension[]" value="{{ $resi !== null ? $resi->totalVolumeAll() : '' }}">
                                                        <input type="hidden" name="totalSend[]" value="{{ $line !== null ? $line->total_coly : '' }}">
                                                        <input type="hidden" name="deliveryCost[]" value="{{ $line !== null ? $line->delivery_cost : '' }}">
                                                        <input type="hidden" name="descriptionLine[]" value="{{ $line !== null ? $line->description : '' }}">
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
                                @if($model->status == null || $model->status == DeliveryOrderHeader::OPEN)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if(Gate::check('access', [$resource, 'request-approval']) && $model->status == DeliveryOrderHeader::OPEN)
                                <button type="submit" name="btn-request-approval" class="btn btn-sm btn-info">
                                    <i class="fa fa-send"></i> {{ trans('shared/common.request-approval') }}
                                </button>
                                @endif
                                @if(in_array($model->status, DeliveryOrderHeader::canPrint()))
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->delivery_order_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
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
                                    <div class="form-group {{ $errors->has('resiNumber') ? 'has-error' : '' }}">
                                        <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="resiId" id="resiId" value="">
                                                <input type="text" class="form-control" id="resiNumber" name="resiNumber" readonly>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-resi"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverName" name="receiverName" value="" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="deliveryArea" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="deliveryArea" name="deliveryArea" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="0" disabled>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemName" name="itemName" value="" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalColy" name="totalColy" value="0" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group hidden">
                                        <label for="totalReceipt" class="col-sm-4 control-label">{{ trans('operational/fields.total-receipt') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalReceipt" name="totalReceipt" value="0" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="weight" class="col-sm-4 control-label">{{ trans('operational/fields.weight') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control decimal text-right" id="weight" name="weight" value="0" disabled="disabled">
                                            <span class="help-block"></span>
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
                                        <label for="totalSend" class="col-sm-4 control-label">{{ trans('operational/fields.total-send') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalSend" name="totalSend" value="0" >
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="deliveryCost" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-cost') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="deliveryCost" name="deliveryCost" value="0" disabled>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="descriptionLine" class="col-sm-4 control-label">{{ trans('shared/common.description') }} </label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="descriptionLine" name="descriptionLine"></textarea>
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
                            <th>{{ trans('operational/fields.resi-number') }} <hr/> {{ trans('operational/fields.delivery-area') }}</th>
                            <th>{{ trans('operational/fields.customer-name') }} <hr/> {{ trans('operational/fields.receiver-name') }}</th>
                            <th>{{ trans('operational/fields.address') }} <hr/> {{ trans('operational/fields.phone') }}</th>
                            <th>{{ trans('inventory/fields.item') }}</th>
                            <th>{{ trans('operational/fields.total-coly') }}</th>
                            <th>{{ trans('operational/fields.coly-wh') }} <hr/> {{ trans('operational/fields.coly-available') }}</th>
                            <th>{{ trans('shared/common.note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionResi as $resi)
                        <tr style="cursor: pointer;" data-resi="{{ json_encode($resi) }}">
                            <td>{{ $resi->resi_number }} <hr/> {{ $resi->delivery_area_name }}</td>
                            <td>{{ $resi->customer_name }} <hr/> {{ $resi->receiver_name }}</td>
                            <td>{{ $resi->receiver_address }} <hr/> {{ $resi->receiver_phone }}</td>
                            <td>{{ $resi->item_name }}</td>
                            <td class="text-right">{{ $resi->total_coly }}</td>
                            <td class="text-right">{{ $resi->total_receipt }} <hr/> {{ $resi->total_available }}</td>
                            <td>{{ $resi->wdl_note }}</td>
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
<div id="modal-draft-do" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Draft Delivery Order</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-draft-do" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.draft-do-number') }}</th>
                            <th>{{ trans('operational/fields.truck') }}</th>
                            <th>{{ trans('operational/fields.driver') }}</th>
                            <th>{{ trans('operational/fields.assistant') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('operational/fields.number-of-resi') }}</th>
                            <th>{{ trans('operational/fields.number-available-resi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionDraftDo as $draftDo)
                        <tr style="cursor: pointer;" data-draft="{{ json_encode($draftDo) }}">
                            <td>{{ $draftDo->draft_delivery_order_number }}</td>
                            <td>{{ $draftDo->police_number }}</td>
                            <td>{{ $draftDo->driver_name }}</td>
                            <td>{{ $draftDo->assistant_name }}</td>
                            <td>{{ $draftDo->note }}</td>
                            <td>{{ $draftDo->count_resi }} Resi</td>
                            <td>{{ $draftDo->count_available }} Resi</td>
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
                            <td>{{ $driver->driver_nickname }}</td>
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
<div id="modal-partner" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Partner List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-partner" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.code') }}</th>
                            <th>{{ trans('shared/common.name') }}</th>
                            <th>{{ trans('operational/fields.address') }}</th>
                            <th>{{ trans('operational/fields.phone') }}</th>
                            <th>{{ trans('operational/fields.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionPartner as $partner)
                        <tr style="cursor: pointer;" data-partner="{{ json_encode($partner) }}">
                            <td>{{ $partner->vendor_code }}</td>
                            <td>{{ $partner->vendor_name }}</td>
                            <td>{{ $partner->address }}</td>
                            <td>{{ $partner->phone_number }}</td>
                            <td>{{ $partner->description }}</td>
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
<div id="modal-assistant" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Assistant Driver List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-assistant" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.code') }}</th>
                            <th>{{ trans('shared/common.name') }}</th>
                            <th>{{ trans('operational/fields.nickname') }}</th>
                            <th>{{ trans('shared/common.category') }}</th>
                            <th>{{ trans('shared/common.type') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionDriver as $assistant)
                        <tr style="cursor: pointer;" data-assistant="{{ json_encode($assistant) }}">
                            <td>{{ $assistant->driver_code }}</td>
                            <td>{{ $assistant->driver_name }}</td>
                            <td>{{ $assistant->driver_nickname }}</td>
                            <td>{{ $assistant->driver_category }}</td>
                            <td>{{ $assistant->type }}</td>
                            <td>{{ $assistant->address }}</td>
                            <td>{{ $assistant->description }}</td>
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
                            <th>{{ trans('shared/common.type') }}</th>
                            <th>{{ trans('operational/fields.brand') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionTruck as $truck)
                        <tr style="cursor: pointer;" data-truck="{{ json_encode($truck) }}">
                            <td>{{ $truck->truck_code }}</td>
                            <td>{{ $truck->police_number }}</td>
                            <td>{{ $truck->owner_name }}</td>
                            <td>{{ $truck->truck_type }}</td>
                            <td>{{ $truck->truck_brand }}</td>
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
    $(document).on('ready', function(){
        if ($('#type').val() == '{{ DeliveryOrderHeader::TRANSITION }}') {
                $('#formPartner').removeClass('hidden');
                $('#partnerId').val('');
                $('#partnerName').val('');
            }
        $('#type').on('change', function(){
            if ($('#type').val() == '{{ DeliveryOrderHeader::TRANSITION }}') {
                $('#formPartner').removeClass('hidden');
            }else{
                $('#formPartner').addClass('hidden');
                $('#partnerId').val('');
                $('#partnerName').val('');
            }
        });

        $('#remove-driver-assistant').on('click', removeDriverAssistant);
        $('#remove-draft-do').on('click', removeDraftDo);

        $('#save-line').on('click', saveLine);
        $('.delete-line').on('click', deleteLine);
        $('.edit-line').on('click', editLine);
        $('#cancel-save-line').on('click', cancelSaveLine);
        $('#clear-lines').on('click', clearLines);
        $('.add-line').on('click', addLine);


        $("#datatables-driver").dataTable({
            "pageLength" : 10,
            "lengthChange": false,
            "bSort": false
        });

        $('#datatables-driver tbody').on('click', 'tr', function () {
            var driver = $(this).data('driver');

            $('#driverId').val(driver.driver_id);
            $('#driverName').val(driver.driver_name);
            
            $('#modal-driver').modal('hide');
        });

        $("#datatables-assistant").dataTable({
            "pageLength" : 10,
            "lengthChange": false,
            "bSort": false
        });

        $("#datatables-draft-do").dataTable({
            "pageLength" : 10,
            "lengthChange": false,
            "bSort": false
        });

        $('#datatables-assistant tbody').on('click', 'tr', function () {
            var assistant = $(this).data('assistant');

            $('#assistantId').val(assistant.driver_id);
            $('#assistantName').val(assistant.driver_name);
            
            $('#modal-assistant').modal('hide');
        });

        $("#datatables-truck").dataTable({
            "pageLength" : 10,
            "lengthChange": false,
            "bSort": false
        });

        $('#datatables-truck tbody').on('click', 'tr', function () {
            var truck = $(this).data('truck');

            $('#truckId').val(truck.truck_id);
            $('#policeNumber').val(truck.police_number);
            
            $('#modal-truck').modal('hide');
        });

        $("#datatables-partner").dataTable({
            "pageLength" : 10,
            "lengthChange": false,
            "bSort": false
        });

        $('#datatables-partner tbody').on('click', 'tr', function () {
            var partner = $(this).data('partner');

            $('#partnerId').val(partner.vendor_id);
            $('#partnerName').val(partner.vendor_name);
            
            $('#modal-partner').modal('hide');
        });
    
        $("#datatables-resi").dataTable({
            "pageLength" : 10,
            "lengthChange": false,
            "bSort": false
        });

        $('#datatables-resi tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            if(checkDeliveryExist(resi.resi_header_id)){
                $('#modal-alert').find('.alert-message').html('{{ trans('operational/fields.resi-exist') }}');
                $('#modal-alert').modal('show');
                return;
            }

            $('#resiId').val(resi.resi_header_id);
            $('#resiNumber').val(resi.resi_number);
            $('#deliveryArea').val(resi.delivery_area_name);
            $('#customerName').val(resi.customer_name);
            $('#receiverName').val(resi.receiver_name);
            $('#address').val(resi.receiver_address);
            $('#phoneNumber').val(resi.receiver_phone);
            $('#itemName').val(resi.item_name);
            $('#totalColy').val(resi.total_coly);
            $('#totalReceipt').val(resi.total_receipt);
            $('#weight').val(resi.total_weight);
            $('#dimension').val(resi.total_volume);
            $('#totalSend').val(resi.total_available);
            $('#descriptionLine').val(resi.wdl_note);

            // $('#totalSend').autoNumeric('update', {mDec: 0, vMax: resi.total_receipt});
            $('#totalColy').autoNumeric('update', {mDec: 0});
            $('#totalReceipt').autoNumeric('update', {mDec: 0});
            $('#weight').autoNumeric('update', {mDec: 2});
            $('#dimension').autoNumeric('update', {mDec: 6});

            $('#modal-resi').modal('hide');
        });

        $('#datatables-draft-do tbody').on('click', 'tr', function () {
            var draftDo = $(this).data('draft');
            
            $('#draftDoHeaderId').val(draftDo.draft_delivery_order_header_id);
            $('#draftDoNumber').val(draftDo.draft_delivery_order_number);
            $('#driverId').val(draftDo.driver_id);
            $('#assistantId').val(draftDo.assistant_id);
            $('#driverName').val(draftDo.driver_name);
            $('#assistantName').val(draftDo.assistant_name);
            $('#truckId').val(draftDo.truck_id);
            $('#policeNumber').val(draftDo.police_number);
            $('#noteHeader').val(draftDo.note);

            clearLines();

            $(draftDo.lines).each(function(index, line) {
              var htmlTr = '<td class="text-center" >' + line.resi_number + '<hr/>' + line.delivery_area + '</td>' +
                '<td >' + line.customer_name + '<hr/>' + line.receiver_name + '</td>' +
                '<td >' + line.receiver_address + '<hr/>' + line.receiver_phone + '</td>' +
                '<td >' + line.item_name + '</td>' +
                '<td class="text-right">' + line.total_weight + '<hr/>' + line.total_volume + '</td>' +
                '<td class="text-right">' + line.total_coly_resi + '<hr/>' + line.total_coly + '</td>' +
                '<td class="text-right">' + '' + '</td>' +
                '<td >' + line.description + '</td>' +
                '<td class="text-center">' +
                '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
                '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
                '<input type="hidden" name="lineId[]" value="">' +
                '<input type="hidden" name="resiId[]" value="' + line.resi_header_id + '">' +
                '<input type="hidden" name="resiNumber[]" value="' + line.resi_number + '">' +
                '<input type="hidden" name="deliveryArea[]" value="' + line.delivery_area + '">' +
                '<input type="hidden" name="receiverName[]" value="' + line.receiver_name + '">' +
                '<input type="hidden" name="customerName[]" value="' + line.customer_name + '">' +
                '<input type="hidden" name="address[]" value="' + line.receiver_address + '">' +
                '<input type="hidden" name="phoneNumber[]" value="' + line.receiver_phone + '">' +
                '<input type="hidden" name="itemName[]" value="' + line.item_name + '">' +
                '<input type="hidden" name="totalColy[]" value="' + line.total_coly_resi + '">' +
                '<input type="hidden" name="totalReceipt[]" value="' + line.total_receipt + '">' +
                '<input type="hidden" name="weight[]" value="' + line.total_weight + '">' +
                '<input type="hidden" name="dimension[]" value="' + line.totdal_volume + '">' +
                '<input type="hidden" name="totalSend[]" value="' + line.total_coly + '">' +
                '<input type="hidden" name="deliveryCost[]" value="0">' +
                '<input type="hidden" name="descriptionLine[]" value="'+ line.description +'">' +
                '</td>';

            $('#table-line tbody').append(
                '<tr data-index="' + dataIndex + '">' + htmlTr + '</tr>'
            );
            dataIndex++;

            $('.edit-line').on('click', editLine);
            $('.delete-line').on('click', deleteLine);

            clearFormLine();
            calculateTotal();

            dataIndex++;

            });

            $('#modal-draft-do').modal('hide');
        });

    });

    var removeDriverAssistant = function() {
        $('#assistantId').val('');
        $('#assistantName').val('');
    };

    var removeDraftDo = function() {
        $('#draftDoHeaderId').val('');
        $('#draftDoNumber').val('');
        $('#driverId').val('');
        $('#driverName').val('');
        $('#assistantId').val('');
        $('#assistantName').val('');
        $('#truckId').val('');
        $('#policeNumber').val('');
        $('#noteHeader').val('');
        clearLines();
    };

    var checkDeliveryExist = function(resiId) {
        var exist = false;
        $('#table-line tbody tr').each(function (i, row) {
            var resiHeaderId = parseFloat($(row).find('[name="resiId[]"]').val().split(',').join(''));
            if (resiId == resiHeaderId) {
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
        $('#deliveryArea').val('');
        $('#deliveryRequestId').val('');
        $('#deliveryRequestNumber').val('');
        $('#receiverName').val('');
        $('#customerName').val('');
        $('#address').val('');
        $('#itemName').val('');
        $('#phoneNumber').val('');
        $('#totalColy').val('');
        $('#weight').val('');
        $('#dimension').val('');
        $('#totalSend').val('');
        $('#deliveryCost').val('');
        $('#descriptionLine').val('');

        $('#deliveryRequestNumber').parent().parent().parent().removeClass('has-error');
        $('#deliveryRequestNumber').parent().parent().find('span.help-block').html('');
        $('#deliveryCost').parent().parent().removeClass('has-error');
        $('#deliveryCost').parent().find('span.help-block').html('');
    };

    var saveLine = function() {
        var dataIndexForm = $('#dataIndexForm').val();
        var lineId = $('#lineId').val();
        var resiId = $('#resiId').val();
        var resiNumber = $('#resiNumber').val();
        var deliveryArea = $('#deliveryArea').val();
        var receiverName = $('#receiverName').val();
        var customerName = $('#customerName').val();
        var address = $('#address').val();
        var phoneNumber = $('#phoneNumber').val();
        var itemName = $('#itemName').val();
        var totalColy = $('#totalColy').val();
        var totalReceipt = $('#totalReceipt').val();
        var weight = $('#weight').val();
        var dimension = $('#dimension').val();
        var totalSend = $('#totalSend').val();
        var deliveryCost = $('#deliveryCost').val();
        var descriptionLine = $('#descriptionLine').val();
        var error = false;

        if (resiNumber == '' || resiId == '') {
            $('#resiNumber').parent().parent().parent().addClass('has-error');
            $('#resiNumber').parent().parent().find('span.help-block').html('Choose resi first');
            error = true;
        } else {
            $('#resiNumber').parent().parent().parent().removeClass('has-error');
            $('#resiNumber').parent().parent().find('span.help-block').html('');
        }

        if (address == '') {
            $('#address').parent().parent().addClass('has-error');
            $('#address').parent().find('span.help-block').html('Address is required');
            error = true;
        } else {
            $('#address').parent().parent().removeClass('has-error');
            $('#address').parent().find('span.help-block').html('');
        }

        if (phoneNumber == '') {
            $('#phoneNumber').parent().parent().addClass('has-error');
            $('#phoneNumber').parent().find('span.help-block').html('Phone number is required');
            error = true;
        } else {
            $('#phoneNumber').parent().parent().removeClass('has-error');
            $('#phoneNumber').parent().find('span.help-block').html('');
        }

        if (totalSend == '' || totalSend == 0 ) {
            $('#totalSend').parent().parent().addClass('has-error');
            $('#totalSend').parent().find('span.help-block').html('Coly Send is required');
            error = true;
        } else {
            $('#totalSend').parent().parent().removeClass('has-error');
            $('#totalSend').parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        var htmlTr = '<td class="text-center" >' + resiNumber + '<hr/>' + deliveryArea + '</td>' +
            '<td >' + customerName + '<hr/>' + receiverName + '</td>' +
            '<td >' + address + '<hr/>' + phoneNumber + '</td>' +
            '<td >' + itemName + '</td>' +
            '<td class="text-right">' + weight + '<hr/>' + dimension + '</td>' +
            '<td class="text-right">' + totalColy + '<hr/>' + totalSend + '</td>' +
            '<td class="text-right">' + deliveryCost + '</td>' +
            '<td >' + descriptionLine + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="resiId[]" value="' + resiId + '">' +
            '<input type="hidden" name="resiNumber[]" value="' + resiNumber + '">' +
            '<input type="hidden" name="deliveryArea[]" value="' + deliveryArea + '">' +
            '<input type="hidden" name="receiverName[]" value="' + receiverName + '">' +
            '<input type="hidden" name="customerName[]" value="' + customerName + '">' +
            '<input type="hidden" name="address[]" value="' + address + '">' +
            '<input type="hidden" name="phoneNumber[]" value="' + phoneNumber + '">' +
            '<input type="hidden" name="itemName[]" value="' + itemName + '">' +
            '<input type="hidden" name="totalColy[]" value="' + totalColy + '">' +
            '<input type="hidden" name="totalReceipt[]" value="' + totalReceipt + '">' +
            '<input type="hidden" name="weight[]" value="' + weight + '">' +
            '<input type="hidden" name="dimension[]" value="' + dimension + '">' +
            '<input type="hidden" name="totalSend[]" value="' + totalSend + '">' +
            '<input type="hidden" name="deliveryCost[]" value="' + deliveryCost + '">' +
            '<input type="hidden" name="descriptionLine[]" value="' + descriptionLine + '">' +
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
        var dataIndexForm = $(this).parent().parent().data('index');
        var lineId = $(this).parent().parent().find('[name="lineId[]"]').val();
        var resiId = $(this).parent().parent().find('[name="resiId[]"]').val();
        var resiNumber = $(this).parent().parent().find('[name="resiNumber[]"]').val();
        var deliveryArea = $(this).parent().parent().find('[name="deliveryArea[]"]').val();
        var receiverName = $(this).parent().parent().find('[name="receiverName[]"]').val();
        var customerName = $(this).parent().parent().find('[name="customerName[]"]').val();
        var address = $(this).parent().parent().find('[name="address[]"]').val();
        var phoneNumber = $(this).parent().parent().find('[name="phoneNumber[]"]').val();
        var itemName = $(this).parent().parent().find('[name="itemName[]"]').val();
        var totalColy = $(this).parent().parent().find('[name="totalColy[]"]').val();
        var totalReceipt = $(this).parent().parent().find('[name="totalReceipt[]"]').val();
        var weight = $(this).parent().parent().find('[name="weight[]"]').val();
        var dimension = $(this).parent().parent().find('[name="dimension[]"]').val();
        var totalSend = $(this).parent().parent().find('[name="totalSend[]"]').val();
        var deliveryCost = $(this).parent().parent().find('[name="deliveryCost[]"]').val();
        var descriptionLine = $(this).parent().parent().find('[name="descriptionLine[]"]').val();

        clearFormLine();
        // console.log(receiverName);
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#resiId').val(resiId);
        $('#resiNumber').val(resiNumber);
        $('#deliveryArea').val(deliveryArea);
        $('#receiverName').val(receiverName);
        $('#customerName').val(customerName);
        $('#address').val(address);
        $('#phoneNumber').val(phoneNumber);
        $('#itemName').val(itemName);
        $('#totalColy').val(totalColy);
        $('#totalReceipt').val(totalReceipt);
        $('#weight').val(weight);
        $('#dimension').val(dimension);
        $('#totalSend').val(totalSend);
        $('#deliveryCost').val(deliveryCost);
        $('#descriptionLine').val(descriptionLine);

        $('#totalColy').autoNumeric('update', {mDec: 0});
        $('#totalReceipt').autoNumeric('update', {mDec: 0});
        $('#weight').autoNumeric('update', {mDec: 2});
        $('#dimension').autoNumeric('update', {mDec: 6});
        $('#deliveryCost').autoNumeric('update', {mDec: 0});
        // $('#totalSend').autoNumeric('update', {mDec: 0, vMax: totalReceipt});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
        calculateTotal();
    };

    var calculateTotal = function() {
        var totalCost = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var deliveryCost = parseFloat($(row).find('[name="deliveryCost[]"]').val().split(',').join(''));
            totalCost += deliveryCost;
        });

        $('#totalCost').val(totalCost);
        $('#totalCost').autoNumeric('update', {mDec: 0});
    };


</script>
@endsection
