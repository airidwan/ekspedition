@extends('layouts.master')

@section('title', trans('marketing/menu.pickup-request'))

<?php
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-users"></i> <strong>{{ $title }}</strong> {{ trans('marketing/menu.pickup-request') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save-approve') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->pickup_request_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('pickupRequestNumber') ? 'has-error' : '' }}">
                                <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('marketing/fields.pickup-request-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" value="{{ count($errors) > 0 ? old('pickupRequestNumber') : $model->pickup_request_number }}" readonly>
                                    @if($errors->has('pickupRequestNumber'))
                                    <span class="help-block">{{ $errors->first('pickupRequestNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                            } else {
                                $date = !empty($model->pickup_request_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_request_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" disabled>
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
                                $date = !empty($model->pickup_request_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_request_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                                <label for="time" class="col-sm-4 control-label">{{ trans('shared/common.time') }}</label>
                                <div class="col-sm-3" style="padding:0px 0px 0px 15px;" disabled>
                                    <select class="form-control" id="hours" name="hours" disabled>
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
                                    <select class="form-control" id="minute" name="minute" disabled>
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
                                    <select class="form-control" id="status" name="status" readonly>
                                        <?php $statusString = count($errors) > 0 ? old('status') : $model->status ?>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status }}" {{ $statusString == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('callersName') ? 'has-error' : '' }}">
                                <label for="callersName" class="col-sm-4 control-label">{{ trans('marketing/fields.callers-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="callersName" name="callersName" value="{{ count($errors) > 0 ? old('callersName') : $model->callers_name }}" readonly>
                                    @if($errors->has('callersName'))
                                    <span class="help-block">{{ $errors->first('callersName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('customerName') ? 'has-error' : '' }}">
                                <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="customerId" name="customerId" value="{{ count($errors) > 0 ? old('customerId') : $model->customer_id }}">
                                    <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $model->customer_name }}" readonly>
                                    <span class="btn input-group-addon {{ $model->isOpen() ? 'md-trigger' : '' }}" data-modal="modal-customer"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('customerName'))
                                    <span class="help-block">{{ $errors->first('customerName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $model->address }}" readonly>
                                    @if($errors->has('address'))
                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('phoneNumber') ? 'has-error' : '' }}">
                                <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="{{ count($errors) > 0 ? old('phoneNumber') : $model->phone_number }}" readonly>
                                    @if($errors->has('phoneNumber'))
                                    <span class="help-block">{{ $errors->first('phoneNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('pickupCost') ? 'has-error' : '' }}">
                                <label for="pickupCost" class="col-sm-4 control-label">{{ trans('marketing/fields.pickup-cost') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="pickupCost" name="pickupCost" value="{{ count($errors) > 0 ? str_replace(',', '', old('pickupCost')) : $model->pickup_cost }}" >
                                    @if($errors->has('pickupCost'))
                                    <span class="help-block">{{ $errors->first('pickupCost') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('noteAdd') ? 'has-error' : '' }}">
                                <label for="noteAdd" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="noteAdd" name="noteAdd" rows="3"  maxlength="255" >{{ count($errors) > 0 ? old('noteAdd') : $model->note_add }}</textarea>
                                    @if($errors->has('noteAdd'))
                                    <span class="help-block">{{ $errors->first('noteAdd') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('itemName') ? 'has-error' : '' }}">
                                <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemName" name="itemName" value="{{ count($errors) > 0 ? old('itemName') : $model->item_name }}" readonly>
                                    @if($errors->has('itemName'))
                                    <span class="help-block">{{ $errors->first('itemName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('totalColy') ? 'has-error' : '' }}">
                                <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="totalColy" name="totalColy" value="{{ count($errors) > 0 ? old('totalColy') : $model->total_coly }}" readonly>
                                    @if($errors->has('totalColy'))
                                    <span class="help-block">{{ $errors->first('totalColy') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('weight') ? 'has-error' : '' }}">
                                <label for="weight" class="col-sm-4 control-label">{{ trans('operational/fields.weight') }} (Kg)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control decimal" id="weight" name="weight" value="{{ count($errors) > 0 ? old('weight') : $model->weight }}" readonly>
                                    @if($errors->has('weight'))
                                    <span class="help-block">{{ $errors->first('weight') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="volume" class="col-sm-4 control-label">{{ trans('operational/fields.dimension') }} (Cm)</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control decimal text-right" id="dimensionL" placeholder="L" name="dimensionL" value="{{ count($errors) > 0 ? old('dimensionL') : $model->dimension_long }}" readonly>
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control decimal text-right" id="dimensionW" placeholder="W" name="dimensionW" value="{{ count($errors) > 0 ? old('dimensionW') : $model->dimension_width }}" readonly>
                                </div>    
                                <div class="col-sm-2">
                                    <input type="text" class="form-control decimal text-right" id="dimensionH" placeholder="H" name="dimensionH" value="{{ count($errors) > 0 ? old('dimensionH') : $model->dimension_height }}" readonly>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('dimension') ? 'has-error' : '' }}">
                                <label for="dimension" class="col-sm-4 control-label">{{ trans('operational/fields.volume') }} (M<sup>3</sup>)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control decimal text-right" id="dimension" name="dimension" value="{{ count($errors) > 0 ? old('dimension') : $model->dimension }}" readonly>
                                    @if($errors->has('dimension'))
                                    <span class="help-block">{{ $errors->first('dimension') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="3"  maxlength="255" readonly>{{ count($errors) > 0 ? old('note') : $model->note }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if($model->isOpen() ? 'disabled' : '')
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if($model->isOpen() ? 'disabled' : '')
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info"><i class="fa fa-save"></i> {{ trans('shared/common.approve') }}</button>
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
<div class="md-modal-lg md-fade-in-scale-up" id="modal-customer">
    <div class="md-content">
        <div class="md-close-btn"><a class="md-close"><i class="fa fa-times"></i></a></div>
        <h3><strong>{{ trans('operational/fields.customer') }}</strong></h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <table id="datatables-customer" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
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
                            @foreach ($optionCustomer as $customer)
                            <tr style="cursor: pointer;" data-customer="{{ json_encode($customer) }}">
                                <td>{{ $customer->customer_code }}</td>
                                <td>{{ $customer->customer_name }}</td>
                                <td>{{ $customer->address }}</td>
                                <td>{{ $customer->phone_number }}</td>
                                <td>{{ $customer->description }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#dimensionL').on('keyup', calculateVolume);
        $('#dimensionW').on('keyup', calculateVolume);
        $('#dimensionH').on('keyup', calculateVolume);
        $("#datatables-customer").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-customer tbody').on('click', 'tr', function () {
            var customer = $(this).data('customer');

            $('#customerName').val(customer.customer_name);
            $('#customerId').val(customer.customer_id);
            $('#address').val(customer.address);
            $('#phoneNumber').val(customer.phone_number);
            
            $('#modal-customer').removeClass("md-show");
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
</script>
@endsection
