@extends('layouts.master')

@section('title', trans('general-ledger/menu.master-coa'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.master-coa') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->coa_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('segmentName') ? 'has-error' : '' }}">
                                <label for="segmentName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.segment-name') }}</label>
                                <div class="col-sm-8">
                                    <?php $segmentName = count($errors) > 0 ? old('segmentName') : $model->segment_name; ?>
                                    <select class="form-control" id="segmentName" name="segmentName">
                                        <option value="Account" {{ $segmentName == 'Account' ? 'selected' : '' }}>Account</option>
                                        <option value="Future 1" {{ $segmentName == 'Future 1' ? 'selected' : '' }}>Future 1</option>
                                    </select>
                                    @if($errors->has('segmentName'))
                                    <span class="help-block">{{ $errors->first('segmentName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('coaCode') ? 'has-error' : '' }}">
                                <label for="coaCode" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa-code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="coaCode" name="coaCode" value="{{ count($errors) > 0 ? old('coaCode') : $model->coa_code }}">
                                    @if($errors->has('coaCode'))
                                    <span class="help-block">{{ $errors->first('coaCode') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.deskripsi') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $model->description }}">
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('identifier') ? 'has-error' : '' }}">
                                <label for="identifier" class="col-sm-4 control-label">{{ trans('general-ledger/fields.identifier') }}</label>
                                <div class="col-sm-8">
                                    <?php $identifier = count($errors) > 0 ? old('identifier') : $model->identifier; ?>
                                    <select class="form-control" id="identifier" name="identifier">
                                        <option value="1" {{ $identifier == '1' ? 'selected' : '' }}>Asset</option>
                                        <option value="2" {{ $identifier == '2' ? 'selected' : '' }}>Liability</option>
                                        <option value="3" {{ $identifier == '3' ? 'selected' : '' }}>Ekuitas</option>
                                        <option value="4" {{ $identifier == '4' ? 'selected' : '' }}>Revenue</option>
                                        <option value="5" {{ $identifier == '5' ? 'selected' : '' }}>Ekspense</option>
                                    </select>
                                    @if($errors->has('identifier'))
                                    <span class="help-block">{{ $errors->first('identifier') }}</span>
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
        var segmentName = function () {
            if ($('#segmentName').val() == 'Account')  {
                $('#identifier').prop('disabled', false);
            }
            else {
                $('#identifier').prop('disabled', 'disabled');
            }
        };
        $(segmentName);
        $("#segmentName").change(segmentName);
    });
</script>
@endsection
