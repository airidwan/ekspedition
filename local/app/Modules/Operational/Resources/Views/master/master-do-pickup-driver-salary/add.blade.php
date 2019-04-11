@extends('layouts.master')

@section('title', trans('operational/menu.do-pickup-driver-salary'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.do-pickup-driver-salary') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->do_pickup_driver_salary_id }}">
                        <input type="hidden" id="deliveryAreaId" name="deliveryAreaId" value="{{ count($errors) > 0 ? old('deliveryAreaId') : $model->route_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('deliveryArea') ? 'has-error' : '' }}">
                                <label for="deliveryArea" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <?php $stringArea = count($errors) > 0 ? old('deliveryArea') : $model->delivery_area_id; ?>
                                    <select class="form-control" id="deliveryArea" name="deliveryArea">
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionDeliveryArea as $deliveryArea)
                                        <option value="{{ $deliveryArea->delivery_area_id }}" {{ $deliveryArea->delivery_area_id == $stringArea ? 'selected' : '' }}>{{ $deliveryArea->delivery_area_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                                <label for="position" class="col-sm-4 control-label">{{ trans('operational/fields.position') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="position" name="position">
                                        <?php $stringPosition = count($errors) > 0 ? old('position') : $model->driver_position; ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionPosition as $position)
                                        <option value="{{ $position->lookup_code }}" {{ $position->lookup_code == $stringPosition ? 'selected' : '' }}>{{ $position->meaning }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('operational/fields.type') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = count($errors) > 0 ? old('type') : $model->vehicle_type; ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type->lookup_code }}" {{ $type->lookup_code == $stringType ? 'selected' : '' }}>{{ $type->meaning }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('salary') ? 'has-error' : '' }}">
                                <label for="salary" class="col-sm-4 control-label">{{ trans('operational/fields.salary') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="salary" name="salary" value="{{ count($errors) > 0 ? str_replace(',', '', old('salary')) : $model->salary }}">
                                    @if($errors->has('salary'))
                                    <span class="help-block">{{ $errors->first('salary') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group ">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="description" name="description" rows="4">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
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
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL('operational/master/master-do-pickup-driver-salary') }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}
                                </a>
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
@parent
<script type="text/javascript">
    $(document).on('ready', function() {
    });
</script>
@endsection
