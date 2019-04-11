@extends('layouts.master')

@section('title', trans('operational/menu.receipt-or-return-delivery-order'))

<?php use  App\Modules\Operational\Model\Transaction\ReceiptOrReturnDeliveryLine;?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.receipt-or-return-delivery-order') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->receipt_or_return_delivery_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-10 portlets">
                                    <div class="form-group {{ $errors->has('receiptOrReturnNumber') ? 'has-error' : '' }}">
                                        <label for="receiptOrReturnNumber" class="col-sm-4 control-label">{{ trans('operational/fields.receipt-or-return-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="receiptOrReturnNumber" name="receiptOrReturnNumber" class="form-control" value="{{ $model->receipt_or_return_delivery_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php $createdDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group {{ $errors->has('createdDate') ? 'has-error' : '' }}">
                                        <label for="createdDate" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="createdDate" name="createdDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $createdDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('deliveryOrderId') ? 'has-error' : '' }}">
                                        <label for="deliveryOrderId" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <?php $deliveryOrderNumber = !empty($model->deliveryOrder) ? $model->deliveryOrder->delivery_order_number : '' ?>
                                            <div class="input-group">
                                                <input type="hidden" id="deliveryOrderId" name="deliveryOrderId" value="{{ count($errors) > 0 ? old('deliveryOrderId') : $model->delivery_order_header_id }}">
                                                <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ count($errors) > 0 ? old('deliveryOrderNumber') : $deliveryOrderNumber }}" readonly>
                                                <span class="btn input-group-addon" id="{{ empty($model->receipt_or_return_delivery_header_id) ? 'show-lov-delivery-order' : ''}}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('deliveryOrderId'))
                                            <span class="help-block">{{ $errors->first('deliveryOrderId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php $doType = !empty($model->deliveryOrder) ? $model->deliveryOrder->type : '' ?>
                                    <div class="form-group">
                                        <label for="doType" class="col-sm-4 control-label">{{ trans('shared/common.type') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="doType" name="doType" value="{{ count($errors) > 0 ? old('doType') : $doType }}" readonly>
                                        </div>
                                    </div>
                                    <?php $policeNumber = !empty($model->deliveryOrder->truck) ? $model->deliveryOrder->truck->police_number : '' ?>
                                    <div class="form-group">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" readonly>
                                        </div>
                                    </div>
                                    <?php $driver = !empty($model->deliveryOrder->driver) ? $model->deliveryOrder->driver->driver_code.' - '.$model->deliveryOrder->driver->driver_name : '' ?>
                                    <div class="form-group">
                                        <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driver" name="driver" value="{{ count($errors) > 0 ? old('driver') : $driver }}" readonly>
                                        </div>
                                    </div>
                                    <?php $driverAssistant = !empty($model->deliveryOrder->driverAssistant) ? $model->deliveryOrder->driverAssistant->driver_code.' - '.$model->deliveryOrder->driverAssistant->driver_name : '' ?>
                                    <div class="form-group">
                                        <label for="driverAssistant" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverAssistant" name="driverAssistant" value="{{ count($errors) > 0 ? old('driverAssistant') : $driverAssistant }}" readonly>
                                        </div>
                                    </div>
                                    <?php $partner = !empty($model->deliveryOrder->partner) ? $model->deliveryOrder->partner->vendor_code.' - '.$model->deliveryOrder->partner->vendor_name : '' ?>
                                    <div class="form-group">
                                        <label for="partner" class="col-sm-4 control-label">{{ trans('payable/fields.vendor') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="partner" name="partner" value="{{ count($errors) > 0 ? old('partner') : $partner }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="3" id="note" name="note" {{ !
                                            empty($model->receipt_or_return_delivery_header_id) ? 'readonly' : ''}}>{{ count($errors) > 0 ? old('note') : $model->note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabLine">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th width="10%">{{ trans('operational/fields.resi-number') }}</th>
                                                    <th width="15%">{{ trans('operational/fields.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                                    <th width="15%">{{ trans('shared/common.address') }}<hr/>{{ trans('shared/common.description') }}</th>
                                                    <th width="10%">{{ trans('shared/common.status') }}</th>
                                                    <th width="10%">{{ trans('operational/fields.total-coly') }}</th>
                                                    <th width="10%">{{ trans('operational/fields.received-by') }}</th>
                                                    <th width="10%">{{ trans('operational/fields.received-date') }}</th>
                                                    <th width="20%">{{ trans('shared/common.note') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(count($errors) > 0)
                                                    @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                        <tr>
                                                            <td >{{ old('resiNumber')[$i] }}</td>
                                                            <td >{{ old('customerReceiver')[$i] }}<hr/>{{ old('receiver')[$i] }}</td>
                                                            <td >{{ old('receiverAddress')[$i] }}<hr/>{{ old('doLineDescription')[$i] }}</td>
                                                            <td >
                                                                <select name="status[]" class="form-control">
                                                                    <option value=""></option>
                                                                    @foreach($optionStatus as $option)
                                                                        <option value="{{ $option }}" {{ $option == old('status')[$i] ? 'selected' : '' }}>{{ $option }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td >
                                                                <input type="text" name="totalColy[]"  min="1" max="{{ old('totalColyHidden')[$i] }}" class="form-control currency" value="{{ old('totalColy')[$i] }}" {{ old('status')[$i] != ReceiptOrReturnDeliveryLine::RECEIVED ? 'readonly' : '' }} />
                                                            </td>
                                                            <td >
                                                                <input type="text" name="receivedBy[]" class="form-control" value="{{ old('receivedBy')[$i] }}" {{ old('status')[$i] != ReceiptOrReturnDeliveryLine::RECEIVED ? 'readonly' : '' }}/>
                                                            </td>
                                                            <td >
                                                                <div class="input-group">
                                                                    <input type="text" name="receivedDate[]" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ old('receivedDate')[$i] }}" {{ old('status')[$i] != ReceiptOrReturnDeliveryLine::RECEIVED ? 'readonly' : '' }}>
                                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input name="noteLine[]" class="form-control currency" value="{{ old('noteLine')[$i] }}" />
                                                            </td>
                                                            <td class="hidden">
                                                                <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                                <input type="hidden" name="totalColyHidden[]" value="{{ old('totalColyHidden')[$i] }}">
                                                                <input type="hidden" name="deliveryOrderLineId[]" value="{{ old('deliveryOrderLineId')[$i] }}">
                                                                <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                                <input type="hidden" name="customerReceiver[]" value="{{ old('customerReceiver')[$i] }}">
                                                                <input type="hidden" name="receiver[]" value="{{ old('receiver')[$i] }}">
                                                                <input type="hidden" name="receiverAddress[]" value="{{ old('receiverAddress')[$i] }}">
                                                                <input type="hidden" name="doLineDescription[]" value="{{ old('doLineDescription')[$i] }}">
                                                            </td>
                                                        </tr>
                                                    @endfor

                                                @else
                                                    @foreach($model->lines as $line)
                                                        <?php
                                                            $receivedDate = !empty($line->received_date) ? new \DateTime($line->received_date) : null;
                                                        ?>
                                                        <tr >
                                                            <td >{{ !empty($line->deliveryOrderLine->resi) ? $line->deliveryOrderLine->resi->resi_number : '' }}</td>
                                                            <td >
                                                                {{ !empty($line->deliveryOrderLine->resi->customerReceiver) ? $line->deliveryOrderLine->resi->customerReceiver->customer_name : '' }}<hr/>
                                                                {{ !empty($line->deliveryOrderLine->resi) ? $line->deliveryOrderLine->resi->receiver : '' }}
                                                            </td>
                                                            <td >
                                                                {{ !empty($line->deliveryOrderLine->resi) ? $line->deliveryOrderLine->resi->receiver_address : '' }}<hr/>
                                                                {{ !empty($line->deliveryOrderLine) ? $line->deliveryOrderLine->description : '' }}
                                                            </td>
                                                            <td >
                                                                <select name="status[]" class="form-control" disabled>
                                                                    <option value=""></option>
                                                                    @foreach($optionStatus as $option)
                                                                        <option value="{{ $option }}" {{ $option == $line->status ? 'selected' : '' }}>{{ $option }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td >
                                                                <input type="text" name="totalColy[]" class="form-control currency" value="{{ $line->total_coly }}" disabled/>
                                                            </td>
                                                            <td >
                                                                <input type="text" name="receivedBy[]" class="form-control" value="{{ $line->received_by }}" disabled/>
                                                            </td>
                                                            <td >
                                                                <div class="input-group">
                                                                    <input type="text" name="receivedDate[]" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $receivedDate !== null ? $receivedDate->format('d-m-Y') : '' }}" disabled>
                                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input name="noteLine[]" class="form-control" value="{{ $line->note }}" disabled/>
                                                            </td>
                                                            <td class="hidden">
                                                                <input type="hidden" name="lineId[]" value="{{ $line->receipt_or_return_line_id }}">
                                                                <input type="hidden" name="deliveryOrderLineId[]" value="{{ $line->delivery_order_line_id }}">
                                                                <input type="hidden" name="resiNumber[]" value="{{ !empty($line->deliveryOrderLine->resi) ? $line->deliveryOrderLine->resi->resi_number : '' }}">
                                                                <input type="hidden" name="customerReceiver[]" value="{{ !empty($line->deliveryOrderLine->resi->customerReceiver) ? $line->deliveryOrderLine->resi->customerReceiver->customer_name : '' }}">
                                                                <input type="hidden" name="receiver[]" value="{{ !empty($line->deliveryOrderLine->resi) ? $line->deliveryOrderLine->resi->receiver : '' }}">
                                                                <input type="hidden" name="receiverAddress[]" value="{{ !empty($line->deliveryOrderLine->resi) ? $line->deliveryOrderLine->resi->receiver_address : '' }}">
                                                                <input type="hidden" name="doLineDescription[]" value="{{ !empty($line->deliveryOrderLine) ? $line->deliveryOrderLine->description : '' }}">
                                                            </td>
                                                        </tr>
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
                                @if (empty($model->receipt_or_return_delivery_header_id))
                                    <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
<div id="modal-lov-delivery-order" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.delivery-order') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchDeliveryOrder" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchDeliveryOrder" name="searchDeliveryOrder">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-delivery-order" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.do-number') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
                                    <th>{{ trans('operational/fields.driver-assistant') }}</th>
                                    <th>{{ trans('payable/fields.partner') }}</th>
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
@endsection


@section('script')
@parent()
<script type="text/javascript">
$(document).on('ready', function(){
    $('#show-lov-delivery-order').on('click', showLovDeliveryOrder);
    $('#searchDeliveryOrder').on('keyup', loadLovDeliveryOrder);
    $('#table-lov-delivery-order tbody').on('click', 'tr', selectDeliveryOrder);

    $('select[name="status[]"]').on('change', changeStatus);
});

var showLovDeliveryOrder = function() {
    $('#searchDeliveryOrder').val('');
    loadLovDeliveryOrder(function() {
        $('#modal-lov-delivery-order').modal('show');
    });
};

var xhrDeliveryOrder;
var loadLovDeliveryOrder = function(callback) {
    if(xhrDeliveryOrder && xhrDeliveryOrder.readyState != 4){
        xhrDeliveryOrder.abort();
    }
    xhrDeliveryOrder = $.ajax({
        url: '{{ URL($url.'/get-json-delivery-order') }}',
        data: {search: $('#searchDeliveryOrder').val()},
        success: function(data) {
            $('#table-lov-delivery-order tbody').html('');
            data.forEach(function(item) {
                var driverAssistantCode = item.driver_assistant_code != null ? item.driver_assistant_code : '';
                var driverAssistantName = item.driver_assistant_code != null ? item.driver_assistant_name : '';
                $('#table-lov-delivery-order tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item).split('\'').join('') + '\'>\
                        <td>' + item.delivery_order_number + '</td>\
                        <td>' + item.type + '</td>\
                        <td>' + item.police_number + '</td>\
                        <td>' + item.driver_code + ' - ' + item.driver_name + '</td>\
                        <td>' + driverAssistantCode + ' - ' + driverAssistantName + '</td>\
                        <td>' + item.vendor_code + ' - ' + item.vendor_name + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectDeliveryOrder = function() {
    var data = $(this).data('json');
    var driverAssistantCode = data.driver_assistant_code != null ? data.driver_assistant_code : '';
    var driverAssistantName = data.driver_assistant_code != null ? data.driver_assistant_name : '';
    $('#deliveryOrderId').val(data.delivery_order_header_id);
    $('#deliveryOrderNumber').val(data.delivery_order_number);
    $('#doType').val(data.type);
    $('#policeNumber').val(data.police_number);
    $('#driver').val(data.driver_code + ' - ' + data.driver_name);
    $('#driverAssistant').val(driverAssistantCode + ' - ' + driverAssistantName);
    $('#partner').val(data.vendor_code + ' - ' + data.vendor_name);

    $('#table-line tbody').html('');
    data.lines.forEach(function(line){
        var customerName = line.customer_name != null ? line.customer_name : '';
        $('#table-line tbody').append(
            '<tr>\
                <td >' + line.resi_number + '</td>\
                <td >' + customerName + '<hr/>' + line.receiver_name + '</td>\
                <td >' + line.receiver_address + '<hr/>' + line.description + '</td>\
                <td >\
                    <select name="status[]" class="form-control">\
                        <option value=""></option>\
                        @foreach($optionStatus as $option)
                            <option value="{{ $option }}">{{ $option }}</option>\
                        @endforeach
                    </select>\
                </td>\
                <td >\
                    <input name="totalColy[]" type="number" class="form-control currency" min="1" max="'+ line.total_coly +'" value="'+ line.total_coly +'" readonly="readonly"/>\
                </td>\
                <td >\
                    <input name="receivedBy[]" class="form-control" value="" readonly="readonly"/>\
                </td>\
                <td >\
                    <div class="input-group">\
                        <input type="text" name="receivedDate[]" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="" readonly="readonly">\
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>\
                    </div>\
                </td>\
                <td>\
                    <input name="noteLine[]" class="form-control" value="" />\
                </td>\
                <td class="hidden">\
                    <input type="hidden" name="lineId[]" value="">\
                    <input type="hidden" name="totalColyHidden[]" value="'+ line.total_coly+'">\
                    <input type="hidden" name="deliveryOrderLineId[]" value="' + line.delivery_order_line_id + '">\
                    <input type="hidden" name="resiNumber[]" value="' + line.resi_number + '">\
                    <input type="hidden" name="customerReceiver[]" value="' + customerName + '">\
                    <input type="hidden" name="receiver[]" value="' + line.receiver_name + '">\
                    <input type="hidden" name="receiverAddress[]" value="' + line.receiver_address + '">\
                    <input type="hidden" name="doLineDescription[]" value="' + line.description + '">\
                </td>\
            </tr>'
        );
    });

    $('select[name="status[]"]').on('change', changeStatus);

    $('.datepicker-input').datepicker();
    $('#modal-lov-delivery-order').modal('hide');

};
var changeStatus = function(){
        var $tr = $(this).parent().parent();
        var status = $(this).val();
        var colySent = $tr.find('input[name="totalColy[]"]').attr('max');

        $tr.find('input[name="receivedBy[]"]').val('');
        $tr.find('input[name="receivedDate[]"]').val('');
        $tr.find('input[name="totalColy[]"]').val(colySent);

        if (status == '{{ ReceiptOrReturnDeliveryLine::RECEIVED }}') {
            $tr.find('input[name="totalColy[]"]').removeAttr('readonly','readonly');
            $tr.find('input[name="receivedBy[]"]').removeAttr('readonly','readonly');
            $tr.find('input[name="receivedDate[]"]').removeAttr('readonly','readonly');
        }else if(status == '{{ ReceiptOrReturnDeliveryLine::RETURNED }}') {
            $tr.find('input[name="totalColy[]"]').attr('readonly','readonly');
            $tr.find('input[name="receivedBy[]"]').attr('readonly','readonly');
            $tr.find('input[name="receivedDate[]"]').attr('readonly','readonly');
        }else{
            $tr.find('input[name="totalColy[]"]').attr('readonly','readonly');
            $tr.find('input[name="receivedBy[]"]').attr('readonly','readonly');
            $tr.find('input[name="receivedDate[]"]').attr('readonly','readonly');
        }
    }
</script>
@endsection
