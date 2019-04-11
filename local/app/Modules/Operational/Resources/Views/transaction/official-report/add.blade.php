<?php 
    use App\Service\Penomoran; 
    use App\Modules\Operational\Model\Transaction\OfficialReport;
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
    use App\Service\TimezoneDateConverter;
?>
@extends('layouts.master')

@section('title', trans('operational/menu.official-report'))

@section('header')
@parent
<style type="text/css">
    #table-lov-resi tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.official-report') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->official_report_id }}">
                        <div class="col-sm-10 portlets">
                            <div class="form-group">
                                <label for="officialReportNumber" class="col-sm-3 control-label">{{ trans('operational/fields.official-report-number') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="officialReportNumber" name="officialReportNumber"  value="{{ $model->official_report_number }}" readonly>
                                </div>
                            </div>
                            <?php
                                if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                                } else {
                                    $date = !empty($model->datetime) ? TimezoneDateConverter::getClientDateTime($model->datetime) :  TimezoneDateConverter::getClientDateTime();
                                }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-3 control-label">{{ trans('shared/common.date') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    @if($errors->has('date'))
                                        <span class="help-block">{{ $errors->first('date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('time') ? 'has-error' : '' }}">
                                <label for="time" class="col-sm-3 control-label">{{ trans('shared/common.time') }}</label>
                                <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                    <select class="form-control" id="hour" name="hour" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
                                        <?php $hourFormat = $date !== null ? $date->format('H') : '00' ; ?>
                                        <?php $hour = count($errors) > 0 ? old('hour') : $hourFormat ; ?>
                                        @for ($i = 0; $i < 24; $i++)
                                               <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hour == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                        @endfor
                                    </select>
                                    @if($errors->has('hour'))
                                    <span class="help-block">{{ $errors->first('hour') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                    <select class="form-control" id="minute" name="minute" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
                                        <?php $minuteFormat = $date !== null ? $date->format('i') : '' ; ?>
                                        <?php $minute = count($errors) > 0 ? old('minute') : $minuteFormat ; ?>
                                        @for ($i = 0; $i < 60; $i=$i+1)
                                               <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                        @endfor
                                    </select>
                                    @if($errors->has('minute'))
                                    <span class="help-block">{{ $errors->first('minute') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-3 control-label">{{ trans('shared/common.status') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="status" id="status" {{ $model->status != OfficialReport::OPEN || $model->created_by != \Auth::user()->id ? 'disabled' : '' }}>
                                        @foreach($optionStatus as $status)
                                            <option value="{{ $status }}" {{ $status == $model->status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('status'))
                                        <span class="help-block">{{ $errors->first('status') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-3 control-label">{{ trans('shared/common.category') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="category" id="category" {{ $model->status != OfficialReport::OPEN || $model->created_by != \Auth::user()->id ? 'disabled' : '' }}>
                                        @foreach($optionCategory as $category)
                                            <option value="{{ $category }}" {{ $category == $model->category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('category'))
                                        <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('toRole') ? 'has-error' : '' }}">
                                <label for="nama" class="col-sm-3 control-label">{{ trans('operational/fields.send-to-role') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="toRole[]" id="toRole" multiple="multiple" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
                                        <?php
                                        if (count($errors) > 0) {
                                            $toRoleId = old('toRole', []);
                                        } else {
                                            $toRoleId = [];
                                            $toRole   = DB::table('op.dt_official_report_to_role')->where('official_report_id', '=', $model->official_report_id)->get();
                                            foreach ($toRole as $role) {
                                                $toRoleId[] = $role->role_id;
                                            }
                                        }
                                        ?>
                                        @foreach($optionRole as $role)
                                        <option value="{{ $role->id }}" {{ in_array($role->id, $toRoleId) ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('toRole'))
                                    <span class="help-block">{{ $errors->first('toRole') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('toBranch') ? 'has-error' : '' }}">
                                <label for="nama" class="col-sm-3 control-label">{{ trans('operational/fields.send-to-branch') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="toBranch[]" id="toBranch" multiple="multiple" {{ !empty($model->official_report_id) ? 'disabled' : '' }}>
                                         <?php
                                        if (count($errors) > 0) {
                                            $toBranchId = old('toBranch', []);
                                        } else {
                                            $toBranchId = [];
                                            $toBranch   = DB::table('op.dt_official_report_to_branch')->where('official_report_id', '=', $model->official_report_id)->get();
                                            foreach ($toBranch as $branch) {
                                                $toBranchId[] = $branch->branch_id;
                                            }
                                        }
                                        ?>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ in_array($branch->branch_id, $toBranchId) ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('toBranch'))
                                    <span class="help-block">{{ $errors->first('toBranch') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('personName') ? 'has-error' : '' }}">
                                <label for="personName" class="col-sm-3 control-label">{{ trans('operational/fields.person-name') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="personName" name="personName"  value="{{ count($errors) > 0 ? old('personName') : $model->person_name }}" {{ $model->status != OfficialReport::OPEN || $model->created_by != \Auth::user()->id ? 'readonly' : '' }}>
                                    @if($errors->has('personName'))
                                        <span class="help-block">{{ $errors->first('personName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                $modelResi  = $model->resi;
                                $resiId     = !empty($modelResi) ? $modelResi->resi_header_id : '';
                                $resiNumber = !empty($modelResi) ? $modelResi->resi_number : '';
                            ?>
                            <div class="form-group {{ $errors->has('resiNumber') ? 'has-error' : '' }}">
                                <label for="resiNumber" class="col-sm-3 control-label">{{ trans('operational/fields.resi-number') }} </label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="hidden" name="resiId" id="resiId" value="{{ count($errors) > 0 ? old('resiId') : $resiId }}">
                                        <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                        <span class="btn input-group-addon {{ $model->status != OfficialReport::OPEN || $model->created_by != \Auth::user()->id ? '' : 'remove-resi' }}"><i class="fa fa-remove"></i></span>
                                        <span class="btn input-group-addon" id="{{ $model->status != OfficialReport::OPEN || $model->created_by != \Auth::user()->id ? '' : 'modalResi' }}" ><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('resiNumber'))
                                        <span class="help-block">{{ $errors->first('resiNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-3 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="description" rows="7" id="description" {{ $model->status != OfficialReport::OPEN || $model->created_by != \Auth::user()->id ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                        <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('respon') ? 'has-error' : '' }}">
                                <label for="respon" class="col-sm-3 control-label">{{ trans('shared/common.respon') }} </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="respon" rows="7" id="respon" {{ $model->status != OfficialReport::OPEN || $model->created_by == \Auth::user()->id ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('respon') : $model->respon }}</textarea>
                                    @if($errors->has('respon'))
                                        <span class="help-block">{{ $errors->first('respon') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if($model->status == OfficialReport::OPEN )
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
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

@section('modal')
@parent
<div id="modal-lov-resi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.resi') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchResi" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchResi" name="searchResi">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                    <th>{{ trans('operational/fields.customer') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('operational/fields.sender-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('operational/fields.receiver-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $("#toRole").select2();
        $("#toBranch").select2();
        $(".remove-resi").on('click', function() {
            $('#resiId').val('');
            $('#resiNumber').val('');
        });

        $('#modalResi').on('click', showLovResi);
        $('#searchResi').on('keyup', loadLovResi);
        $('#table-lov-resi tbody').on('click', 'tr', selectResi);

    });

    var showLovResi = function() {
        $('#searchResi').val('');
        loadLovResi(function() {
            $('#modal-lov-resi').modal('show');
        });
    };

    var xhrResi;
    var loadLovResi = function(callback) {
        if(xhrResi && xhrResi.readyState != 4){
            xhrResi.abort();
        }
        xhrResi = $.ajax({
            url: '{{ URL($url.'/get-json-resi') }}',
            data: {search: $('#searchResi').val()},
            success: function(data) {
                $('#table-lov-resi tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-resi tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.resi_number + '</td>\
                            <td>' + item.customer_name + '</td>\
                            <td>' + item.customer_address + '</td>\
                            <td>' + item.sender_name + '</td>\
                            <td>' + item.sender_address + '</td>\
                            <td>' + item.receiver_name + '</td>\
                            <td>' + item.receiver_address + '</td>\
                            <td>' + item.item_name + '</td>\
                            <td>' + item.total_coly + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectResi = function() {
        var data = $(this).data('json');
        $('#resiId').val(data.resi_header_id);
        $('#resiNumber').val(data.resi_number);

        $('#modal-lov-resi').modal('hide');
    };
    
</script>
@endsection
