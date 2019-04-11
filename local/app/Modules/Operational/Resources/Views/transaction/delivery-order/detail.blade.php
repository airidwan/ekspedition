@extends('layouts.master')

@section('title', trans('operational/menu.delivery-order'))

<?php 
    use App\Service\Penomoran; 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader; 
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
                                    <div class="form-group">
                                        <label for="deliveryOrderNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ $model->delivery_order_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $startTime = !empty($model->delivery_start_time) ? new \DateTime($model->delivery_start_time) : new \DateTime();
                                    ?>
                                    <div class="form-group">
                                        <label for="startTime" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="startTime" name="startTime" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $startTime !== null ? $startTime->format('d-m-Y') : '' }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $startTime = !empty($model->delivery_start_time) ? new \DateTime($model->delivery_start_time) : new \DateTime();
                                    ?>
                                    <div class="form-group">
                                        <label for="startTime" class="col-sm-4 control-label">{{ trans('shared/common.start-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="startHours" name="startHours" disabled>
                                                <?php $hoursFormat = $startTime !== null ? $startTime->format('H') : 1 ; ?>
                                                <?php $hours = count($errors) > 0 ? old('startHours') : $hoursFormat ; ?>
                                                @for ($i = 0; $i < 24; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hours == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="startMinute" name="startMinute" disabled>
                                                <?php $minuteFormat = $startTime !== null ? $startTime->format('i') : -1 ; ?>
                                                <?php $minute = count($errors) > 0 ? old('startMinute') : $minuteFormat ; ?>
                                                @for ($i = 0; $i < 60; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $endTime = !empty(old('endTime')) ? new \DateTime(old('endTime')) : null;
                                    } else {
                                        $endTime = !empty($model->delivery_end_time) ? new \DateTime($model->delivery_end_time) : null;
                                    }
                                    ?>
                                    <div class="form-group">
                                        <label for="endTime" class="col-sm-4 control-label">{{ trans('shared/common.end-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endHours" name="endHours" disabled>
                                                <?php $hoursFormat = $endTime !== null ? $endTime->format('H') : -1 ; ?>
                                                <?php $hours = count($errors) > 0 ? old('endHours') : $hoursFormat ; ?>
                                                @for ($i = 0; $i < 24; $i++)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hours == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endMinute" name="endMinute" disabled>
                                                <?php $minuteFormat = $endTime !== null ? $endTime->format('i') : -1 ; ?>
                                                <?php $minute = count($errors) > 0 ? old('endMinute') : $minuteFormat ; ?>
                                                @for ($i = 0; $i < 60; $i=$i+5)
                                                       <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
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
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" disabled>
                                                <?php $stringType = count($errors) > 0 ? old('type') : $model->type; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php 
                                    $partner = $model->partner;
                                    $partnerName = !empty($partner) ? $partner->vendor_name : '';
                                    ?>
                                    <div id="formPartner" class="hidden form-group">
                                        <label for="partnerName" class="col-sm-4 control-label">{{ trans('operational/fields.partner-name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="partnerName" name="partnerName" value="{{ $partnerName }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                        $modelAssistant = $model->assistant;
                                        $assistantName  = !empty($modelAssistant) ? $modelAssistant->driver_name : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="assistantName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="assistantName" name="assistantName" value="{{ $assistantName }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                        $modelTruck = $model->truck;
                                        $policeNumber  = !empty($modelTruck) ? $modelTruck->police_number : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalCost" class="col-sm-4 control-label">{{ trans('operational/fields.total-cost') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalCost" name="totalCost" value="{{ $model->totalCost() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="noteHeader" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="3" id="noteHeader" name="noteHeader" disabled>{{ $model->note }}</textarea>
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
                                                    <th>{{ trans('operational/fields.resi-number') }}</th>
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
                                                        <td > {{ $resi !== null ? $resi->resi_number : '' }} </td>
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
