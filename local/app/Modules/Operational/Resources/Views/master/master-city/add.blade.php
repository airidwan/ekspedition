@extends('layouts.master')

@section('title', trans('operational/menu.city'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.city') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->city_id }}">
                        <div class="col-sm-5 portlets">
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                <label for="code" class="col-sm-4 control-label">{{ trans('operational/fields.city-code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code" value="{{ count($errors) > 0 ? old('code') : $model->city_code }}">
                                    @if($errors->has('code'))
                                    <span class="help-block">{{ $errors->first('code') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('cityName') ? 'has-error' : '' }}">
                                <label for="cityName" class="col-sm-4 control-label">{{ trans('operational/fields.city-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="cityName" name="cityName"  value="{{ count($errors) > 0 ? old('cityName') : $model->city_name }}">
                                    @if($errors->has('cityName'))
                                    <span class="help-block">{{ $errors->first('cityName') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 portlets">
                            <div class="form-group {{ $errors->has('province') ? 'has-error' : '' }}">
                                <label for="province" class="col-sm-4 control-label">{{ trans('operational/fields.province') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="province" name="province">
                                        <?php $provinceName = count($errors) > 0 ? old('province') : $model->province; ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionProvince as $province)
                                        {{ $model->province }}
                                        <option value="{{ $province->province_name }}" {{ $province->province_name == $provinceName ? 'selected' : '' }}>{{ $province->province_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('province'))
                                    <span class="help-block">{{ $errors->first('province') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
        $('#province').select2();
    });
</script>
@endsection
