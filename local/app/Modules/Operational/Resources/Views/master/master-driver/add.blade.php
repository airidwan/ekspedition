@extends('layouts.master')

@section('title', trans('operational/menu.driver'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.driver') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <ul id="demo1" class="nav nav-tabs">
                    <li class="active">
                        <a href="#driverTab" data-toggle="tab">{{ trans('operational/menu.driver') }} <span class="label label-success"></span></a>
                    </li>
                    <li class="">
                        <a href="#activationTab" data-toggle="tab">{{ trans('shared/common.activation') }} <span class="badge badge-primary"></span></a>
                    </li>
                </ul>
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->driver_id }}">
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="driverTab">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                        <label for="code" class="col-sm-4 control-label">{{ trans('shared/common.code') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="code" name="code"  value="{{ !empty($model->driver_code) ? $model->driver_code : '' }}" disabled>
                                            @if($errors->has('code'))
                                            <span class="help-block">{{ $errors->first('code') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label for="name" class="col-sm-4 control-label">{{ trans('shared/common.name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="name" name="name" value="{{ count($errors) > 0 ? old('name') : $model->driver_name }}">
                                            @if($errors->has('name'))
                                            <span class="help-block">{{ $errors->first('name') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('nickname') ? 'has-error' : '' }}">
                                        <label for="nickname" class="col-sm-4 control-label">{{ trans('operational/fields.nickname') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="nickname" name="nickname" value="{{ count($errors) > 0 ? old('nickname') : $model->driver_nickname }}">
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('identityNumber') ? 'has-error' : '' }}">
                                        <label for="identityNumber" class="col-sm-4 control-label">{{ trans('operational/fields.no-ktp') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="identityNumber" name="identityNumber" value="{{ count($errors) > 0 ? old('identityNumber') : $model->identity_number }}">
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="address" name="address" rows="2">{{ count($errors) > 0 ? old('address') : $model->address }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">
                                        <label for="city" class="col-sm-4 control-label">{{ trans('shared/common.city') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
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
                                    <input type="hidden" name="subAccountCode" value="{{ count($errors) > 0 ? old('subAccountCode') : $model->subaccount_code }}">
                                    <div class="form-group {{ $errors->has('viewSubAccountCode') ? 'has-error' : '' }}">
                                        <label for="viewSubAccountCode" class="col-sm-4 control-label">{{ trans('operational/fields.sub-account') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="viewSubAccountCode" name="viewSubAccountCode" value="{{ count($errors) > 0 ? old('subAccountCode') : $model->subaccount_code }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                        <label for="phone" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ count($errors) > 0 ? old('phone') : $model->no_phone }}">
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                                        <label for="position" class="col-sm-4 control-label">{{ trans('operational/fields.position') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="position" name="position">
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                <?php $stringPosition = count($errors) > 0 ? old('position') : $model->position; ?>
                                                @foreach($optionPosition as $position)
                                                <option value="{{ $position->lookup_code }}" {{ $position->lookup_code == $stringPosition ? 'selected' : '' }}>{{ $position->meaning }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('position'))
                                            <span class="help-block">{{ $errors->first('position') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type">
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                <?php $stringType = count($errors) > 0 ? old('type') : $model->type; ?>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $joinDate = !empty(old('joinDate')) ? new \DateTime(old('joinDate')) : null;
                                    } else {
                                        $joinDate = !empty($model->join_date) ? new \DateTime($model->join_date) : null;
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('joinDate') ? 'has-error' : '' }}">
                                        <label for="joinDate" class="col-sm-4 control-label">{{ trans('operational/fields.tanggal-masuk') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="joinDate" name="joinDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $joinDate !== null ? $joinDate->format('d-m-Y') : '' }}">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $resignDate = !empty(old('resignDate')) ? new \DateTime(old('resignDate')) : null;
                                    } else {
                                        $resignDate = !empty($model->resign_date) ? new \DateTime($model->resign_date) : null;
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('resignDate') ? 'has-error' : '' }}">
                                        <label for="resignDate" class="col-sm-4 control-label">{{ trans('operational/fields.tanggal-keluar') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="resignDate" name="resignDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $resignDate !== null ? $resignDate->format('d-m-Y') : '' }}">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                        $merriedStatus = count($errors) > 0 ? old('merriedStatus') : $model->merried_status;  
                                    ?>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.status-pernikahan') }}</label>
                                        <div class="col-sm-8">
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="merriedStatus" id="merriedStatus" value="Merried" {{ !empty($merriedStatus) && $merriedStatus == "Merried" ? 'checked' : '' }}> {{ trans('operational/fields.menikah') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="merriedStatus" id="merriedStatus" value="Not Merried" {{ !empty($merriedStatus)&& $merriedStatus == "Not Merried" ? 'checked' : '' }}> {{ trans('operational/fields.belum-menikah') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4     control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="2">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
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
                            </div>
                            <div class="tab-pane fade" id="activationTab">
                                <div class="col-sm-12 portlets">
                                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('shared/common.cabang') }}</th>
                                                <th><input name="all-branch" id="all-branch" type="checkbox" ></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $detailBranchId = [];
                                                if (count($errors) > 0) {
                                                    $detailBranchId = old('branchDetail',[]);
                                                }else{
                                                    $branchDetail   = DB::table('op.dt_driver_branch')->where('driver_id', '=', $model->driver_id)->get();
                                                    foreach ($branchDetail as $dtlCabang) {
                                                        $detailBranchId[] = $dtlCabang->branch_id;
                                                    }
                                                }
                                            ?>
                                            @foreach($optionBranch as $branch)
                                            <tr>
                                                <td>{{ $branch->branch_name }} </td>
                                                <td class="text-center">
                                                    <input name="branchDetail[]" value="{{ $branch->branch_id }}" type="checkbox" class="rows-check" {{ in_array($branch->branch_id, $detailBranchId) ? 'checked' : '' }}  >
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL('operational/master/master-driver') }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
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
        $('#all-branch').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });
    });
</script>
@endsection
