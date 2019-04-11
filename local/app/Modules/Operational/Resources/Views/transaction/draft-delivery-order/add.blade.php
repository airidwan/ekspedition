@extends('layouts.master')

@section('title', trans('operational/menu.draft-delivery-order'))

<?php 
    use App\Service\Penomoran; 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader; 
    use App\Service\TimezoneDateConverter;
    use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader; 
?>

@section('header')
@parent
<style type="text/css">
    #table-resi tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.draft-delivery-order') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->draft_delivery_order_header_id }}">
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
                                </div>
                                <div class="col-sm-6 portlets">
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
                                                            <input type="hidden" name="weight[]" value="{{ old('weight')[$i] }}">
                                                            <input type="hidden" name="dimension[]" value="{{ old('dimension')[$i] }}">
                                                            <input type="hidden" name="totalSend[]" value="{{ old('totalSend')[$i] }}">
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
                                                        <input type="hidden" name="weight[]" value="{{ $resi !== null ? $resi->totalWeightAll() : '' }}">
                                                        <input type="hidden" name="dimension[]" value="{{ $resi !== null ? $resi->totalVolumeAll() : '' }}">
                                                        <input type="hidden" name="totalSend[]" value="{{ $line !== null ? $line->total_coly : '' }}">
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
                                                <span class="btn input-group-addon" id="modalResi" ><i class="fa fa-search"></i></span>

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
<div id="modal-resi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Resi List</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchResi" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchResi" name="searchResi">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.resi-number') }} <hr/> {{ trans('operational/fields.delivery-area') }}</th>
                                    <th>{{ trans('operational/fields.customer-name') }} <hr/> {{ trans('operational/fields.receiver-name') }}</th>
                                    <th>{{ trans('operational/fields.address') }} <hr/> {{ trans('operational/fields.phone') }}</th>
                                    <th>{{ trans('inventory/fields.item') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                    <th>{{ trans('operational/fields.coly-sent') }}</th>
                                    <th>{{ trans('shared/common.note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
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

        $('#modalResi').on('click', showLovResi);
        $('#searchResi').on('keyup', loadLovResi);
        $('#table-resi tbody').on('click', 'tr', selectResi);
    });

    var showLovResi = function() {
        $('#searchResi').val('');
        loadLovResi(function() {
            $('#modal-resi').modal('show');
        });
    };

    var xhrResi;
    var loadLovResi = function(callback) {
        if(xhrResi && xhrResi.readyState != 4){
            xhrResi.abort();
        }
        xhrResi = $.ajax({
            url: '{{ URL($url.'/get-json-resi') }}',
            data: {search: $('#searchResi').val()},
            success: function(data) {
                $('#table-resi tbody').html('');
                data.forEach(function(resi) {
                    $deliveryAreaName = resi.delivery_area_name ? resi.delivery_area_name : '';
                    $wdlNote = resi.wdl_note ? resi.wdl_note : '';
                    $('#table-resi tbody').append(
                        '<tr data-json=\'' + JSON.stringify(resi) + '\'>\
                            <td>' + resi.resi_number + '<hr> ' + $deliveryAreaName + '</td>\
                            <td>' + resi.customer_name + '<hr/>' + resi.receiver_name + '</td>\
                            <td>' + resi.receiver_address + '<hr/>' + resi.receiver_phone + '</td>\
                            <td>' + resi.item_name + '</td>\
                            <td class="text-right">' + parseInt(resi.total_coly).formatMoney(0) + '</td>\
                            <td class="text-right">' + parseInt(resi.coly_sent).formatMoney(0) + '</td>\
                            <td>' + $wdlNote + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectResi = function() {
        var resi = $(this).data('json');

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
        $('#weight').val(resi.total_weight);
        $('#dimension').val(resi.total_volume);
        $('#totalSend').val(resi.coly_sent);
        $('#descriptionLine').val(resi.wdl_note);

        // $('#totalSend').autoNumeric('update', {mDec: 0, vMax: resi.coly_sent});
        $('#totalColy').autoNumeric('update', {mDec: 0});
        $('#totalSent').autoNumeric('update', {mDec: 0});
        $('#weight').autoNumeric('update', {mDec: 2});
        $('#dimension').autoNumeric('update', {mDec: 6});

        $('#modal-resi').modal('hide');
    };

    var removeDriverAssistant = function() {
        $('#assistantId').val('');
        $('#assistantName').val('');
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
        $('#descriptionLine').val('');

        $('#deliveryRequestNumber').parent().parent().parent().removeClass('has-error');
        $('#deliveryRequestNumber').parent().parent().find('span.help-block').html('');
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
        var weight = $('#weight').val();
        var dimension = $('#dimension').val();
        var totalSend = $('#totalSend').val();
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
            '<input type="hidden" name="weight[]" value="' + weight + '">' +
            '<input type="hidden" name="dimension[]" value="' + dimension + '">' +
            '<input type="hidden" name="totalSend[]" value="' + totalSend + '">' +
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
        var weight = $(this).parent().parent().find('[name="weight[]"]').val();
        var dimension = $(this).parent().parent().find('[name="dimension[]"]').val();
        var totalSend = $(this).parent().parent().find('[name="totalSend[]"]').val();
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
        $('#weight').val(weight);
        $('#dimension').val(dimension);
        $('#totalSend').val(totalSend);
        $('#descriptionLine').val(descriptionLine);

        $('#totalColy').autoNumeric('update', {mDec: 0});
        $('#weight').autoNumeric('update', {mDec: 2});
        $('#dimension').autoNumeric('update', {mDec: 6});
        // $('#totalSend').autoNumeric('update', {mDec: 0, vMax: totalSent});

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
    };


</script>
@endsection
