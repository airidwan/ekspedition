@extends('layouts.master')

@section('title', trans('operational/menu.approve-delivery-order'))

<?php 
    use App\Service\Penomoran; 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader; 
    use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader; 
    use App\Service\TimezoneDateConverter;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.approve-delivery-order') }}</h2>
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
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabApprove">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                        <label for="poNumber" class="col-sm-4 control-label">Note about Approve or Reject <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="note" name="note" rows="4">{{ count($errors) > 0 ? old('note') : '' }}</textarea>
                                            @if($errors->has('note'))
                                            <span class="help-block">{{ $errors->first('note') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
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
                                    if (count($errors) > 0) {
                                        $startTime = !empty(old('startTime')) ? new \DateTime(old('startTime')) : TimezoneDateConverter::getClientDateTime();
                                    } else {
                                        $startTime = !empty($model->delivery_start_time) ? TimezoneDateConverter::getClientDateTime($model->delivery_start_time) :  TimezoneDateConverter::getClientDateTime();
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('startTime') ? 'has-error' : '' }}">
                                        <label for="startTime" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="startTime" name="startTime" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $startTime !== null ? $startTime->format('d-m-Y') : '' }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('startTime'))
                                            <span class="help-block">{{ $errors->first('startTime') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('startTime') ? 'has-error' : '' }}">
                                        <label for="startTime" class="col-sm-4 control-label">{{ trans('shared/common.start-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="startHours" name="startHours" disabled>
                                                <?php $hoursFormat = $startTime !== null ? $startTime->format('H') : -1 ; ?>
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
                                    <div class="form-group {{ $errors->has('endTime') ? 'has-error' : '' }}">
                                        <label for="endTime" class="col-sm-4 control-label">{{ trans('shared/common.end-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endHours" name="endHours" disabled>
                                                <?php $hoursFormat = $endTime !== null ? $endTime->format('H') : -1 ; ?>
                                                <?php $hours = count($errors) > 0 ? old('endHours') : $hoursFormat ; ?>
                                                @for ($i = 0; $i < 24; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hours == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                            @if($errors->has('endHours'))
                                            <span class="help-block">{{ $errors->first('endHours') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endMinute" name="endMinute" disabled>
                                                <?php $minuteFormat = $endTime !== null ? $endTime->format('i') : -1 ; ?>
                                                <?php $minute = count($errors) > 0 ? old('endMinute') : $minuteFormat ; ?>
                                                @for ($i = 0; $i < 60; $i=$i+5)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                            @if($errors->has('endMinute'))
                                            <span class="help-block">{{ $errors->first('endMinute') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ count($errors) > 0 ? old('status') : $model->status }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="type" name="type" value="{{ count($errors) > 0 ? old('type') : $model->type }}" readonly>
                                        </div>
                                    </div>
                                    @if($model->type == DeliveryOrderHeader::TRANSITION)
                                    <div class="form-group">
                                        <label for="partnerName" class="col-sm-4 control-label">{{ trans('operational/fields.partner-name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="partnerName" name="partnerName" value="{{ count($errors) > 0 ? old('partnerName') : $model->partner_name }}" readonly>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="form-group {{ $errors->has('noteHeader') ? 'has-error' : '' }}">
                                        <label for="noteHeader" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="3" id="noteHeader" name="noteHeader" readonly>{{ count($errors) > 0 ? old('noteHeader') : $model->note }}</textarea>
                                            @if($errors->has('noteHeader'))
                                            <span class="help-block">{{ $errors->first('noteHeader') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            <span class="btn input-group-addon" id="modalDriver"  data-target="#modal-driver"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverName'))
                                            <span class="help-block">{{ $errors->first('driverName') }}</span>
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
                                            <span class="btn input-group-addon" id="modalAssistant"  data-target="#modal-assistant"><i class="fa fa-search"></i></span>
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
                                    <div class="form-group {{ $errors->has('policeNumber') ? 'has-error' : '' }}">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" readonly>
                                            @if($errors->has('policeNumber'))
                                            <span class="help-block">{{ $errors->first('policeNumber') }}</span>
                                            @endif
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
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $resi     = $line->resi;
                                                    $customer = !empty($resi) ? $resi->customer : null;
                                                ?>
                                                <tr>
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
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(Gate::check('access', [$resource, 'reject']) && $model->status == DeliveryOrderHeader::REQUEST_APPROVAL)
                                <button type="submit" name="btn-reject" class="btn btn-sm btn-danger">
                                    <i class="fa fa-remove"></i> {{ trans('purchasing/fields.reject') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->status == DeliveryOrderHeader::REQUEST_APPROVAL)
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('purchasing/fields.approve') }}
                                </button>
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

@section('script')
@parent()
<script type="text/javascript">
    

</script>
@endsection
