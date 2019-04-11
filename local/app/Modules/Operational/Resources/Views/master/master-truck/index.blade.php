@extends('layouts.master')

@section('title', trans('operational/menu.truck'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.truck') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="code" class="col-sm-4 control-label">{{ trans('shared/common.code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code" value="{{ !empty($filters['code']) ? $filters['code'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ownerName" class="col-sm-4 control-label">{{ trans('operational/fields.nama-pemilik') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="ownerName" name="ownerName" value="{{ !empty($filters['ownerName']) ? $filters['ownerName'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="category" name="category">
                                        <?php $stringCategory = !empty($filters['category']) ? $filters['category'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionCategory as $category)
                                        <option value="{{ $category->lookup_code }}" {{ $category->lookup_code == $stringCategory ? 'selected' : '' }}>{{ $category->meaning }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
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
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info">
                                    <i class="fa fa-search"></i> {{ trans('shared/common.filter') }}
                                </button>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL('operational/master/master-truck/add') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('shared/common.code') }}</th>
                                    <th>
                                        {{ trans('operational/fields.nama-pemilik') }}<hr/>
                                        {{ trans('operational/fields.price-per-unit') }}
                                    </th>
                                    <th>
                                        {{ trans('operational/fields.police-number') }}<hr/>
                                        {{ trans('operational/fields.brand') }}</th>
                                    <th>
                                        {{ trans('operational/fields.type') }}<hr/>
                                        {{ trans('shared/common.category') }}</th>
                                    <th>
                                        {{ trans('operational/fields.nomor-rangka') }}<hr/>
                                        {{ trans('operational/fields.nomor-mesin') }}
                                    </th>
                                    <th>
                                        {{ trans('operational/fields.tahun-buat') }}<hr/>
                                        {{ trans('operational/fields.dimensi-bak-plt') }}
                                    </th>
                                    <th>
                                        {{ trans('operational/fields.tanggal-stnk') }}<hr/>
                                        {{ trans('operational/fields.tanggal-kir') }}
                                    </th>
                                    <th>
                                        {{ trans('operational/fields.berat-max') }}<hr/>
                                        {{ trans('operational/fields.ground-clearance') }}
                                    </th>
                                    <th>{{ trans('shared/common.keterangan') }}</th>
                                    <th>{{ trans('operational/fields.sub-account') }}</th>
                                    <th>{{ trans('shared/common.active') }}</th>
                                    <th style="min-width:60px" >{{ trans('shared/common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                                @foreach($models as $model)
                                 <?php
                                     $stnkDate = !empty($model->stnk_date) ? new \DateTime($model->stnk_date) : null;
                                     $kirDate = !empty($model->kir_date) ? new \DateTime($model->kir_date) : null;
                                 ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->truck_code }}</td>
                                    <td>
                                        {{ $model->owner_name }}<hr/>
                                        {{ number_format($model->truck_price)  }}</td>
                                    <td>
                                        {{ $model->police_number }}<hr/>
                                        {{ $model->vehicle_merk }}</td>
                                    <td>
                                        {{ $model->vehicle_type }}<hr/>
                                        {{ $model->vehicle_category }}
                                    </td>
                                    <td>
                                        {{ $model->chassis_number }}<hr/>
                                        {{ $model->machine_number }}
                                    </td>
                                    <td class="text-right">
                                        {{ $model->production_year }}<hr/>
                                        {{ number_format($model->long_tube, 2) }} x {{ number_format($model->width_tube, 2) }} x {{ number_format($model->height_tube, 2) }} m
                                    </td>
                                    <td>
                                        {{ !empty($stnkDate) ? $stnkDate->format('d-M-Y') : '' }}<hr/>
                                        {{ !empty($kirDate) ? $kirDate->format('d-M-Y') : '' }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($model->weight_max) }}<hr/>
                                        {{ number_format($model->ground_clearance, 2) }}
                                    </td>
                                    <td>{{ $model->description }}</td>
                                    <td>{{ $model->subaccount_code }}</td>
                                    <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                    <td class="text-center">
                                        @can('access', [$resource, 'update'])
                                        <a href="{{ URL($url . '/edit/' . $model->truck_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
