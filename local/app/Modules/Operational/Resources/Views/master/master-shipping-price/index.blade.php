<?php
use App\Modules\Operational\Model\Master\MasterShippingPrice;
?>

@extends('layouts.master')

@section('title', trans('operational/menu.shipping-price'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.shipping-price') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="registerForm" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="kotaAsal" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="kotaAsal" id="kotaAsal">
                                        <option value="">ALL</option>
                                        @foreach($optionKota as $kota)
                                        <option value="{{ $kota->city_id }}" {{ !empty($filters['kotaAsal']) && $filters['kotaAsal'] == $kota->city_id ? 'selected' : '' }}>{{ $kota->city_name }}</option>
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
                                        <option value="{{ $kota->city_id }}" {{ !empty($filters['kotaTujuan']) && $filters['kotaTujuan'] == $kota->city_id ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="nama" class="col-sm-4 control-label">{{ trans('operational/fields.nama-barang') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="nama" name="nama" value="{{ !empty($filters['nama']) ? $filters['nama'] : '' }}">
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
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="toolbar-btn-action">
                                        <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                        <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                            <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                        </a>
                                        <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div><hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('operational/fields.nama-barang') }}</th>
                                <th>{{ trans('operational/fields.route-code') }}</th>
                                <th>{{ trans('operational/fields.tarif-kirim') }}</th>
                                <th>{{ trans('shared/common.keterangan') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $no = ($datas->currentPage() - 1) * $datas->perPage() + 1;?>
                            @foreach($datas as $data)
                            <?php
                            $data = MasterShippingPrice::find($data->shipping_price_id);
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $data->commodity !== null ? $data->commodity->commodity_name : '' }}</td>
                                <td>{{ $data->route !== null ? $data->route->route_code : '' }}</td>
                                <td class="text-right">{{ number_format($data->delivery_rate) }}</td>
                                <td>{{ $data->description }}</td>
                                <td class="text-center">
                                    <i class="fa {{ $data->active == 'Y' ? 'fa-check' : 'fa-remove' }}"></i>
                                </td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $data->shipping_price_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="data-table-toolbar">
                        {!! $datas->render() !!}
                    </div>
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

