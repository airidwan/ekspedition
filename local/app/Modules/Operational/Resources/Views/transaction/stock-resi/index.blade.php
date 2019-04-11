<?php use App\Modules\Operational\Model\Transaction\ResiStock; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.stock-resi'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.stock-resi') }}</h2>
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
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="route" class="col-sm-4 control-label">{{ trans('operational/fields.route') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="route" name="route[]" multiple="multiple">
                                        <?php $routeIds = !empty($filters['route']) ? $filters['route'] : []; ?>
                                        @foreach($optionsRoute as $route)
                                            <option value="{{ $route->route_id }}" {{ in_array($route->route_id, $routeIds) ? 'selected' : '' }}>
                                                {{ $route->route_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="nama" class="col-sm-4 control-label">{{ trans('operational/fields.region') }} </label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="region[]" id="region" multiple="multiple">
                                        <?php $regionIds = !empty($filters['region']) ? $filters['region'] : []; ?>
                                        @foreach($optionsRegion as $region)
                                        <option value="{{ $region->region_id }}" {{ in_array($region->region_id, $regionIds) ? 'selected' : '' }}>{{ $region->region_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-pdf-checklist') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print-checklist') }}
                                </a>
                                <a href="{{ URL($url.'/print-excel-checklist') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel-checklist') }}
                                </a>
                                <a href="{{ URL($url.'/print-pdf-report') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print-report') }}
                                </a>
                                <a href="{{ URL($url.'/print-excel-report') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel-report') }}
                                </a>
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>
                                    {{ trans('operational/fields.resi-number') }}<hr/>
                                    {{ trans('operational/fields.date') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.route') }}<hr/>
                                    {{ trans('operational/fields.region') }}
                                </th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>
                                    {{ trans('shared/common.customer') }}<hr/>
                                    {{ trans('operational/fields.sender') }}
                                </th>
                                <th>
                                    {{ trans('shared/common.customer') }}<hr/>
                                    {{ trans('operational/fields.receiver') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.total-coly') }}<hr/>
                                    {{ trans('operational/fields.coly-wh') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.total-weight') }}<hr/>
                                    {{ trans('operational/fields.total-volume') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.total-unit') }}
                                </th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('accountreceivables/fields.remaining') }}</th>
                                <th>{{ trans('accountreceivables/fields.bill') }}</th>
                                <th>{{ trans('operational/fields.official-report') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($paginate->currentPage() - 1) * $paginate->perPage() + 1; ?>
                             @foreach($models as $model)
                             <?php
                             $modelStock = ResiStock::find($model->stock_resi_id);
                             $resiDate   = new \DateTime($model->created_date);
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->resi_number }}<hr/>
                                    {{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}
                                </td>
                                <td>
                                    {{ $model->route_code }}<hr/>
                                    {{ $model->region_name }}
                                </td>
                                <td>{{ $modelStock->resi !== null ? $modelStock->resi->itemName() : '' }}</td>
                                <td>
                                    {{ !empty($modelStock->resi->customer) ? $modelStock->resi->customer->customer_name : '' }}<hr/>
                                    {{ $model->sender_name }}
                                </td>
                                <td>
                                    {{ !empty($modelStock->resi->customerReceiver) ? $modelStock->resi->customerReceiver->customer_name : '' }}<hr/>
                                    {{ $model->receiver_name }}
                                </td>
                                <td class="text-right">
                                    {{ $modelStock->resi !== null ? $modelStock->resi->totalColy() : '' }}<hr/>
                                    {{ $model->coly_wh }}
                                </td>
                                <td class="text-right">
                                    {{ $modelStock->resi !== null ? number_format($modelStock->resi->totalWeight(), 2) : '' }}<hr/>
                                    {{ $modelStock->resi !== null ? number_format($modelStock->resi->totalVolume(), 2) : '' }}
                                </td>
                                </td>
                                <td class="text-right">
                                    @foreach($modelStock->resi->lineUnit as $index => $lineUnit)
                                        {{ $lineUnit->coly }} {{ $lineUnit->item_name }} {{ $index == $modelStock->resi->lineUnit->count() -1 ? '' : '<br/>' }}
                                    @endforeach
                                </td>
                                <td>{{ !empty($modelStock->resi) ? $modelStock->resi->wdl_note : '' }}</td>
                                <td class="text-right">{{ !empty($modelStock->resi) ? number_format($modelStock->resi->totalRemainingInvoice()) : 0 }}</td>
                                <td class="text-center">
                                    <i class="fa {{ !empty($modelStock->resi) && $modelStock->resi->isTagihan() ? 'fa-check' : 'fa-remove' }}"></i>
                                </td>
                                <td >
                                @if(!empty($model->official_report))
                                    @foreach($model->official_report as $modelOr)
                                    
                                    <a href="{{ URL($urlBa . '/edit/' . $modelOr->official_report_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}" style="margin-bottom: 2px;">
                                        <i class="fa fa-pencil"></i> {{ $modelOr->official_report_number }} 
                                    </a>
                                    <br>
                                    @endforeach
                                @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $paginate->render() !!}
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
    $("#route").select2();
    $("#region").select2();
    $('#category').select2();
    $('#warehouse').select2();
});
</script>
@endsection
