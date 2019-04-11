@extends('layouts.master')

@section('title', trans('operational/menu.routes-rates'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.routes-rates') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <form  role="form" id="registerForm" class="form-horizontal" method="post" action="">
                    {{ csrf_field() }}
                    <div id="horizontal-form">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="kotaAsal" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="kotaAsal" id="kotaAsal">
                                        <option value="">ALL</option>
                                        @foreach($optionKota as $kota)
                                        <option value="{{ $kota->city_name }}" {{ !empty($filters['kotaAsal']) && $filters['kotaAsal'] == $kota->city_name ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="kotaTujuan" id="kotaTujuan">
                                        <option value="">ALL</option>
                                        @foreach($optionKota as $kota)
                                        <option value="{{ $kota->city_name }}" {{ !empty($filters['kotaTujuan']) && $filters['kotaTujuan'] == $kota->city_name ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="kode" class="col-sm-4 control-label">{{ trans('shared/common.kode') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="kode" name="kode" value="{{ !empty($filters['kode']) ? $filters['kode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = !empty($filters['status']) || !Session::has('filters') ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="data-table-toolbar">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="toolbar-btn-action">
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                    <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                        <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                    </a>
                                    @can('access', [$resource, 'insert'])
                                    <a href="{{ URL($url.'/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('shared/common.kode') }}</th>
                                <th>{{ trans('operational/fields.kota-asal') }}</th>
                                <th>{{ trans('operational/fields.kota-tujuan') }}</th>
                                <th>{{ trans('operational/fields.min-berat') }}</th>
                                <th>{{ trans('operational/fields.min-price') }}</th>
                                <th>{{ trans('operational/fields.tarif-kg') }}</th>
                                <th>{{ trans('operational/fields.tarif-m3') }}</th>
                                <th>{{ trans('operational/fields.delivery-estimation') }}</th>
                                <th>{{ trans('shared/common.keterangan') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($routes->currentPage() - 1) * $routes->perPage() + 1; ?>
                            @foreach($routes as $route)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $route->route_code }}</td>
                                <td>{{ $route->city_start_name }}</td>
                                <td>{{ $route->city_end_name }}</td>
                                <td class="text-right">{{ number_format($route->minimum_weight, 2) }}</td>
                                <td class="text-right">{{ number_format($route->minimum_rates) }}</td>
                                <td class="text-right">{{ number_format($route->rate_kg) }}</td>
                                <td class="text-right">{{ number_format($route->rate_m3) }}</td>
                                <td>{{ $route->delivery_estimation }}</td>
                                <td>{{ $route->description }}</td>
                                <td class="text-center"><i class="fa {{ $route->active == 'Y' ? 'fa-check' : 'fa-remove'}}"></i></td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $route->route_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $routes->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function(){
    $("#kotaAsal").select2();
    $("#kotaTujuan").select2();
});
</script>
@endsection
