
@extends('layouts.master')

@section('title', trans('operational/menu.delivery-area-money-trip'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.delivery-area-money-trip') }}</h2>
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
                                <label for="deliveryArea" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="deliveryArea" id="deliveryArea">
                                        <option value="">ALL</option>
                                        @foreach($optionDeliveryArea as $deliveryArea)
                                        <option value="{{ $deliveryArea->delivery_area_name }}" {{ !empty($filters['deliveryArea']) && $filters['deliveryArea'] == $deliveryArea->delivery_area_name ? 'selected' : '' }}>{{ $deliveryArea->delivery_area_name }}</option>
                                        @endforeach
                                    </select>
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
                                        <a href="{{ URL($url.'/add') }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                        </a>
                                    </div>
                                </div>
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
                                    <th>{{ trans('operational/fields.delivery-area') }}</th>
                                    <th>{{ trans('operational/fields.vehicle-type') }}</th>
                                    <th>{{ trans('operational/fields.money-trip') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('shared/common.active') }}</th>
                                    <th width="60px">{{ trans('shared/common.action') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                                @foreach($models as $model)
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->delivery_area_name }}</td>
                                    <td>{{ $model->type_vehicle }}</td>
                                    <td class="text-right">{{ number_format($model->money_trip_rate,0,".",",") }}</td>
                                    <td>{{ $model->description }}</td>
                                    <td class="text-center">
                                        @if($model->active == 'Y')
                                        <i class="fa fa-check"></i>
                                        @else
                                        <i class="fa fa-remove"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('access', [$resource, 'update'])
                                        <a href="{{ URL($url . '/edit/' . $model->delivery_area_money_trip_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#deliveryArea').select2();
    });
</script>
@endsection
