<?php use App\Modules\Operational\Model\Transaction\ResiStock; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.resi-stock'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.resi-stock') }}</h2>
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
                                        <option value="{{ $route->route_id }}" {{ in_array($route->route_id, $routeIds) ? 'selected' : '' }}>{{ $route->route_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="region" class="col-sm-4 control-label">{{ trans('operational/fields.region') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="region" name="region[]" multiple="multiple">
                                        <?php $regionIds = !empty($filters['region']) ? $filters['region'] : []; ?>
                                        @foreach($optionsRegion as $region)
                                        <option value="{{ $region->region_id }}" {{ in_array($region->region_id, $regionIds) ? 'selected' : '' }}>{{ $region->region_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="deliveryArea" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="deliveryArea" name="deliveryArea[]" multiple="multiple">
                                        <?php $deliveryAreaIds = !empty($filters['deliveryArea']) ? $filters['deliveryArea'] : []; ?>
                                        @foreach($optionArea as $deliveryArea)
                                        <option value="{{ $deliveryArea->delivery_area_id }}" {{ in_array($deliveryArea->delivery_area_id, $deliveryAreaIds) ? 'selected' : '' }}>{{ $deliveryArea->delivery_area_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('operational/fields.is-ready') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="">ALL</option>
                                        <option value="FALSE" {{ !empty($filters['status']) && $filters['status'] == FALSE ? 'selected' : '' }}>Not Ready</option>
                                        <option value="TRUE" {{ !empty($filters['status']) && $filters['status'] == TRUE ? 'selected' : '' }}>Ready to Delivery</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-pdf') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
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
                                    {{ trans('operational/fields.date') }}<hr/>
                                    {{ trans('operational/fields.payment') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.route') }}<hr/>
                                    {{ trans('operational/fields.region') }}
                                </th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>
                                    {{ trans('shared/common.customer') }}<hr/>
                                    {{ trans('operational/fields.receiver') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.address') }}<hr/>
                                    {{ trans('shared/common.telepon') }}
                                </th>
                                <th width="110px">
                                    {{ trans('operational/fields.total-coly') }}<hr/>
                                    {{ trans('operational/fields.coly-wh') }}
                                </th>
                                <th width="100px">
                                    {{ trans('operational/fields.total-weight') }}<hr/>
                                    {{ trans('operational/fields.total-volume') }}<hr/>
                                    {{ trans('operational/fields.total-unit') }}
                                </th>
                                <th>{{ trans('operational/fields.delivery-area') }}</th>
                                <th>{{ trans('operational/fields.is-ready') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('accountreceivables/fields.remaining') }}</th>
                                <th>{{ trans('accountreceivables/fields.bill') }}</th>
                                <th>{{ trans('operational/fields.official-report') }}</th>
                                <th width="50px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($paginate->currentPage() - 1) * $paginate->perPage() + 1; ?>
                             @foreach($models as $modela)
                             <?php
                             $model = ResiStock::find($modela->stock_resi_id);
                             $resi  = $model->resi;
                             $area  = !empty($resi) ? $resi->deliveryArea : null;
                             $resiDate = $model->resi !== null ? new \DateTime($model->resi->created_date) : null;
                             $warehouse = $model->warehouse;
                             $route = $model->resi !== null ? $model->resi->route : null;
                             $endCity = $route !== null ? $route->cityEnd : null;
                             $region = $endCity !== null ? $endCity->region() : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->resi !== null ? $model->resi->resi_number : '' }}<hr/>
                                    {{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}<hr/>
                                    {{ $model->resi->getSingkatanPayment() }}
                                </td>
                                <td>
                                    {{ $route !== null ? $route->route_code : '' }}<hr/>
                                    {{ $region !== null ? $region->region_name : '' }}
                                </td>
                                <td>{{ $model->resi !== null ? $model->resi->itemName() : '' }}</td>
                                <td>
                                    {{ !empty($model->resi->customerReceiver) ? $model->resi->customerReceiver->customer_name : '' }}<hr/>
                                    {{ $model->resi->receiver_name }}
                                </td>
                                <td>
                                    {{ $model->resi !== null ? $resi->receiver_address : '' }}<hr/>
                                    {{ $model->resi !== null ? $resi->receiver_phone : '' }}
                                </td>
                                <td class="text-right">
                                    {{ $model->resi !== null ? $model->resi->totalColy() : '' }}<hr/>
                                    {{ $model->coly }}
                                </td>
                                <td class="text-right">
                                    {{ $model->resi !== null ? number_format($model->resi->totalWeight(), 2) : '' }}<hr/>
                                    {{ $model->resi !== null ? number_format($model->resi->totalVolume(), 2) : '' }}<hr/>
                                    @foreach($model->resi->lineUnit as $index => $lineUnit)
                                        {{ $lineUnit->coly }} {{ $lineUnit->item_name }} {{ $index == $model->resi->lineUnit->count() -1 ? '' : '<br/>' }}
                                    @endforeach
                                </td>
                                <td >{{  $area !== null ? $area->delivery_area_name : '' }}</td>
                                <td class="text-center">
                                    @if($model->is_ready_delivery)
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                <td>{{ $model->resi !== null ? $resi->wdl_note : '' }}</td>
                                <td class="text-right">{{ !empty($model->resi) ? number_format($model->resi->totalRemainingInvoice()) : 0 }}</td>
                                <td class="text-center">
                                    <i class="fa {{ !empty($model->resi) && $model->resi->isTagihan() ? 'fa-check' : 'fa-remove' }}"></i>
                                </td>
                                <td >
                                @if(!empty($modela->official_report))
                                    @foreach($modela->official_report as $modelOr)
                                    
                                    <a href="{{ URL($urlBa . '/edit/' . $modelOr->official_report_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}" style="margin-bottom: 2px;">
                                        <i class="fa fa-pencil"></i> {{ $modelOr->official_report_number }} 
                                    </a>
                                    <br>
                                    @endforeach
                                @endif
                                </td>
                                <td class="text-center">
                                @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->stock_resi_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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
                    {!! $paginate->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div class="md-modal md-3d-flip-horizontal" id="modal-delete">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.delete') }}</strong> {{ trans('operational/menu.resi-stock') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="delete-text">Are you sure want to delete ?</h4>
                    <form role="form" method="post" action="{{ URL($url . '/delete') }}" class="text-right">
                        {{ csrf_field() }}
                        <input type="hidden" id="delete-id" name="id" >
                        <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                        <button type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
                    </form>
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
    $('#deliveryArea').select2();
});
</script>
@endsection
