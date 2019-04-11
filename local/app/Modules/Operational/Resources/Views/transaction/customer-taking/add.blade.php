@extends('layouts.master')

@section('title', trans('operational/menu.customer-taking'))

<?php 
use App\Service\Penomoran; 
use App\Service\TimezoneDateConverter;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-users"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.customer-taking') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->customer_taking_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('customerTakingNumber') ? 'has-error' : '' }}">
                                <label for="customerTakingNumber" class="col-sm-4 control-label">{{ trans('operational/fields.customer-taking-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerTakingNumber" name="customerTakingNumber" value="{{ count($errors) > 0 ? old('customerTakingNumber') : $model->customer_taking_number }}" readonly>
                                    @if($errors->has('customerTakingNumber'))
                                    <span class="help-block">{{ $errors->first('customerTakingNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                            } else {
                                $date = !empty($model->customer_taking_time) ? TimezoneDateConverter::getClientDateTime($model->customer_taking_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" {{ $model->haveTransact() ? 'disabled' : '' }}>
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
                                    <select class="form-control" id="hour" name="hour" {{ $model->haveTransact() ? 'disabled' : '' }}>
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
                                    <select class="form-control" id="minute" name="minute" {{ $model->haveTransact() ? 'disabled' : '' }}>
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
                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="5" {{ $model->haveTransact() ? 'readonly' : '' }} >{{ count($errors) > 0 ? old('note') : $model->note }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                            $modelResi    = $model->resi; 
                            $resiId       = !empty($modelResi) ? $modelResi->resi_header_id : '' ; 
                            $resiNumber   = !empty($modelResi) ? $modelResi->resi_number : '' ; 
                            $customerName = !empty($modelResi) ? $modelResi->getCustomerName() : '' ; 
                            $receiverName = !empty($modelResi) ? $modelResi->receiver_name : '' ; 
                            $address      = !empty($modelResi) ? $modelResi->receiver_address : '' ; 
                            $phoneNumber  = !empty($modelResi) ? $modelResi->receiver_phone : '' ; 
                            $itemName     = !empty($modelResi) ? $modelResi->item_name : '' ; 
                            $weight       = !empty($modelResi) ? $modelResi->totalWeightAll() : '' ; 
                            $dimension    = !empty($modelResi) ? $modelResi->totalVolumeAll() : '' ; 
                            $totalColy    = !empty($modelResi) ? $modelResi->totalColy() : '' ; 
                            $colyWh       = !empty($modelResi) ? $modelResi->totalReceipt() : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('resiId') ? 'has-error' : '' }}">
                                <label for="resiId" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="resiId" name="resiId" value="{{ count($errors) > 0 ? old('resiId') : $resiId }}">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                    <span class="btn input-group-addon" id="{{ $model->haveTransact() || !empty($model->customer_taking_id) ? '' : 'modalResi' }}" ><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('resiId'))
                                    <span class="help-block">{{ $errors->first('resiId') }}</span>
                                    @endif
                                </div>
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
                            <div class="form-group {{ $errors->has('weight') ? 'has-error' : '' }}">
                                <label for="weight" class="col-sm-4 control-label">{{ trans('operational/fields.weight') }} (kg)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="currency form-control" id="weight" name="weight" value="{{ count($errors) > 0 ? old('weight') : $weight }}" readonly>
                                </div>
                                    @if($errors->has('weight'))
                                    <span class="help-block">{{ $errors->first('weight') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('dimension') ? 'has-error' : '' }}">
                                <label for="dimension" class="col-sm-4 control-label">{{ trans('operational/fields.dimension') }} (m<sup>3</sup>)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="currency form-control" id="dimension" name="dimension" value="{{ count($errors) > 0 ? old('dimension') : $dimension }}" readonly>
                                </div>
                                    @if($errors->has('dimension'))
                                    <span class="help-block">{{ $errors->first('dimension') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('totalColy') ? 'has-error' : '' }}">
                                <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="currency form-control" id="totalColy" name="totalColy" value="{{ count($errors) > 0 ? old('totalColy') : $totalColy }}" readonly>
                                </div>
                                    @if($errors->has('totalColy'))
                                    <span class="help-block">{{ $errors->first('totalColy') }}</span>
                                    @endif
                            </div>
                            <div class="form-group {{ $errors->has('colyWh') ? 'has-error' : '' }}">
                                <label for="colyWh" class="col-sm-4 control-label">{{ trans('operational/fields.coly-wh') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="currency form-control" id="colyWh" name="colyWh" value="{{ count($errors) > 0 ? old('colyWh') : $colyWh }}" readonly>
                                </div>
                                    @if($errors->has('colyWh'))
                                    <span class="help-block">{{ $errors->first('colyWh') }}</span>
                                    @endif
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(!empty($model->customer_taking_id))
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->customer_taking_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(!$model->haveTransact())
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
                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                    <th>{{ trans('operational/fields.customer-name') }}<hr/>{{ trans('operational/fields.receiver-name') }}</th>
                                    <th>{{ trans('shared/common.address') }} <hr/> {{ trans('shared/common.phone') }}</th>
                                    <th>{{ trans('inventory/fields.item') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}<hr/>{{ trans('operational/fields.coly-wh') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('accountreceivables/fields.remaining') }}<hr/>{{ trans('shared/common.status') }}</th>
                                    <th>{{ trans('accountreceivables/fields.bill') }}</th>
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

@if(Session::has('successMessage'))
<div id="modal-success-message" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <h2 class="text-center">{{ Session::get('successMessage') }}</h2>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="print-from-modal" href="#" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection


@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        @if(Session::has('successMessage'))
            $('#modal-success-message').modal('show');
            $('#print-from-modal').on('click', function(event) {
                event.preventDefault();
                window.open('{{ URL($url . '/print-pdf-detail/' . $model->customer_taking_id) }}', '_blank');
                window.location = '{{ URL($url) }}';
            });
        @endif

        $("#datatables-lov-resi").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#modalResi').on('click', showLovResi);
        $('#searchResi').on('keyup', loadLovResi);
        $('#table-resi tbody').on('click', 'tr', selectResi);

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

        $('#datatables-resi tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            $('#resiId').val(resi.resi_header_id);
            $('#resiNumber').val(resi.resi_number);
            $('#customerName').val(resi.customer_name);
            $('#receiverName').val(resi.receiver_name);
            $('#address').val(resi.receiver_address);
            $('#phoneNumber').val(resi.receiver_phone);
            $('#itemName').val(resi.item_name);
            $('#weight').val(resi.total_weight);
            $('#dimension').val(resi.total_volume);
            $('#totalColy').val(resi.total_coly);
            $('#colyWh').val(resi.total_receipt);

            $('#totalColy').autoNumeric('update', {mDec: 0});
            $('#colyWh').autoNumeric('update', {mDec: 0});
            $('#weight').autoNumeric('update', {mDec: 2});
            $('#dimension').autoNumeric('update', {mDec: 6});

            $('#modal-resi').modal('hide');
        });
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
                    $('#table-resi tbody').append(
                        '<tr data-json=\'' + JSON.stringify(resi) + '\'>\
                            <td>' + resi.resi_number + '</td>\
                            <td>' + resi.customer_name + '<hr/>' + resi.receiver_name + '</td>\
                            <td>' + resi.receiver_address + '<hr/>' + resi.receiver_phone + '</td>\
                            <td>' + resi.item_name + '</td>\
                            <td class="text-right">' + parseInt(resi.total_coly).formatMoney(0) + '<hr/>' + parseInt(resi.total_receipt).formatMoney(0) + '</td>\
                            <td>' + resi.description + '</td>\
                            <td class="text-right">' + parseInt(resi.total_remaining_invoice).formatMoney(0) + '<hr/>' + resi.status + '</td>\
                            <td>' + resi.is_tagihan + '</td>\
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
        var data = $(this).data('json');

        $('#resiId').val(data.resi_header_id);
        $('#resiNumber').val(data.resi_number);
        $('#customerName').val(data.customer_name);
        $('#receiverName').val(data.receiver_name);
        $('#address').val(data.receiver_address);
        $('#phoneNumber').val(data.receiver_phone);
        $('#itemName').val(data.item_name);
        $('#weight').val(parseInt(data.total_weight).formatMoney(2));
        $('#dimension').val(parseInt(data.total_volume).formatMoney(5));
        $('#totalColy').val(parseInt(data.total_coly).formatMoney(0));
        $('#colyWh').val(parseInt(data.total_receipt).formatMoney(0));

        $('#modal-resi').modal("hide");
    };
</script>
@endsection
