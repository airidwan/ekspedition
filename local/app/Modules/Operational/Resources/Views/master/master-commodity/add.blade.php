@extends('layouts.master')

@section('title', trans('operational/menu.commodity'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.commodity') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->commodity_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('commodity') ? 'has-error' : '' }}">
                                <label for="commodity" class="col-sm-4 control-label">{{ trans('operational/fields.commodity') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="commodity" name="commodity"  value="{{ count($errors) > 0 ? old('commodity') : $model->commodity_name }}">
                                    @if($errors->has('commodity'))
                                    <span class="help-block">{{ $errors->first('commodity') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('show') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('operational/fields.show-on-manifest') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $show = count($errors) > 0 ? old('show') : $model->show; ?>
                                        <input type="checkbox" id="show" name="show" value="Y" {{ $show == 'Y' ? 'checked' : '' }}> {{ trans('operational/fields.show') }}
                                    </label>
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
    });
</script>
@endsection
