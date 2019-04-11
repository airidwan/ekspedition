@extends('layouts.master')

@section('title', trans('operational/menu.shipping-price'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.shipping-price') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->shipping_price_id }}">
                        <div class="col-sm-6 portlets">
                            <?php
                            $commodityName = $model->commodity !== null ? $model->commodity->commodity_name : '';
                            ?>
                            <div class="form-group {{ $errors->has('commodityId') ? 'has-error' : '' }}">
                                <label for="commodityId" class="col-sm-5 control-label">{{ trans('operational/fields.nama-barang') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="hidden" id="commodityId" name="commodityId" value="{{ count($errors) > 0 ? old('commodityId') : $model->commodity_id }}">
                                        <input type="text" class="form-control" id="namaBarang" name="namaBarang" value="{{ count($errors) > 0 ? old('namaBarang') : $commodityName }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="{{ empty($model->shipping_price_id) ? 'modal' : '' }}" data-target="#modal-lov-commodity"><i class="fa fa-search"></i></span>

                                    </div>
                                        @if($errors->has('commodityId'))
                                        <span class="help-block">{{ $errors->first('commodityId') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                            $route = $model->route()->first();
                            $kodeRute = $route !== null ? $route->route_code : '';
                            ?>
                            <div class="form-group {{ $errors->has('kodeRute') ? 'has-error' : '' }}">
                                <label for="kodeRute" class="col-sm-5 control-label">{{ trans('operational/fields.kode-rute') }} <span class="required">*</span></label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="hidden" id="idRute" name="idRute" value="{{ count($errors) > 0 ? old('idRute') : $model->route_id }}">
                                        <input type="text" class="form-control" id="kodeRute" name="kodeRute" value="{{ count($errors) > 0 ? old('kodeRute') : $kodeRute }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="{{ empty($model->shipping_price_id) ? 'modal' : '' }}" data-target="#modal-lov-kode-rute"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('kodeRute'))
                                        <span class="help-block">{{ $errors->first('kodeRute') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                            $kotaAsal = $route !== null ? $route->cityStart()->first() : null;
                            $namaKotaAsal = $kotaAsal !== null ? $kotaAsal->city_name : '';
                            ?>
                            <div class="form-group">
                                <label for="kotaAsal" class="col-sm-5 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="kotaAsal" name="kotaAsal" value="{{ count($errors) > 0 ? old('kotaAsal') : $namaKotaAsal }}" readonly>
                                </div>
                            </div>
                            <?php
                            $kotaTujuan = $route !== null ? $route->cityEnd()->first() : null;
                            $namaKotaTujuan = $kotaTujuan !== null ? $kotaTujuan->city_name : '';
                            ?>
                            <input type="hidden" name="kotaTujuan">
                            <div class="form-group">
                                <label for="kotaTujuan" class="col-sm-5 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="kotaTujuan" name="kotaTujuan" value="{{ count($errors) > 0 ? old('kotaTujuan') : $namaKotaTujuan }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('tarifKirim') ? 'has-error' : '' }}">
                                <label for="tarifKirim" class="col-sm-4 control-label">{{ trans('operational/fields.tarif-kirim') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="tarifKirim" name="tarifKirim" value="{{ count($errors) > 0 ? str_replace(',', '', old('tarifKirim')) : $model->delivery_rate }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan" class="col-sm-4 control-label">{{ trans('shared/common.keterangan') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="keterangan" name="keterangan" rows="4">{{ count($errors) > 0 ? old('keterangan') : $model->description }}</textarea>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('aktif') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <?php
                        $idRute = count($errors) > 0 && empty(old('idRute')) ? 0 : old('idRute');  
                        $route = count($errors) > 0 ? \App\Modules\Operational\Model\Master\MasterRoute::find($idRute) : $model->route()->first();
                        ?>
                        <div class="col-sm-12">
                            <hr><br>
                            <h3>{{ trans('operational/fields.detail-rute') }}</h3>
                            <table id="table-detail" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('operational/fields.kota-asal') }}</th>
                                        <th>{{ trans('operational/fields.kota-tujuan-transit') }}</th>
                                        <th>{{ trans('operational/fields.tarif-kirim') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($route !== null && $route->details()->count() > 0)
                                    @foreach($route->details()->get() as $routeDetail)
                                    <?php
                                    $kotaAsalDetail = $routeDetail->cityStart()->first();
                                    $kotaTujuanDetail = $routeDetail->cityEnd()->first();
                                    $detailShipping = \DB::table('op.dt_shipping_price')
                                                            ->where('dt_route_id', '=', $routeDetail->dt_route_id)
                                                            ->where('shipping_price_id', '=', $model->shipping_price_id)
                                                            ->first();
                                    $detailShippingPrice = $detailShipping !== null ? $detailShipping->delivery_rate : 0;
                                    ?>
                                    <tr>
                                        <td>{{ $kotaAsalDetail !== null ? $kotaAsalDetail->city_name : '' }}</td>
                                        <td>{{ $kotaTujuanDetail !== null ? $kotaTujuanDetail->city_name : '' }}</td>
                                        <td class="text-center">
                                            <input type="text" class="form-control currency" name="tarifDetail-{{ $routeDetail->dt_route_id }}" value="{{ count($errors) > 0 ? str_replace(',', '', old('tarifDetail-' . $routeDetail->dt_route_id)) : $detailShippingPrice }}" />
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning">
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
<div id="modal-lov-commodity" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.commodity') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-commodity" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.commodity') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionCommodity as $commodity)
                        <tr style="cursor: pointer;" data-commodity="{{ json_encode($commodity) }}">
                            <td>{{ $commodity->commodity_name }}</td>
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
                        @foreach ($optionRoute as $rute)
                        <?php
                            $detailRute = \DB::table('op.v_dt_route')->where('route_id', '=', $rute->route_id)->orderBy('dt_route_id', 'asc')->get();
                        ?>
                        <tr style="cursor: pointer;" data-rute="{{ json_encode($rute) }}" data-detail-rute="{{ json_encode($detailRute) }}">
                            <td>{{ $rute->route_code }}</td>
                            <td>{{ $rute->city_start_name }}</td>
                            <td>{{ $rute->city_end_name }}</td>
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
    $("#datatables-lov-commodity").dataTable({ "pagelength" : 10, "lengthChange": false });
    $('#datatables-lov-commodity tbody').on('click', 'tr', selectCommodity);

    $("#datatables-lov-rute").dataTable({ "pagelength" : 10, "lengthChange": false });
    $('#datatables-lov-rute tbody').on('click', 'tr', selectRute);
});

var selectCommodity = function () {
    var dataCommodity = $(this).data('commodity');
    $('#commodityId').val(dataCommodity.commodity_id);
    $('#namaBarang').val(dataCommodity.commodity_name);

    $('#modal-lov-commodity').modal("hide");
};

var selectRute = function () {
    var dataRute = $(this).data('rute');
    var dataDetailRute = $(this).data('detail-rute');

    $('#kodeRute').val(dataRute.route_code);
    $('#idRute').val(dataRute.route_id);
    $('#kotaAsal').val(dataRute.city_start_name);
    $('#kotaTujuan').val(dataRute.city_end_name);

    $('#table-detail tbody').html('');
    for (var i = 0; i < dataDetailRute.length; i++) {
        $('#table-detail tbody').append(
            '<tr>' +
            '<td>' + dataDetailRute[i].city_start_name + '</td>' +
            '<td>' + dataDetailRute[i].city_end_name + '</td>' +
            '<td><input type="text" class="form-control currency" name="tarifDetail-' + dataDetailRute[i].dt_route_id + '" value="0"</td>' +
            '</tr>'
        );
    }

    $('.currency').autoNumeric('init', {mDec: 0});

    $('#modal-lov-kode-rute').modal("hide");
};

</script>
@endsection
