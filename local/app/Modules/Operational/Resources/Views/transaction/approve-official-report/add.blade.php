<?php 
    use App\Service\TimezoneDateConverter;
    use App\Service\Penomoran; 
?>
@extends('layouts.master')

@section('title', trans('operational/menu.approve-official-report'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.approve-official-report') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->official_report_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="officialReportNumber" class="col-sm-4 control-label">{{ trans('operational/fields.official-report-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="officialReportNumber" name="officialReportNumber"  value="{{ $model->official_report_number }}" readonly>
                                </div>
                            </div>
                            <?php
                                if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                                } else {
                                    $date = !empty($model->datetime) ? TimezoneDateConverter::getClientDateTime($model->datetime) :  TimezoneDateConverter::getClientDateTime();
                                }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
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
                                    <select class="form-control" id="hour" name="hour" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
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
                                    <select class="form-control" id="minute" name="minute" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
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
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="category" name="category"  value="{{ $model->category }}" readonly>
                                    @if($errors->has('category'))
                                        <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('personName') ? 'has-error' : '' }}">
                                <label for="personName" class="col-sm-4 control-label">{{ trans('operational/fields.person-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="personName" name="personName"  value="{{ count($errors) > 0 ? old('personName') : $model->person_name }}" readonly>
                                    @if($errors->has('personName'))
                                        <span class="help-block">{{ $errors->first('personName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" name="description" rows="7" id="description" readonly>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                        <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('respon') ? 'has-error' : '' }}">
                                <label for="respon" class="col-sm-4 control-label">{{ trans('shared/common.respon') }} </label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" name="respon" rows="7" id="respon" readonly>{{ count($errors) > 0 ? old('respon') : $model->respon }}</textarea>
                                    @if($errors->has('respon'))
                                        <span class="help-block">{{ $errors->first('respon') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('noteApproved') ? 'has-error' : '' }}">
                                <label for="noteApproved" class="col-sm-4 control-label">{{ trans('shared/common.approve-note') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" name="noteApproved" rows="7" id="noteApproved" >{{ count($errors) > 0 ? old('noteApproved') : $model->noteApproved }}</textarea>
                                    @if($errors->has('noteApproved'))
                                        <span class="help-block">{{ $errors->first('noteApproved') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(Gate::check('access', [$resource, 'approve']))
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
    $(document).on('ready', function(){
    });
</script>
@endsection
