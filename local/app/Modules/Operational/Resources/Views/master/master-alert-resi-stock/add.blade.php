@extends('layouts.master')

@section('title', trans('operational/menu.alert-resi-stock'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.alert-resi-stock') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $modelCity->city_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="code" class="col-sm-4 control-label">{{ trans('operational/fields.city-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code" value="{{ $modelCity->city_code }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cityName" class="col-sm-4 control-label">{{ trans('operational/fields.city-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="cityName" name="cityName"  value="{{ $modelCity->city_name }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="province" class="col-sm-4 control-label">{{ trans('operational/fields.province') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="province" name="province" value="{{ $modelCity->province }}" disabled>
                                </div>
                            </div>
                            <?php $minimumDays = $model !== null ? $model->minimum_days : 0; ?>
                            <div class="form-group">
                                <label for="minimumDays" class="col-sm-4 control-label">{{ trans('operational/fields.maximum-days') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="minimumDays" name="minimumDays" value="{{ count($errors) > 0 ? old('minimumDays') : $minimumDays }}">
                                </div>
                            </div>
                            <?php $description = $model !== null ? $model->description : ''; ?>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('operational/fields.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="description" name="description" >{{ count($errors) > 0 ? old('description') : $description }}</textarea>
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
