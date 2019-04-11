@extends('layouts.master')

@section('title', trans('operational/menu.rent-car'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.rent-car') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->rent_car_id }}">
                        <input type="hidden" id="routeId" name="routeId" value="{{ count($errors) > 0 ? old('routeId') : $model->route_id }}">
                        <input type="hidden" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}">
                        <div class="col-sm-7 portlets">
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
                            <input type="hidden" id="startCity" name="startCity" value="{{ count($errors) > 0 ? old('startCity') : $startCity->city_name }}">
                            <div class="form-group {{ $errors->has('viewStartCity') ? 'has-error' : '' }}">
                                <label for="viewStartCity" class="col-sm-5 control-label">{{ trans('operational/fields.kota-asal') }} </label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="viewStartCity" name="viewStartCity" value="{{ count($errors) > 0 ? old('startCity') : $startCity->city_name }}" disabled>
                                    @if($errors->has('viewStartCity'))
                                    <span class="help-block">{{ $errors->first('viewStartCity') }}</span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" id="endCity" name="endCity" value="{{ count($errors) > 0 ? old('endCity') : $endCity->city_name }}">
                            <div class="form-group {{ $errors->has('viewEndCity') ? 'has-error' : '' }}">
                                <label for="viewEndCity" class="col-sm-5 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="viewEndCity" name="viewEndCity" value="{{ count($errors) > 0 ? old('endCity') : $endCity->city_name }}" disabled>
                                    @if($errors->has('viewEndCity'))
                                    <span class="help-block">{{ $errors->first('viewEndCity') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('truckCode') ? 'has-error' : '' }}">
                                <label for="truckCode" class="col-sm-5 control-label">{{ trans('operational/fields.kode-truk-kendaraan') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="truckCode" name="truckCode" value="{{ count($errors) > 0 ? old('truckCode') : $modelTruck->truck_code }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-kode-truk"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('truckCode'))
                                        <span class="help-block">{{ $errors->first('truckCode') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php 
                            $description = '';
                            if (!empty($modelTruck->truck_id)) {

                                $description=$modelTruck->getBrand().", ".$modelTruck->getType().", ".$modelTruck->police_number.", ".$modelTruck->owner_name;
                            }
                            ?>
                            
                            <div class="form-group">
                                <label for="description" class="col-sm-5 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="description" name="description" disabled value="{{ count($errors) > 0 ? old('description') : $description }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 portlets">
                            <div class="form-group {{ $errors->has('rentRate') ? 'has-error' : '' }}">
                                <label for="rentRate" class="col-sm-4 control-label">{{ trans('operational/fields.uang-sewa-per-trip') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="rentRate" name="rentRate" value="{{ count($errors) > 0 ? str_replace(',', '' ,old('rentRate')) : $model->rent_rate }}">
                                    @if($errors->has('rentRate'))
                                    <span class="help-block">{{ $errors->first('rentRate') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="keterangan" class="col-sm-4 control-label">{{ trans('shared/common.keterangan') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="keterangan" name="keterangan" rows="4">{{ count($errors) > 0 ? old('keterangan') : $model->description }}</textarea>
                                    @if($errors->has('keterangan'))
                                    <span class="help-block">{{ $errors->first('keterangan') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.active') }}</label>
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
                                <a href="{{ URL('operational/master/master-rent-car') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
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
                <table id="datatables-lov-rute" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
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
<div id="modal-lov-kode-truk" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.truck') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-truk" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.kode-truk-kendaraan') }}</th>
                            <th>{{ trans('operational/fields.brand') }}</th>
                            <th>{{ trans('operational/fields.type') }}</th>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('operational/fields.dimensi') }}</th>
                            <th>{{ trans('shared/common.kategori') }}</th>
                            <th>{{ trans('operational/fields.nama-pemilik') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionTruck as $truck)
                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $truck->truck_code }}
                                <input type="hidden" value="{{ $truck->truck_id }}" name="no-truk[]">
                                <input type="hidden" value="{{ $truck->truck_code }}" name="kode-truk[]">
                            </td>
                            <td>{{ $truck->vehicle_merk }}
                                <input type="hidden" value="{{ $truck->vehicle_merk }}" name="brand-truk[]">
                            </td>
                            <td>{{ $truck->vehicle_type }}
                                <input type="hidden" value="{{ $truck->vehicle_type }}" name="type-truk[]">
                            </td>
                            <td>{{ $truck->police_number }}
                                <input type="hidden" value="{{ $truck->police_number }}" name="nopol-truk[]">
                            </td>
                            <td>{{ $truck->long_tube }}
                                <input type="hidden" value="{{ $truck->long_tube }}" name="dimension[]">
                            </td>
                            <td>{{ $truck->vehicle_category }}
                                <input type="hidden" value="{{ $truck->category }}" name="kategori[]">
                            </td>
                            <td>{{ $truck->owner_name }}
                                <input type="hidden" value="{{ $truck->owner_name }}" name="nama-pemilik-truk[]">
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
        $("#datatables-lov-rute").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-rute tbody').on('click', 'tr', function () {
            var routeCode       = $(this).find('input[name="kode-rute[]"]').val();
            var routeId         = $(this).find('input[name="no-rute[]"]').val();
            var startCity       = $(this).find('input[name="kota-asal[]"]').val();
            var endCity         = $(this).find('input[name="kota-tujuan[]"]').val();
            var viewStartCity   = $(this).find('input[name="view-kota-asal[]"]').val();
            var viewEndCity     = $(this).find('input[name="view-kota-tujuan[]"]').val();
            $('#routeCode').val(routeCode);
            $('#routeId').val(routeId);
            $('#startCity').val(startCity);
            $('#endCity').val(endCity);
            $('#viewStartCity').val(viewStartCity);
            $('#viewEndCity').val(viewEndCity);
            $('#modal-lov-kode-rute').modal("hide");
        });

        $("#datatables-lov-truk").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-truk tbody').on('click', 'tr', function () {
            var truckCode       = $(this).find('input[name="kode-truk[]"]').val();
            var truckId         = $(this).find('input[name="no-truk[]"]').val();
            var brand         = $(this).find('input[name="brand-truk[]"]').val();
            var type         = $(this).find('input[name="type-truk[]"]').val();
            var nopol         = $(this).find('input[name="nopol-truk[]"]').val();
            var namaPemilik         = $(this).find('input[name="nama-pemilik-truk[]"]').val();
            $('#truckCode').val(truckCode);
            $('#truckId').val(truckId);
            $('#description').val(brand + ', ' + type + ', ' + nopol + ', ' + namaPemilik +'.');
            $('#modal-lov-kode-truk').modal("hide");
        });
    });
</script>
@endsection