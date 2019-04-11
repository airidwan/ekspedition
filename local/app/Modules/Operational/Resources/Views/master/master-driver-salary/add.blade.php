@extends('layouts.master')

@section('title', trans('operational/menu.driver-salary'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.driver-salary') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->driver_salary_id }}">
                        <input type="hidden" id="routeId" name="routeId" value="{{ count($errors) > 0 ? old('routeId') : $model->route_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('routeCode') ? 'has-error' : '' }}">
                                <label for="routeCode" class="col-sm-5 control-label">{{ trans('operational/fields.kode-rute') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="routeCode" name="routeCode" value="{{ count($errors) > 0 ? old('routeCode') : $routeCode }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-kode-rute"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('routeCode'))
                                        <span class="help-block">{{ $errors->first('routeCode') }}</span>
                                        @endif
                                </div>
                            </div>
                            <input type="hidden" name="startCity" id="startCity" value="{{ count($errors) > 0 ? old('startCity') : $startCity->city_name }}">
                            <div class="form-group {{ $errors->has('viewStartCity') ? 'has-error' : '' }}">
                                <label for="viewStartCity" class="col-sm-5 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="viewStartCity" name="viewStartCity" value="{{ count($errors) > 0 ? old('startCity') : $startCity->city_name }}" disabled>
                                    @if($errors->has('viewStartCity'))
                                    <span class="help-block">{{ $errors->first('viewStartCity') }}</span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="endCity" id="endCity" value="{{ count($errors) > 0 ? old('endCity') : $endCity->city_name }}">
                            <div class="form-group {{ $errors->has('viewEndCity') ? 'has-error' : '' }}">
                                <label for="viewEndCity" class="col-sm-5 control-label">{{ trans('operational/fields.kota-tujuan') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="viewEndCity" name="viewEndCity" value="{{ count($errors) > 0 ? old('viewEndCity') : $endCity->city_name }}" disabled>
                                    @if($errors->has('viewEndCity'))
                                    <span class="help-block">{{ $errors->first('viewEndCity') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                                <label for="position" class="col-sm-5 control-label">{{ trans('operational/fields.position') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
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
                                <label for="type" class="col-sm-5 control-label">{{ trans('operational/fields.type') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = count($errors) > 0 ? old('type') : $model->vehicle_type; ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type->lookup_code }}" {{ $type->lookup_code == $stringType ? 'selected' : '' }}>{{ $type->meaning }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 portlets">
                            <div class="form-group {{ $errors->has('salary') ? 'has-error' : '' }}">
                                <label for="salary" class="col-sm-4 control-label">{{ trans('operational/fields.salary') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="salary" name="salary" value="{{ count($errors) > 0 ? str_replace(',', '', old('salary')) : $model->salary }}">
                                    @if($errors->has('salary'))
                                    <span class="help-block">{{ $errors->first('salary') }}</span>
                                    @endif
                                </div>
                            </div>
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
                                <a href="{{ URL('operational/master/master-driver-salary') }}" class="btn btn-sm btn-warning">
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

@section('modal')
@parent
<div id="modal-lov-kode-rute" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.rute') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.kode-rute') }}</th>
                            <th>{{ trans('operational/fields.kota-asal') }}</th>
                            <th>{{ trans('operational/fields.kota-tujuan') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionRoute as $route)

                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $route->route_code }}
                                <input type="hidden" value="{{ $route->route_id }}" name="no-rute[]">
                                <input type="hidden" value="{{ $route->route_code }}" name="kode-rute[]">
                            </td>
                            <td>{{ $route->city_start_name }}
                                <input type="hidden" value="{{ $route->city_start_name }}" name="kota-asal[]">
                                <input type="hidden" value="{{ $route->city_start_name }}" name="view-kota-asal[]">
                            </td>
                            <td>{{ $route->city_end_name }}
                                <input type="hidden" value="{{ $route->city_end_name }}" name="kota-tujuan[]">
                                <input type="hidden" value="{{ $route->city_end_name }}" name="view-kota-tujuan[]">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function() {
        $("#datatables-lov").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var routeCode       = $(this).find('input[name="kode-rute[]"]').val();
            var routeId         = $(this).find('input[name="no-rute[]"]').val();
            var startCity       = $(this).find('input[name="kota-asal[]"]').val();
            var endCity     = $(this).find('input[name="kota-tujuan[]"]').val();
            var viewStartCity   = $(this).find('input[name="view-kota-asal[]"]').val();
            var viewEndCity = $(this).find('input[name="view-kota-tujuan[]"]').val();
            $('#routeCode').val(routeCode);
            $('#routeId').val(routeId);
            $('#startCity').val(startCity);
            $('#endCity').val(endCity);
            $('#viewStartCity').val(viewStartCity);
            $('#viewEndCity').val(viewEndCity);
            $('#modal-lov-kode-rute').modal("hide");
        });
    });
</script>
@endsection
