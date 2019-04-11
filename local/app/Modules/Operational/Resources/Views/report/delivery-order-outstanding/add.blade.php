@extends('layouts.master')

@section('title', trans('operational/menu.delivery-order-outstanding'))

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
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.delivery-order-outstanding') }}</h2>
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
                                    <div class="form-group {{ $errors->has('endTime') ? 'has-error' : '' }}">
                                        <label for="endTime" class="col-sm-4 control-label">{{ trans('shared/common.end-time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="endHours" name="endHours" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>
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
                                            <select class="form-control" id="endMinute" name="endMinute" {{ $model->status != null && $model->status != DeliveryOrderHeader::OPEN ? 'disabled' : '' }}>
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
                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="modal" data-target="#modal-driver"><i class="fa fa-search"></i></span>
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
                                    <div class="form-group {{ $errors->has('policeNumber') ? 'has-error' : '' }}">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" readonly>
                                            <span class="btn input-group-addon" id="modalAssistant" data-toggle="modal" data-target="#modal-truck"><i class="fa fa-search"></i></span>
                                            </div>
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
                                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                                    <th>{{ trans('operational/fields.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                                    <th>{{ trans('shared/common.address') }}<hr/>{{ trans('shared/common.telepon') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}<hr/>{{ trans('operational/fields.dimension') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}<hr/>{{ trans('operational/fields.total-send') }}</th>
                                                    <th>{{ trans('operational/fields.delivery-cost') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th>{{ trans('shared/common.status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $resi     = $line->resi;
                                                    $customer = !empty($resi) ? $resi->customer : null;
                                                    $receiptReturn = $line->receiptReturn;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
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
                                                    <td class="text-center">
                                                        {{ !empty($receiptReturn) ? $receiptReturn->status : 'Outstanding' }}
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>
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
    var dataIndex = {{ $dataIndex }};
    $(document).on('ready', function(){
        if ($('#type').val() == '{{ DeliveryOrderHeader::TRANSITION }}') {
                $('#formPartner').removeClass('hidden');
            }
        $('#type').on('change', function(){
            if ($('#type').val() == '{{ DeliveryOrderHeader::TRANSITION }}') {
                $('#formPartner').removeClass('hidden');
            }else{
                $('#formPartner').addClass('hidden');
            }
        });
        
        $('#save-line').on('click', saveLine);
        $('.delete-line').on('click', deleteLine);
        $('.edit-line').on('click', editLine);
        $('#cancel-save-line').on('click', cancelSaveLine);
        $('#clear-lines').on('click', clearLines);
        $('.add-line').on('click', addLine);


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

        $("#datatables-assistant").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-assistant tbody').on('click', 'tr', function () {
            var assistant = $(this).data('assistant');

            $('#assistantId').val(assistant.driver_id);
            $('#assistantName').val(assistant.driver_name);
            
            $('#modal-assistant').modal('hide');
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

        $("#datatables-partner").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-partner tbody').on('click', 'tr', function () {
            var partner = $(this).data('partner');

            $('#partnerId').val(partner.vendor_id);
            $('#partnerName').val(partner.vendor_name);
            
            $('#modal-partner').modal('hide');
        });
    
        $("#datatables-resi").dataTable({
            "pageLength" : 10,
            "lengthChange": false
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

            // $('#totalSend').autoNumeric('update', {mDec: 0, vMax: resi.total_receipt});
            $('#totalColy').autoNumeric('update', {mDec: 0});
            $('#totalReceipt').autoNumeric('update', {mDec: 0});
            $('#weight').autoNumeric('update', {mDec: 2});
            $('#dimension').autoNumeric('update', {mDec: 6});

            $('#modal-resi').modal('hide');
        });

    });

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

        // if (deliveryCost == '' || deliveryCost <= 0) {
        //     $('#deliveryCost').parent().parent().addClass('has-error');
        //     $('#deliveryCost').parent().find('span.help-block').html('Delivery Cost is required');
        //     error = true;
        // } else {
        //     $('#deliveryCost').parent().parent().removeClass('has-error');
        //     $('#deliveryCost').parent().find('span.help-block').html('');
        // }

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

        var htmlTr = '<td >' + resiNumber + '</td>' +
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
