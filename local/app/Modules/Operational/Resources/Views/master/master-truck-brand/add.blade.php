@extends('layouts.master')

@section('title', trans('operational/menu.truck-brand'))

@section('header')
@parent
<style type="text/css">
    ::-webkit-input-placeholder {
        text-align: center;
    }
</style>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong> {{ $title }} </strong> {{ trans('operational/menu.truck-brand') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                <label for="code" class="col-sm-4 control-label">{{ trans('shared/common.code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code"  value="{{ !empty($model->lookup_code) ? $model->lookup_code : '' }}" disabled>
                                    @if($errors->has('code'))
                                    <span class="help-block">{{ $errors->first('code') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('brandName') ? 'has-error' : '' }}">
                                <label for="brandName" class="col-sm-4 control-label">{{ trans('operational/fields.brand-name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="brandName" name="brandName" value="{{ count($errors) > 0 ? old('brandName') : $model->meaning }}">
                                    @if($errors->has('brandName'))
                                    <span class="help-block">{{ $errors->first('brandName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $model->description }}">
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                        <input type="checkbox" id="status" name="status" value="Y"{{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL('operational/master/master-truck-brand') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                            </div>
                        </div>
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
        
    });
</script>
@endsection
