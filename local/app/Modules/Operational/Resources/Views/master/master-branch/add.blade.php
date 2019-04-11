@extends('layouts.master')

@section('title', trans('operational/menu.branch'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.branch') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->branch_id }}">
                        <div class="col-sm-5 portlets">
                            <input type="hidden" name="codeNumber" value="{{ count($errors) > 0 ? old('viewCodeNumber') : $model->branch_code_numeric }}">
                            <div class="form-group {{ $errors->has('viewCodeNumber') ? 'has-error' : '' }}">
                                <label for="viewCodeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.id-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="viewCodeNumber" name="viewCodeNumber" value="{{ count($errors) > 0 ? old('viewCodeNumber') : $model->branch_code_numeric }}" disabled>
                                    @if($errors->has('viewCodeNumber'))
                                    <span class="help-block">{{ $errors->first('viewCodeNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                <label for="code" class="col-sm-4 control-label">{{ trans('operational/fields.code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code"  value="{{ count($errors) > 0 ? old('code') : $model->branch_code }}">
                                    @if($errors->has('code'))
                                    <span class="help-block">{{ $errors->first('code') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label for="name" class="col-sm-4 control-label">{{ trans('shared/common.name') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="name" name="name"  value="{{ count($errors) > 0 ? old('name') : $model->branch_name }}">
                                    @if($errors->has('name'))
                                    <span class="help-block">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="address" name="address" rows="4">{{ count($errors) > 0 ? old('address') : $model->address }}</textarea>
                                    @if($errors->has('address'))
                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                <label for="phone" class="col-sm-5 control-label">{{ trans('shared/common.phone') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="phone" name="phone"  value="{{ count($errors) > 0 ? old('phone') : $model->phone_number }}">
                                    @if($errors->has('phone'))
                                    <span class="help-block">{{ $errors->first('phone') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('branchManager') ? 'has-error' : '' }}">
                                <label for="branchManager" class="col-sm-5 control-label">{{ trans('operational/fields.branch-manager') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="branchManager" name="branchManager" value="{{ count($errors) > 0 ? old('branchManager') : $model->branch_manager }}" />
                                    @if($errors->has('branchManager'))
                                    <span class="help-block">{{ $errors->first('branchManager') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">
                                <label for="city" class="col-sm-5 control-label">{{ trans('shared/common.city') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
                                    <select class="form-control" id="city" name="city">
                                        <?php $cityId = count($errors) > 0 ? old('city') : $model->city_id; ?>
                                        <option value="">{{ trans('shared/common.select-city') }}</option>
                                        @foreach($optionCity as $city)
                                        <option value="{{ $city->city_id }}" {{ $city->city_id == $cityId ? 'selected' : '' }}>{{ $city->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('city'))
                                    <span class="help-block">{{ $errors->first('city') }}</span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" value="{{ count($errors) > 0 ? old('viewCostCenter') : $model->cost_center_code }}" name="costCenterCode">
                            <div class="form-group {{ $errors->has('viewCostCenter') ? 'has-error' : '' }}">
                                <label for="viewCostCenter" class="col-sm-5 control-label">{{ trans('operational/fields.cost-center') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="viewCostCenter" name="viewCostCenter" value="{{ count($errors) > 0 ? old('viewCostCenter') : $model->cost_center_code }}" readonly>
                                    @if($errors->has('viewCostCenter'))
                                    <span class="help-block">{{ $errors->first('viewCostCenter') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('mainBranch') ? 'has-error' : '' }}">
                                <label class="col-sm-5 control-label">{{ trans('operational/fields.main-branch') }}</label>
                                <div class="col-sm-7">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $mainBranch = count($errors) > 0 ? old('mainBranch') : $model->main_branch; ?>
                                        <input type="checkbox" id="mainBranch" name="mainBranch" value="1" {{ $mainBranch ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-5 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-7">
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
        $('#city').select2();
    });
</script>
@endsection
