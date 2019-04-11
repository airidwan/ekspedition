@extends('layouts.master')

@section('title', trans('marketing/menu.complain'))

<?php 
use App\Service\TimezoneDateConverter;
use App\Service\Penomoran; 
?>

@section('header')
@parent
<style type="text/css">
    #table-lov-resi tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-users"></i> <strong>{{ $title }}</strong> {{ trans('marketing/menu.complain') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->complain_id }}">
                        <div class="col-sm-8 portlets">
                            <div class="form-group {{ $errors->has('complainNumber') ? 'has-error' : '' }}">
                                <label for="complainNumber" class="col-sm-4 control-label">{{ trans('marketing/fields.complain-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="complainNumber" name="complainNumber" value="{{ count($errors) > 0 ? old('complainNumber') : $model->complain_number }}" readonly>
                                    @if($errors->has('complainNumber'))
                                    <span class="help-block">{{ $errors->first('complainNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                            } else {
                                $date = !empty($model->complain_time) ? TimezoneDateConverter::getClientDateTime($model->complain_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" {{ !empty($model->complain_id) ? 'disabled' : '' }}>
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
                                    <select class="form-control" id="hours" name="hours" {{ !empty($model->complain_id) ? 'disabled' : '' }}>
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
                                    <select class="form-control" id="minute" name="minute" {{ !empty($model->complain_id) ? 'disabled' : '' }}>
                                        <?php $minuteFormat = $date !== null ? $date->format('i') : -1 ; ?>
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
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label for="name" class="col-sm-4 control-label">{{ trans('marketing/fields.callers-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="name" name="name" value="{{ count($errors) > 0 ? old('name') : $model->name }}" {{ !empty($model->complain_id) ? 'readonly' : '' }}>
                                    @if($errors->has('name'))
                                    <span class="help-block">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('callersPhone') ? 'has-error' : '' }}">
                                <label for="callersPhone" class="col-sm-4 control-label">{{ trans('marketing/fields.callers-phone') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="callersPhone" name="callersPhone" value="{{ count($errors) > 0 ? old('callersPhone') : $model->callers_phone }}" {{ !empty($model->complain_id) ? 'readonly' : '' }}>
                                    @if($errors->has('callersPhone'))
                                    <span class="help-block">{{ $errors->first('callersPhone') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            $modelResi = $model->resi; 
                            $resiId = !empty($modelResi) ? $modelResi->resi_header_id : '' ; 
                            $resiNumber = !empty($modelResi) ? $modelResi->resi_number : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('resiNumber') ? 'has-error' : '' }}">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="resiId" name="resiId" value="{{ count($errors) > 0 ? old('resiId') : $resiId }}">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                    <span class="btn input-group-addon" id="{{ !empty($model->complain_id) ? '' : 'modalResi' }}" ><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('resiNumber'))
                                    <span class="help-block">{{ $errors->first('resiNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="routeCode" class="col-sm-4 control-label">{{ trans('operational/fields.route') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="routeCode" name="routeCode" value="{{ $modelResi !== null ? $modelResi->route->route_code : '' }}" readonly>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('comment') ? 'has-error' : '' }}">
                                <label for="comment" class="col-sm-4 control-label">{{ trans('marketing/fields.comment') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="comment" name="comment" rows="3"  maxlength="255" {{ !empty($model->complain_id) ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('comment') : $model->comment }}</textarea>
                                    @if($errors->has('comment'))
                                    <span class="help-block">{{ $errors->first('comment') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('tempRespon') ? 'has-error' : '' }}">
                                <label for="tempRespon" class="col-sm-4 control-label">{{ trans('marketing/fields.temp-respon') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="tempRespon" name="tempRespon" rows="3"  maxlength="255" {{ !empty($model->complain_id) ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('tempRespon') : $model->temporary_respon }}</textarea>
                                    @if($errors->has('tempRespon'))
                                    <span class="help-block">{{ $errors->first('tempRespon') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('lastRespon') ? 'has-error' : '' }}">
                                <label for="lastRespon" class="col-sm-4 control-label">{{ trans('marketing/fields.last-respon') }} </label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="lastRespon" name="lastRespon" rows="3"  maxlength="255" {{ $model->isClosed() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('lastRespon') : $model->last_respon }}</textarea>
                                    @if($errors->has('lastRespon'))
                                    <span class="help-block">{{ $errors->first('lastRespon') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status" {{ empty($model->complain_id) || ($model->isOpen() && $model->created_by == \Auth::user()->id) ? '' : 'disabled'  }}>
                                        <?php $statusString = count($errors) > 0 ? old('status') : $model->status; var_dump($statusString) ?>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status }}" {{ $statusString == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if($model->isOpen())
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
<div id="modal-lov-resi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.resi') }}</h4>
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
                        <table id="table-lov-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                    <th>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('operational/fields.customer') }}</th>
                                    <th>{{ trans('operational/fields.sender-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('operational/fields.receiver-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}</th>
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
        $('#modalResi').on('click', showLovResi);
        $('#searchResi').on('keyup', loadLovResi);
        $('#table-lov-resi tbody').on('click', 'tr', selectResi);


    });
    var showLovResi = function() {
        $('#searchResi').val('');
        loadLovResi(function() {
            $('#modal-lov-resi').modal('show');
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
                $('#table-lov-resi tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-resi tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.resi_number + '</td>\
                            <td>' + item.route_code + '</td>\
                            <td>' + item.customer + '</td>\
                            <td>' + item.sender_name + '</td>\
                            <td>' + item.sender_address + '</td>\
                            <td>' + item.receiver_name + '</td>\
                            <td>' + item.receiver_address + '</td>\
                            <td>' + item.item_name + '</td>\
                            <td>' + item.total_coly + '</td>\
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
        $('#routeCode').val(data.route_code);

        $('#modal-lov-resi').modal('hide');
    };
</script>
@endsection
