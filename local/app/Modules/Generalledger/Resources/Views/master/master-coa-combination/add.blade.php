@extends('layouts.master')

@section('title', trans('general-ledger/menu.master-coa-combination'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.master-coa-combination') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->account_combination_id }}">
                        <div class="col-sm-8 portlets">
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                                <label for="company" class="col-sm-4 control-label">{{ trans('general-ledger/fields.company') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="company" id="company">
                                        <?php $company = !empty($model->segment_1) ? $model->segment_1 : '' ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionCompany as $data)
                                        <option value="{{ $data->coa_code }}:{{ $data->coa_id }}" {{ $company == $data->coa_id ? 'selected' : '' }}>{{ $data->coa_code .' - '.$data->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('company'))
                                    <span class="help-block">{{ $errors->first('company') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('costCenter') ? 'has-error' : '' }}">
                                <label for="costCenter" class="col-sm-4 control-label">{{ trans('general-ledger/fields.cost-center') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="costCenter" id="costCenter">
                                        <?php $costCenter = !empty($model->segment_2) ? $model->segment_2 : '' ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionCostCenter as $data)
                                        <option value="{{ $data->coa_code }}:{{ $data->coa_id }}" {{ $costCenter == $data->coa_id ? 'selected' : '' }}>{{ $data->coa_code .' - '.$data->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('costCenter'))
                                    <span class="help-block">{{ $errors->first('costCenter') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('account') ? 'has-error' : '' }}">
                                <label for="account" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="account" id="account">
                                        <?php $account = !empty($model->segment_3) ? $model->segment_3 : '' ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionAccount as $data)
                                        <option value="{{ $data->coa_code }}:{{ $data->coa_id }}" {{ $account == $data->coa_id ? 'selected' : '' }}>{{ $data->coa_code .' - '.$data->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('account'))
                                    <span class="help-block">{{ $errors->first('account') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('subAccount') ? 'has-error' : '' }}">
                                <label for="subAccount" class="col-sm-4 control-label">{{ trans('general-ledger/fields.sub-account') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="subAccount" id="subAccount">
                                        <?php $subAccount = !empty($model->segment_4) ? $model->segment_4 : '' ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionSubAccount as $data)
                                        <option value="{{ $data->coa_code }}:{{ $data->coa_id }}" {{ $subAccount == $data->coa_id ? 'selected' : '' }}>{{ $data->coa_code.' - '.$data->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('subAccount'))
                                    <span class="help-block">{{ $errors->first('subAccount') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('future') ? 'has-error' : '' }}">
                                <label for="future" class="col-sm-4 control-label">{{ trans('general-ledger/fields.future') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="future" id="future">
                                        <?php $future = !empty($model->segment_5) ? $model->segment_5 : '' ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionFuture as $data)
                                        <option value="{{ $data->coa_code }}:{{ $data->coa_id }}" {{ $future == $data->coa_id ? 'selected' : '' }}>{{ $data->coa_code.' - '.$data->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('future'))
                                    <span class="help-block">{{ $errors->first('future') }}</span>
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
        $('#company').select2();
        $('#costCenter').select2();
        $('#account').select2();
        $('#subAccount').select2();
        $('#future').select2();
    });
</script>
@endsection
