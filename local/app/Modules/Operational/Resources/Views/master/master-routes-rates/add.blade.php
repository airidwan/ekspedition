<?php
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
?>

@extends('layouts.master')

@section('title', trans('operational/menu.routes-rates'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.routes-rates') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->route_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabMasterRuteTarif" data-toggle="tab">{{ trans('operational/menu.routes-rates') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabDetailRute" data-toggle="tab">{{ trans('operational/fields.detail-rute') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabAktifasi" data-toggle="tab">{{ trans('shared/common.activation') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabMasterRuteTarif">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('kode') ? 'has-error' : '' }}">
                                        <label for="kode" class="col-sm-4 control-label">{{ trans('shared/common.kode') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kode" name="kode"  value="{{ !empty($model->route_code) ? $model->route_code : '' }}" disabled>
                                            @if($errors->has('kode'))
                                            <span class="help-block">{{ $errors->first('kode') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('kotaAsal') ? 'has-error' : '' }}">
                                        <label for="kotaAsal" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <?php $kotaAsal = $model->cityStart()->first() !== null ? $model->cityStart()->first() : $currentBranchCity; ?>
                                            <input type="hidden" name="kotaAsalError" value="{{ $kotaAsal->city_name }}">
                                            <input type="text" class="form-control" name="viewKotaAsal" value="{{ count($errors) > 0 ? old('kotaAsalError') : $kotaAsal->city_name }}" disabled>
                                            @if($errors->has('kotaAsal'))
                                            <span class="help-block">{{ $errors->first('kotaAsal') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('kotaTujuan') ? 'has-error' : '' }}">
                                        <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <?php $kotaId = count($errors) > 0 ? old('kotaTujuan') : $model->city_end_id; ?>
                                            <input type="hidden" name="kotaTujuan" value="{{ $kotaId }}">
                                            <select class="form-control" id="kotaTujuan" name="kotaTujuan" {{ $title == 'Edit' ? 'disabled' : '' }}>
                                                <option value="">{{ trans('shared/common.select-city') }}</option>
                                                @foreach($optionKota as $kota)
                                                <option value="{{ $kota->city_id }}" {{ $kota->city_id == $kotaId ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('kotaTujuan'))
                                            <span class="help-block">{{ $errors->first('kotaTujuan') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label for="estimation" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-estimation') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="estimation" name="estimation" rows="2">{{ count($errors) > 0 ? old('estimation') : $model->delivery_estimation }}</textarea>
                                            @if($errors->has('estimation'))
                                            <span class="help-block">{{ $errors->first('estimation') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label for="keterangan" class="col-sm-4 control-label">{{ trans('shared/common.keterangan') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="keterangan" name="keterangan" rows="2">{{ count($errors) > 0 ? old('keterangan') : $model->description }}</textarea>
                                            @if($errors->has('keterangan'))
                                            <span class="help-block">{{ $errors->first('keterangan') }}</span>
                                            @endif
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
                                <div class="col-sm-6 portlets">
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border">{{ trans('operational/fields.harga') }}</legend>
                                        <div class="form-group {{ $errors->has('perKg') ? 'has-error' : '' }}">
                                            <label for="perKg" class="col-sm-4 control-label">Per KG</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="perKg" name="perKg" value="{{ count($errors) > 0 ? str_replace(',', '', old('perKg')) : $model->rate_kg }}">
                                                @if($errors->has('perKg'))
                                                <span class="help-block">{{ $errors->first('perKg') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('perM3') ? 'has-error' : '' }}">
                                            <label for="perM3" class="col-sm-4 control-label">Per M<sup>3</sup></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="perM3" name="perM3" value="{{ count($errors) > 0 ? str_replace(',', '', old('perM3')) : $model->rate_m3 }}">
                                                @if($errors->has('perM3'))
                                                <span class="help-block">{{ $errors->first('perM3') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('minBeratKirim') ? 'has-error' : '' }}">
                                            <label for="minBeratKirim" class="col-sm-4 control-label">{{ trans('operational/fields.min-berat') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control decimal text-right" id="minBeratKirim" name="minBeratKirim" value="{{ count($errors) > 0 ? str_replace(',', '', old('minBeratKirim')) : $model->minimum_weight }}">
                                                @if($errors->has('minBeratKirim'))
                                                <span class="help-block">{{ $errors->first('minBeratKirim') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="minBiayaKirim" class="col-sm-4 control-label">{{ trans('operational/fields.min-price') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="minBiayaKirim" name="minBiayaKirim" value="{{ count($errors) > 0 ? str_replace(',', '', old('minBiayaKirim')) : $model->minimum_rates }}" disabled>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabAktifasi">
                                <div class="table-responsive">
                                    <div class="col-sm-12 portlets">
                                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('shared/common.cabang') }}</th>
                                                    <th><input name="all-cabang" id="all-cabang" type="checkbox" ></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $detailCabangId = [];
                                                if (count($errors) > 0) {
                                                    $detailCabangId = old('detailCabang',[]);
                                                }else{
                                                    foreach ($model->routeBranch()->get() as $routeBranch) {
                                                        $detailCabangId[] = $routeBranch->branch_id;
                                                    }
                                                }
                                                ?>
                                                @foreach($optionCabang as $cabang)
                                                <tr>
                                                    <td>{{ $cabang->branch_name }}</td>
                                                    <td class="text-center">
                                                        <input name="detailCabang[]" value="{{ $cabang->branch_id }}" type="checkbox" class="rows-check" {{ in_array($cabang->branch_id, $detailCabangId) ? 'checked' : '' }}  >
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabDetailRute">
                                <div class="data-table-toolbar">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="toolbar-btn-action">
                                                <a id="add-detail-action" class="btn btn-sm btn-primary md-trigger" data-modal="modal-add-detail">
                                                    <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add') }} {{ trans('operational/fields.detail-rute') }}
                                                </a>
                                                <a id="clear-details" href="#" class="btn btn-sm btn-danger">
                                                    <i class="fa fa-remove"></i> {{ trans('shared/common.clear') }} {{ trans('operational/fields.detail-rute') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="table-detail" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('operational/fields.kota-asal') }}</th>
                                                <th>{{ trans('operational/fields.kota-tujuan') }}</th>
                                                <th>{{ trans('operational/fields.tarif-kg') }}</th>
                                                <th>{{ trans('operational/fields.tarif-m3') }}</th>
                                                <th>{{ trans('shared/common.keterangan') }}</th>
                                                <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $indexDetail = 0; ?>
                                            @if(count($errors) > 0)
                                            @for($i = 0; $i < count(old('kotaAsalDetail', [])); $i++)
                                            <?php
                                            $namaKotaAsal = '';
                                            $namaKotaTujuan = '';
                                            foreach ($optionKota as $kota) {
                                                if (old('kotaAsalDetail')[$i] == $kota->city_id) {
                                                    $namaKotaAsal = $kota->city_name;
                                                }
                                                if (old('kotaTujuanDetail')[$i] == $kota->city_id) {
                                                    $namaKotaTujuan = $kota->city_name;
                                                }
                                            }
                                            ?>
                                            <tr data-index="{{ $indexDetail }}">
                                                <td>{{ $namaKotaAsal }}</td>
                                                <td>{{ $namaKotaTujuan }}</td>
                                                <td class="text-right">{{ old('perKgDetail')[$i] }}</td>
                                                <td class="text-right">{{ old('perM3Detail')[$i] }}</td>
                                                <td>{{ old('keteranganDetail')[$i] }}</td>
                                                <input type="hidden" name="idDetail[]" value="{{ old('idDetail')[$i] }}">
                                                <input type="hidden" name="kotaAsalDetail[]" value="{{ old('kotaAsalDetail')[$i] }}">
                                                <input type="hidden" name="kotaTujuanDetail[]" value="{{ old('kotaTujuanDetail')[$i] }}">
                                                <input type="hidden" name="perKgDetail[]" value="{{ old('perKgDetail')[$i] }}">
                                                <input type="hidden" name="perM3Detail[]" value="{{ old('perM3Detail')[$i] }}">
                                                <input type="hidden" name="keteranganDetail[]" value="{{ old('keteranganDetail')[$i] }}">
                                                <td class="text-center">
                                                    <a data-toggle="tooltip" class="btn btn-warning btn-xs edit-detail" ><i class="fa fa-pencil"></i></a>
                                                    <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-detail" ><i class="fa fa-remove"></i></a>
                                                </td>
                                            </tr>
                                            <?php $indexDetail++; ?>
                                            @endfor
                                            @else
                                            @foreach($model->details()->get() as $modelDtl)
                                            <?php $kotaAsal = $modelDtl->cityStart()->first(); ?>
                                            <?php $kotaTujuan = $modelDtl->cityEnd()->first(); ?>
                                            <tr data-index="{{ $indexDetail }}">
                                                <td>{{ $kotaAsal !== null ? $kotaAsal->city_name : '' }}</td>
                                                <td>{{ $kotaTujuan !== null ? $kotaTujuan->city_name : '' }}</td>
                                                <td class="text-right">{{  number_format($modelDtl->rate_kg) }}</td>
                                                <td class="text-right">{{  number_format($modelDtl->rate_m3) }}</td>
                                                <td>{{ $modelDtl->description }}</td>
                                                <input type="hidden" name="idDetail[]" value="{{ $modelDtl->dt_route_id }}">
                                                <input type="hidden" name="kotaAsalDetail[]" value="{{ $modelDtl->city_start_id }}">
                                                <input type="hidden" name="kotaTujuanDetail[]" value="{{ $modelDtl->city_end_id }}">
                                                <input type="hidden" name="perKgDetail[]" value="{{ number_format($modelDtl->rate_kg) }}">
                                                <input type="hidden" name="perM3Detail[]" value="{{ number_format($modelDtl->rate_m3) }}">
                                                <input type="hidden" name="keteranganDetail[]" value="{{ $modelDtl->description }}">
                                                <td class="text-center">
                                                    <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-detail"><i class="fa fa-pencil"></i></a>
                                                    <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-detail" ><i class="fa fa-remove"></i></a>
                                                </td>
                                            </tr>
                                            <?php $indexDetail++; ?>
                                            @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ url($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-print"></i> {{ trans('shared/common.save') }}</a>
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
    <div class="md-modal-lg md-fade-in-scale-up" id="modal-add-detail">
        <div class="md-content">
            <h3><strong id="title-modal-detail">{{ trans('shared/common.add') }}</strong> {{ trans('operational/fields.detail-rute') }}</h3>
            <div>
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                                {{ csrf_field() }}
                                <div class="col-sm-6 portlets">
                                    <input type="hidden" name="indexDetailForm" id="indexDetailForm" value="">
                                    <input type="hidden" name="idDetail" id="idDetail" value="">
                                    <div class="form-group {{ $errors->has('kotaAsalDetail') ? 'has-error' : '' }}">
                                        <label for="kotaAsalDetail" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="kotaAsalDetail" name="kotaAsalDetail">
                                                <?php $kotaId = count($errors) > 0 ? old('kotaAsalDetail') : $model->city_start_id; ?>
                                                <option value="">{{ trans('shared/common.select-city') }}</option>
                                                @foreach($optionKota as $kota)
                                                <option value="{{ $kota->city_id }}" {{ $kota->city_id == $kotaId ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('kotaTujuanDetail') ? 'has-error' : '' }}">
                                        <label for="kotaTujuanDetail" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="kotaTujuanDetail" name="kotaTujuanDetail">
                                                <?php $kotaId = count($errors) > 0 ? old('kotaTujuanDetail') : $model->id_kota_tujuan; ?>
                                                <option value="">{{ trans('shared/common.select-city') }}</option>
                                                @foreach($optionKota as $kota)
                                                <option value="{{ $kota->city_id }}" {{ $kota->city_id == $kotaId ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label for="keteranganDetail" class="col-sm-4 control-label">{{ trans('shared/common.keterangan') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="keteranganDetail" name="keteranganDetail" rows="4"></textarea>
                                            @if($errors->has('keteranganDetail'))
                                            <span class="help-block">{{ $errors->first('keteranganDetail') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border">{{ trans('operational/fields.harga') }}</legend>
                                        <div class="form-group {{ $errors->has('perKgDetail') ? 'has-error' : '' }}">
                                            <label for="perKgDetail" class="col-sm-4 control-label">Per KG</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="perKgDetail" name="perKgDetail" value="">
                                                <span class="help-block"></span>
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('perM3Detail') ? 'has-error' : '' }}">
                                            <label for="perM3Detail" class="col-sm-4 control-label">Per M<sup>3</sup></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="perM3Detail" name="perM3Detail" value="">
                                                <span class="help-block"></span>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="form-group text-right">
                                    <div class="col-sm-12">
                                        <a class="btn btn-sm btn-warning md-close">{{ trans('shared/common.cancel') }}</a>
                                        <a id="add-detail" class="btn btn-sm btn-primary">
                                            <span id="submit-modal-detail">{{ trans('shared/common.add') }}</span> {{ trans('operational/fields.detail-rute') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('script')
    @parent()
    <script type="text/javascript">
        var indexDetail = {{ $indexDetail }};
        var pembulatan = {{ TransactionResiHeader::PEMBULATAN }};
        $(document).on('ready', function(){
            $('#kotaAsal').select2();
            $('#kotaTujuan').select2();
            $('#kotaAsalDetail').select2();
            $('#kotaTujuanDetail').select2();

            $('#all-cabang').on('ifChanged', function(){
                var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
                if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                    $inputs.iCheck('check');
                } else {
                    $inputs.iCheck('uncheck');
                }
            });

            var hitungMinBiayaKirim = function() {
                var minBeratKirim = $('#minBeratKirim').val().split(',').join('');
                var perKg = $('#perKg').val().split(',').join('');
                var minBiayaKirim = Math.floor(minBeratKirim * perKg / pembulatan) * pembulatan;

                $('#minBiayaKirim').val(minBiayaKirim).autoNumeric('update', {mDec: 0});
            };

            hitungMinBiayaKirim();

            $('#perKg').on('keyup', function() {
                hitungMinBiayaKirim();
            });

            $('#minBeratKirim').on('keyup', function() {
                hitungMinBiayaKirim();
            });

            $('.delete-detail').on('click', function() {
                $(this).parent().parent().remove();
            });

            $('#add-detail-action').on('click', function(){
                clearFormDetail();
                $('#title-modal-detail').html('{{ trans('shared/common.add') }}');
                $('#submit-modal-detail').html('{{ trans('shared/common.add') }}');
            });

            $('#add-detail').on('click', function() {
                var indexDetailForm       = $('#indexDetailForm').val();
                var idDetail              = $('#idDetail').val();
                var kotaAsalDetail        = $('#kotaAsalDetail').val();
                var kotaAsalDetailLabel   = $('#kotaAsalDetail option:selected').text();
                var kotaTujuanDetail      = $('#kotaTujuanDetail').val();
                var kotaTujuanDetailLabel = $('#kotaTujuanDetail option:selected').text();
                var perKgDetail           = $('#perKgDetail').val();
                var perM3Detail           = $('#perM3Detail').val();
                var keteranganDetail      = $('#keteranganDetail').val();
                var error = false;

                if (kotaAsalDetail == '' || kotaAsalDetail <= 0) {
                    $('#kotaAsalDetail').parent().parent().addClass('has-error');
                    $('#kotaAsalDetail').parent().find('span.help-block').html('City Origin is required');
                    error = true;
                } else {
                    $('#kotaAsalDetail').parent().parent().removeClass('has-error');
                    $('#kotaAsalDetail').parent().find('span.help-block').html('');
                }

                if (kotaTujuanDetail == '' || kotaTujuanDetail <= 0) {
                    $('#kotaTujuanDetail').parent().parent().addClass('has-error');
                    $('#kotaTujuanDetail').parent().find('span.help-block').html('Last Destination is required');
                    error = true;
                } else {
                    $('#kotaTujuanDetail').parent().parent().removeClass('has-error');
                    $('#kotaTujuanDetail').parent().find('span.help-block').html('');
                }

                if (error) {
                    return;
                }

                var htmlTr = '<td >' + kotaAsalDetailLabel + '</td>' +
                '<td >' + kotaTujuanDetailLabel + '</td>' +
                '<td class="text-right">' + perKgDetail + '</td>' +
                '<td class="text-right">' + perM3Detail + '</td>' +
                '<td >' + keteranganDetail + '</td>' +
                '<td class="text-center">' +
                '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-detail" ><i class="fa fa-pencil"></i></a> ' +
                '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-detail" ><i class="fa fa-remove"></i></a>' +
                '<input type="hidden" name="idDetail[]" value="' + idDetail + '">'+
                ' <input type="hidden" name="kotaAsalDetail[]" value="' +kotaAsalDetail + '">' +
                ' <input type="hidden" name="kotaTujuanDetail[]" value="' +kotaTujuanDetail + '">' +
                ' <input type="hidden" name="perKgDetail[]" value="' +perKgDetail + '">' +
                ' <input type="hidden" name="perM3Detail[]" value="' +perM3Detail + '">' +
                ' <input type="hidden" name="keteranganDetail[]" value="' +keteranganDetail + '">' +
                '</td>';

                if (indexDetailForm != '') {
                    $('tr[data-index="' + indexDetailForm + '"]').html(htmlTr);
                    indexDetail++;
                } else {
                    $('#table-detail tbody').append(
                        '<tr data-index="' + indexDetail + '">' + htmlTr + '</tr>'
                        );
                    indexDetail++;
                }

                $('.edit-detail').on('click', editDetail);

                $('.delete-detail').on('click', function() {
                    $(this).parent().parent().remove();
                });

                $('#modal-add-detail').removeClass("md-show");
            });

$('.edit-detail').on('click', editDetail);

$('#clear-details').on('click', function() {
    $('#table-detail tbody').html('');
});
});

var clearFormDetail = function() {
    $('#indexDetailForm').val('');
    $('#idDetail').val('');
    $('#kotaAsalDetail').val('').change();
    $('#kotaTujuanDetail').val('').change();
    $('#perKgDetail').val('');
    $('#perM3Detail').val('');
    $('#keteranganDetail').val('');
};

var editDetail = function() {
    var indexDetailForm       = $(this).parent().parent().data('index');
    var idDetail              = $(this).parent().parent().find('[name="idDetail[]"]').val();
    var kotaAsalDetail        = $(this).parent().parent().find('[name="kotaAsalDetail[]"]').val();
    var kotaTujuanDetail      = $(this).parent().parent().find('[name="kotaTujuanDetail[]"]').val();
    var perKgDetail           = $(this).parent().parent().find('[name="perKgDetail[]"]').val();
    var perM3Detail           = $(this).parent().parent().find('[name="perM3Detail[]"]').val();
    var keteranganDetail      = $(this).parent().parent().find('[name="keteranganDetail[]"]').val();

    $('#indexDetailForm').val(indexDetailForm);
    $('#idDetail').val(idDetail);
    $('#kotaAsalDetail').val(kotaAsalDetail).change();
    $('#kotaTujuanDetail').val(kotaTujuanDetail).change();
    $('#perKgDetail').val(perKgDetail);
    $('#perM3Detail').val(perM3Detail);
    $('#keteranganDetail').val(keteranganDetail);

    $('#title-modal-detail').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-detail').html('{{ trans('shared/common.edit') }}');

    $('#modal-add-detail').addClass("md-show");
};
</script>
@endsection
