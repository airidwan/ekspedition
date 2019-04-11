@extends('layouts.master')

@section('title', trans('asset/menu.service-asset'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> {{ trans('asset/menu.service-asset') }}</h2>
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
                                <label for="serviceNumber" class="col-sm-4 control-label">{{ trans('asset/fields.service-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="serviceNumber" name="serviceNumber" value="{{ !empty($filters['serviceNumber']) ? $filters['serviceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="assetNumber" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="assetNumber" name="assetNumber" value="{{ !empty($filters['assetNumber']) ? $filters['assetNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ !empty($filters['poNumber']) ? $filters['poNumber'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="item" class="col-sm-4 control-label">{{ trans('inventory/fields.item') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="item" name="item" value="{{ !empty($filters['item']) ? $filters['item'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="status">
                                        <option value="">ALL</option>
                                        <option value="true" {{ !empty($filters['status']) && $filters['status'] == 'true' ? 'selected' : '' }}>{{ trans('asset/fields.finished') }}</option>
                                        <option value="false" {{ !empty($filters['status']) && $filters['status'] == 'false' ? 'selected' : '' }}>{{ trans('asset/fields.not-finished') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px;">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('asset/fields.service-number') }}</th>
                                <th>{{ trans('asset/fields.asset-number') }}</th>
                                <th>{{ trans('purchasing/fields.po-number') }}</th>
                                <th>{{ trans('inventory/fields.item') }}</th>
                                <th>{{ trans('shared/common.category') }}</th>
                                <th>{{ trans('asset/fields.employee') }}</th>
                                <th>{{ trans('asset/fields.service-date') }}</th>
                                <th>{{ trans('asset/fields.finish-date') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <?php
                                 $serviceDate = !empty($model->service_date) ? new \DateTime($model->service_date) : null;
                                 $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->service_number }}</td>
                                <td>{{ $model->asset_number }}</td>
                                <td>{{ $model->po_number }}</td>
                                <td>{{ $model->item_description }}</td>
                                <td>{{ $model->category_name }}</td>
                                <td>{{ $model->employee_name }}</td>
                                <td>{{ !empty($serviceDate) ? $serviceDate->format('d M Y') : '' }}</td>
                                <td>{{ !empty($finishDate) ? $finishDate->format('d M Y') : '' }}</td>
                                <td>{{ $model->note }}</td>
                                <td class="text-center">
                                @if($model->finished == TRUE)
                                <i class="fa fa-check"></i>
                                @else
                                <i class="fa fa-remove"></i>
                                @endif
                                </td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->service_asset_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
