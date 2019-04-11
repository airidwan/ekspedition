<?php 
    use App\Service\Penomoran; 
    use App\Modules\Inventory\Model\Master\MasterStock;
    use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
?>

@extends('layouts.master')

@section('title', trans('inventory/menu.move-order'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.move-order') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->mo_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="moNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.mo-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="moNumber" name="moNumber" value="{{ $model->mo_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $date = !empty(old('date')) ? new \DateTime(old('date')) : new \DateTime();
                                    } else {
                                        $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
                                    }
                                    ?>
                                    <div class="form-group">
                                        <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                    <?php $typeString = $model->type ?>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" disabled>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}" {{ $typeString == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="4" id="description" name="description" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelService  = $model->service;
                                        $serviceId     = !empty($modelService) ? $modelService->service_asset_id : '' ; 
                                        $serviceNumber = !empty($modelService) ? $modelService->service_number : '' ; 
                                    ?>
                                    <div id="formServiceNumber" class="form-group">
                                        <label for="serviceNumber" class="col-sm-4 control-label">{{ trans('asset/fields.service-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="serviceNumber" name="serviceNumber" value="{{ $serviceNumber }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                        $modelTruck = $model->truck;
                                        $truckId    = !empty($modelTruck) ? $modelTruck->truck_id : '' ; 
                                        $truckCode  = !empty($modelTruck) ? $modelTruck->truck_code : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="truckCode" class="col-sm-4 control-label">{{ trans('operational/fields.truck') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckCode" name="truckCode" value="{{ $truckCode }}" disabled>
                                        </div>
                                    </div> 
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverId    = !empty($modelDriver) ? $modelDriver->driver_id : '' ; 
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('inventory/fields.item-code') }}</th>
                                                    <th>{{ trans('inventory/fields.item-name') }}</th>
                                                    <th>{{ trans('inventory/fields.warehouse') }}</th>
                                                    <th>{{ trans('inventory/fields.qty-need') }}</th>
                                                    <th>{{ trans('inventory/fields.uom') }}</th>
                                                    <th>{{ trans('general-ledger/fields.coa') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $item    = $line->item;
                                                    $uom     = $item->uom;
                                                    $wh      = $line->warehouse;
                                                    $driver  = $line->driver;
                                                    $truck   = $line->truck;
                                                    $coaComb = $line->coaCombination;
                                                    $coa     = !empty($coaComb) ? $coaComb->account : null;
                                                    $stock   = MasterStock::where('item_id', '=', $item->item_id)->where('wh_id', '=', $wh->wh_id)->first();
                                                ?>
                                                <tr>
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $wh !== null ? $wh->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ $line->qty_need }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td class="text-right"> {{ $coa !== null ? $coa->coa_code : '' }} </td>
                                                    <td > {{ $line->description }} </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>

                                @if($model->status == MoveOrderHeader::COMPLETE)
                                <a href="{{ URL($url.'/print-pdf/'.$model->mo_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent()
<script type="text/javascript">
$(document).on('ready', function(){
    if ($('#type').val() == '{{ MoveOrderHeader::SERVICE }}') {
        $('#formServiceNumber').removeClass('hidden');
    }else{
        $('#formServiceNumber').addClass('hidden');
    }
});
</script>
@endsection
