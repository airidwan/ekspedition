@extends('layouts.master')

@section('title', trans('operational/menu.customer-taking-transact'))

<?php 
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Service\Penomoran; 
use App\Service\TimezoneDateConverter;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.customer-taking-transact') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->customer_taking_transact_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('customerTakingTransactNumber') ? 'has-error' : '' }}">
                                <label for="customerTakingTransactNumber" class="col-sm-4 control-label">{{ trans('operational/fields.customer-taking-transact-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerTakingTransactNumber" name="customerTakingTransactNumber" value="{{ count($errors) > 0 ? old('customerTakingTransactNumber') : $model->customer_taking_transact_number }}" readonly>
                                    @if($errors->has('customerTakingTransactNumber'))
                                    <span class="help-block">{{ $errors->first('customerTakingTransactNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                            } else {
                                $date = !empty($model->customer_taking_transact_time) ? TimezoneDateConverter::getClientDateTime($model->customer_taking_transact_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" {{ !empty($model->customer_taking_id) ? 'disabled' : '' }}>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    @if($errors->has('date'))
                                        <span class="help-block">{{ $errors->first('date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                                <label for="time" class="col-sm-4 control-label">{{ trans('shared/common.time') }}</label>
                                <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                    <select class="form-control" id="hour" name="hour" {{ !empty($model->customer_taking_id) ? 'disabled' : '' }}>
                                        <?php $hourFormat = $date !== null ? $date->format('H') : '00' ; ?>
                                        <?php $hour = count($errors) > 0 ? old('hour') : $hourFormat ; ?>
                                        @for ($i = 0; $i < 24; $i++)
                                               <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hour == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                        @endfor
                                    </select>
                                    @if($errors->has('hour'))
                                    <span class="help-block">{{ $errors->first('hour') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                    <select class="form-control" id="minute" name="minute" {{ !empty($model->customer_taking_id) ? 'disabled' : '' }}>
                                        <?php $minuteFormat = $date !== null ? $date->format('i') : '' ; ?>
                                        <?php $minute = count($errors) > 0 ? old('minute') : $minuteFormat ; ?>
                                        @for ($i = 0; $i < 60; $i=$i+1)
                                               <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                        @endfor
                                    </select>
                                    @if($errors->has('minute'))
                                    <span class="help-block">{{ $errors->first('minute') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            $customerTakingId     = !empty($modelCt) ? $modelCt->customer_taking_id : '';
                            $customerTakingNumber = !empty($modelCt) ? $modelCt->customer_taking_number : '';
                            $modelResi            = !empty($modelCt) ? $modelCt->resi : null; 
                            $resiId               = !empty($modelResi) ? $modelResi->resi_header_id : '' ; 
                            $resiNumber           = !empty($modelResi) ? $modelResi->resi_number : '' ; 
                            $customerName         = !empty($modelResi) ? $modelResi->getCustomerName() : '' ; 
                            $receiverName         = !empty($modelResi) ? $modelResi->receiver_name : '' ; 
                            $address              = !empty($modelResi) ? $modelResi->receiver_address : '' ; 
                            $phoneNumber          = !empty($modelResi) ? $modelResi->receiver_phone : '' ; 
                            $itemName             = !empty($modelResi) ? $modelResi->item_name : '' ; 
                            $weight               = !empty($modelResi) ? $modelResi->totalWeightAll() : '' ; 
                            $dimension            = !empty($modelResi) ? $modelResi->totalVolumeAll() : '' ; 
                            $totalColy            = !empty($modelResi) ? $modelResi->totalColy() : '' ; 
                            $colyWh               = !empty($modelResi) ? $modelResi->totalReceipt() : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('customerTakingId') ? 'has-error' : '' }}">
                                <label for="customerTakingId" class="col-sm-4 control-label">{{ trans('operational/fields.customer-taking-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="customerTakingId" name="customerTakingId" value="{{ count($errors) > 0 ? old('customerTakingId') : $customerTakingId }}">
                                    <input type="text" class="form-control" id="customerTakingNumber" name="customerTakingNumber" value="{{ count($errors) > 0 ? old('customerTakingNumber') : $customerTakingNumber }}" readonly>
                                    <span class="btn input-group-addon" id="{{ !empty($model->customer_taking_id) ? '' : 'modalCustomerTaking' }}" ><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('customerTakingId'))
                                    <span class="help-block">{{ $errors->first('customerTakingId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('resiNumber') ? 'has-error' : '' }}">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                </div>
                                    @if($errors->has('resiNumber'))
                                    <span class="help-block">{{ $errors->first('resiNumber') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('customerName') ? 'has-error' : '' }}">
                                <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer-name') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $customerName }}" readonly>
                                </div>
                                    @if($errors->has('customerName'))
                                    <span class="help-block">{{ $errors->first('customerName') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('receiverName') ? 'has-error' : '' }}">
                                <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver-name') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiverName" name="receiverName" value="{{ count($errors) > 0 ? old('receiverName') : $receiverName }}" readonly>
                                </div>
                                    @if($errors->has('receiverName'))
                                    <span class="help-block">{{ $errors->first('receiverName') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $address }}" readonly>
                                </div>
                                    @if($errors->has('address'))
                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('phoneNumber') ? 'has-error' : '' }}">
                                <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="{{ count($errors) > 0 ? old('phoneNumber') : $phoneNumber }}" readonly>
                                </div>
                                    @if($errors->has('phoneNumber'))
                                    <span class="help-block">{{ $errors->first('phoneNumber') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('itemName') ? 'has-error' : '' }}">
                                <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemName" name="itemName" value="{{ count($errors) > 0 ? old('itemName') : $itemName }}" readonly>
                                </div>
                                    @if($errors->has('itemName'))
                                    <span class="help-block">{{ $errors->first('itemName') }}</span>
                                    @endif
                            </div>
                            <?php 
                                if (empty($model->customer_taking_transact_id)) {
                                    $resiHeaderId = !empty($modelCt) ? $modelCt->resi_header_id : 0;
                                    $resiStock    = ResiStock::where('resi_header_id', '=', $resiHeaderId)->where('branch_id', '=', \Session::get('currentBranch')->branch_id)->first();
                                    $colyTaken  = !empty($resiStock) ? $resiStock->coly : '';
                                }else{
                                    $colyTaken   = $model->coly_taken;
                                }
                            ?>
                            <div class="form-group {{ $errors->has('colyTaken') ? 'has-error' : '' }}">
                                <label for="colyTaken" class="col-sm-4 control-label">{{ trans('operational/fields.coly-taken') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="number" min="1" class="currency form-control" id="colyTaken" name="colyTaken" value="{{ count($errors) > 0 ? old('colyTaken') : $colyTaken }}" {{ !empty($model->customer_taking_id) ? 'readonly' : '' }}>
                                    @if($errors->has('colyTaken'))
                                    <span class="help-block">{{ $errors->first('colyTaken') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                             <div class="form-group {{ $errors->has('takerName') ? 'has-error' : '' }}">
                                <label for="takerName" class="col-sm-4 control-label">{{ trans('operational/fields.taker-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="takerName" name="takerName" value="{{ count($errors) > 0 ? old('takerName') : $model->taker_name }}" {{ !empty($model->customer_taking_id) ? 'readonly' : '' }}>
                                    @if($errors->has('takerName'))
                                    <span class="help-block">{{ $errors->first('takerName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('takerAddress') ? 'has-error' : '' }}">
                                <label for="takerAddress" class="col-sm-4 control-label">{{ trans('operational/fields.taker-address') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="takerAddress" name="takerAddress" value="{{ count($errors) > 0 ? old('takerAddress') : $model->taker_address }}" {{ !empty($model->customer_taking_id) ? 'readonly' : '' }}>
                                    @if($errors->has('takerAddress'))
                                    <span class="help-block">{{ $errors->first('takerAddress') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('takerPhone') ? 'has-error' : '' }}">
                                <label for="takerPhone" class="col-sm-4 control-label">{{ trans('operational/fields.taker-phone') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="takerPhone" name="takerPhone" value="{{ count($errors) > 0 ? old('takerPhone') : $model->taker_phone }}" {{ !empty($model->customer_taking_id) ? 'readonly' : '' }}>
                                    @if($errors->has('takerPhone'))
                                    <span class="help-block">{{ $errors->first('takerPhone') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} </label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="5"  {{ !empty($model->customer_taking_id) ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('note') : $model->note }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(empty($model->customer_taking_id))
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
<div id="modal-customer-taking" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Letter of Expenditure</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchCustomerTaking" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchCustomerTaking" name="searchCustomerTaking">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-customer-taking" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.customer-taking-number') }}<hr/>
                                        {{ trans('operational/fields.resi-number') }}</th>
                                    <th>{{ trans('operational/fields.customer-name') }}<hr/>
                                        {{ trans('operational/fields.receiver-name') }}</th>
                                    <th>{{ trans('operational/fields.address') }}<hr/>
                                        {{ trans('operational/fields.phone') }}</th>
                                    <th>{{ trans('inventory/fields.item') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}<hr/>
                                        {{ trans('operational/fields.coly-wh') }}</th>
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

@endsection


@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $("#datatables-lov-resi").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-resi tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            $('#resiId').val(resi.resi_header_id);
            $('#resiNumber').val(resi.resi_number);
            
            $('#modal-lov-resi').removeClass("md-show");
        });

        $("#datatables-resi").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#modalCustomerTaking').on('click', showLovCustomerTaking);
        $('#searchCustomerTaking').on('keyup', loadLovCustomerTaking);
        $('#table-customer-taking tbody').on('click', 'tr', selectResi);



        $('#datatables-resi tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            $('#customerTakingId').val(resi.customer_taking_id);
            $('#customerTakingNumber').val(resi.customer_taking_number);
            $('#resiId').val(resi.resi_header_id);
            $('#resiNumber').val(resi.resi_number);
            $('#customerName').val(resi.customer_name);
            $('#receiverName').val(resi.receiver_name);
            $('#itemName').val(resi.item_name);
            $('#weight').val(resi.total_weight);
            $('#dimension').val(resi.total_volume);
            $('#totalColy').val(resi.total_coly);
            $('#colyWh').val(resi.total_receipt);
            $('#colyTaken').val(resi.total_receipt);

            $('#colyTaken').attr('max',resi.total_receipt);

            $('#totalColy').autoNumeric('update', {mDec: 0});
            $('#colyWh').autoNumeric('update', {mDec: 0});
            $('#weight').autoNumeric('update', {mDec: 2});
            $('#dimension').autoNumeric('update', {mDec: 6});

            $('#modal-customer-taking').modal('hide');
        });
    });

    var showLovCustomerTaking = function() {
        $('#searchCustomerTaking').val('');
        loadLovCustomerTaking(function() {
            $('#modal-customer-taking').modal('show');

        });
    };

    var xhrResi;
    var loadLovCustomerTaking = function(callback) {
        if(xhrResi && xhrResi.readyState != 4){
            xhrResi.abort();
        }
        xhrResi = $.ajax({
            url: '{{ URL($url.'/get-json-customer-taking') }}',
            data: {search: $('#searchCustomerTaking').val()},
            success: function(data) {
                $('#table-customer-taking tbody').html('');
                data.forEach(function(item) {
                    $('#table-customer-taking tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.customer_taking_number + '<hr/>' + item.resi_number + '</td>\
                            <td>' + item.customer_name + '<hr/>' + item.receiver_name + '</td>\
                            <td>' + item.receiver_address + '<hr/>' + item.receiver_phone + '</td>\
                            <td>' + item.item_name + '</td>\
                            <td class="text-right">' + parseInt(item.total_coly).formatMoney(0) + '<hr/>' + parseInt(item.total_receipt).formatMoney(0) + '</td>\
                            <td>' + item.description + '</td>\
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

        $('#customerTakingId').val(resi.customer_taking_id);
        $('#customerTakingNumber').val(resi.customer_taking_number);
        $('#resiId').val(resi.resi_header_id);
        $('#resiNumber').val(resi.resi_number);
        $('#customerName').val(resi.customer_name);
        $('#receiverName').val(resi.receiver_name);
        $('#itemName').val(resi.item_name);
        $('#weight').val(resi.total_weight);
        $('#dimension').val(resi.total_volume);
        $('#totalColy').val(resi.total_coly);
        $('#colyWh').val(resi.total_receipt);
        $('#colyTaken').val(resi.total_receipt);

        $('#colyTaken').attr('max',resi.total_receipt);

        $('#totalColy').autoNumeric('update', {mDec: 0});
        $('#colyWh').autoNumeric('update', {mDec: 0});
        $('#weight').autoNumeric('update', {mDec: 2});
        $('#dimension').autoNumeric('update', {mDec: 6});

        $('#modal-customer-taking').modal('hide');
    };
</script>
@endsection
