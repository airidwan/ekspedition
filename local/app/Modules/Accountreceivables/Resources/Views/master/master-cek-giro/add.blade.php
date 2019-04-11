@extends('layouts.master')

@section('title', trans('accountreceivables/menu.master-cek-giro'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.master-cek-giro') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->cek_giro_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <?php $type = count($errors) > 0 ? old('type') : $model->cg_type; ?>
                                <label for="type" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.type') }}</label>
                                <div class="col-sm-8">
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="type" id="type" value="C" {{ $type == 'C' ? 'checked' : ''  }} > {{ trans('accountreceivables/fields.cek') }}
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="type" id="type" value="G" {{ $type == 'G' ? 'checked' : ''  }}> {{ trans('accountreceivables/fields.giro') }}
                                    </label>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('number') ? 'has-error' : '' }}">
                                <label for="number" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="number" name="number" value="{{ count($errors) > 0 ? old('number') : $model->cg_number }}">
                                    @if($errors->has('number'))
                                    <span class="help-block">{{ $errors->first('number') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $customerId = count($errors) > 0 ? old('customer') : $model->customer_id ?>
                            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="customer" id="customer">
                                        @foreach($optionCustomer as $data)
                                        <option value="{{ $data->customer_id }}:{{$data->customer_name }}" {{ $data->customer_id == $customerId ? 'selected' : '' }}>{{ $data->customer_name }}</option>
                                        @endforeach
                                    </select>
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
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('bankName') ? 'has-error' : '' }}">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $model->bank_name }}">
                                    @if($errors->has('bankName'))
                                    <span class="help-block">{{ $errors->first('bankName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $startDate = !empty(old('startDate')) ? new \DateTime(old('startDate')) : null;
                            } else {
                                $startDate = !empty($model->cg_date) ? new \DateTime($model->cg_date) : null;
                            }
                            ?>
                            <div class="form-group {{ $errors->has('startDate') ? 'has-error' : '' }}">
                            <label for="startDate" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.start-date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="text" id="startDate" name="startDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $startDate !== null ? $startDate->format('d-m-Y') : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $dueDate = !empty(old('dueDate')) ? new \DateTime(old('dueDate')) : null;
                            } else {
                                $dueDate = !empty($model->cg_due_date) ? new \DateTime($model->cg_due_date) : null;
                            }
                            ?>
                            <div class="form-group {{ $errors->has('dueDate') ? 'has-error' : '' }}">
                                <label for="dueDate" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.due-date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dueDate" name="dueDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $dueDate !== null ? $dueDate->format('d-m-Y') : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
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
        $('#customer').select2();
    });
</script>
@endsection
