@extends('layouts.master')

@section('title', trans('operational/menu.organization'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.organization') }}</h2>
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
                        <input type="hidden" name="id" value="{{ count($errors) > 0 ? old('id') : $model->org_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                <label for="code" class="col-sm-4 control-label">{{ trans('shared/common.code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code" value="{{ count($errors) > 0 ? old('code') : $model->org_code }}" {{ !Gate::check('access', [$resource, 'update']) ? 'disabled' : '' }}>
                                    @if($errors->has('code'))
                                    <span class="help-block">{{ $errors->first('code') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label for="name" class="col-sm-4 control-label">{{ trans('shared/common.name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="name" name="name" value="{{ count($errors) > 0 ? old('name') : $model->org_name }}" {{ !Gate::check('access', [$resource, 'update']) ? 'disabled' : '' }}>
                                    @if($errors->has('name'))
                                    <span class="help-block">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="address" name="address" rows="4" {{ !Gate::check('access', [$resource, 'update']) ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('address') : $model->address }}</textarea>
                                    @if($errors->has('address'))
                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                <label for="phone" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{ count($errors) > 0 ? old('phone') : $model->phone_number }}" {{ !Gate::check('access', [$resource, 'update']) ? 'disabled' : '' }}>
                                    @if($errors->has('phone'))
                                    <span class="help-block">{{ $errors->first('phone') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('director') ? 'has-error' : '' }}">
                                <label for="director" class="col-sm-4 control-label">{{ trans('operational/fields.director') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="director" name="director" value="{{ count($errors) > 0 ? old('director') : $model->director_name }}" {{ !Gate::check('access', [$resource, 'update']) ? 'disabled' : '' }}>
                                    @if($errors->has('director'))
                                    <span class="help-block">{{ $errors->first('director') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">
                                <label for="kolomString" class="col-sm-4 control-label">{{ trans('shared/common.city') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="city" name="city" {{ !Gate::check('access', [$resource, 'update']) ? 'disabled' : '' }}>
                                        <?php $CityId = count($errors) > 0 ? old('city') : $model->city_id ?>
                                        @foreach($optionCity as $city)
                                        <option value="{{ $city->city_id }}" {{ $city->city_id == $CityId ? 'selected' : '' }}>{{ $city->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('city'))
                                    <span class="help-block">{{ $errors->first('city') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('companyCode') ? 'has-error' : '' }}">
                                <label for="kolomString" class="col-sm-4 control-label">{{ trans('operational/fields.company-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="companyCode" name="companyCode" value="{{ count($errors) > 0 ? old('companyCode') : $model->company }}" disabled>
                                    @if($errors->has('companyCode'))
                                    <span class="help-block">{{ $errors->first('companyCode') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                @can('access', [$resource, 'update'])
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endcan
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </form>
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
    $('#city').select2();
});
</script>
@endsection
