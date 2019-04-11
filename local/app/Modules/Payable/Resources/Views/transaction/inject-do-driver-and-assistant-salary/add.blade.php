<?php
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
?>

@extends('layouts.master')

@section('title', trans('payable/menu.inject-do-driver-and-assistant-salary'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.inject-do-driver-and-assistant-salary') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->delivery_order_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabDriverSalary" data-toggle="tab">{{ trans('payable/fields.driver-and-assistant-salary') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li>
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabDriverSalary">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="driver" name="driver" value="{{ !empty($model->driver) ? $model->driver->driver_code.' - '.$model->driver->driver_name : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="driverPosition" class="col-sm-4 control-label">{{ trans('operational/fields.position') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="driverPosition" name="driverPosition" value="{{ !empty($model->driver) ? $model->driver->position : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="driverType" class="col-sm-4 control-label">{{ trans('operational/fields.type') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="driverType" name="driverType" value="{{ !empty($model->driver) ? $model->driver->type : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="driverSalary" class="col-sm-4 control-label">{{ trans('payable/fields.driver-salary') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="driverSalary" name="driverSalary" value="{{ count($errors) > 0 ? str_replace(',', '', old('driverSalary')) : $model->driver_salary }}" {{ empty($model->driver) || !$model->driver->isTripEmployee() ? 'readonly' : '' }}/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="driverAssistant" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="driverAssistant" name="driverAssistant" value="{{ !empty($model->assistant) ? $model->assistant->driver_code.' - '.$model->assistant->driver_name : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="driverAssistantPosition" class="col-sm-4 control-label">{{ trans('operational/fields.position') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="driverAssistantPosition" name="driverAssistantPosition" value="{{ !empty($model->assistant) ? $model->assistant->position : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="driverAssistantType" class="col-sm-4 control-label">{{ trans('operational/fields.type') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="driverAssistantType" name="driverAssistantType" value="{{ !empty($model->assistant) ? $model->assistant->type : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="driverAssistantSalary" class="col-sm-4 control-label">{{ trans('payable/fields.driver-assistant-salary') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="driverAssistantSalary" name="driverAssistantSalary" value="{{ count($errors) > 0 ? str_replace(',', '', old('driverAssistantSalary')) : $model->driver_assistant_salary }}" {{ empty($model->assistant) || !$model->assistant->isTripEmployee() ? 'readonly' : '' }}/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="deliveryOrderNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ $model->delivery_order_number }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="deliveryOrderDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $deliveryOrderDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="deliveryOrderDate" name="deliveryOrderDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $deliveryOrderDate->format('d-m-Y') }}" disabled/>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $deliveryArea = $model->deliveryArea;
                                    $deliveryAreaName = $deliveryArea !== null ? $deliveryArea->delivery_area_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="deliveryAreaName" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="deliveryAreaName" name="deliveryAreaName" value="{{ $deliveryAreaName }}" disabled />
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" disabled>{{ $model->note }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="status" name="status" class="form-control" value="{{ $model->status }}" disabled />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                    $truck = $model->truck;
                                    $truckNopol = $truck !== null ? $truck->police_number : '';
                                    $truckOwner = $truck !== null ? $truck->owner_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="truck" class="col-sm-4 control-label">{{ trans('operational/fields.truck') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckNopol" name="truckNopol" value="{{ $truckNopol }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="truckOwner" class="col-sm-4 control-label">{{ trans('operational/fields.truck-owner') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckOwner" name="truckOwner" value="{{ $truckOwner }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="truckType" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckType" name="truckType" value="{{ !empty($model->truck) ? $model->truck->type : '' }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="truckCategory" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckCategory" name="truckCategory" value="{{ !empty($model->truck) ? $model->truck->getCategory() : '' }}" disabled />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                                    <th>{{ trans('operational/fields.resi-date') }}</th>
                                                    <th>{{ trans('operational/fields.customer') }}</th>
                                                    <th>{{ trans('operational/fields.route-code') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                                    <th>{{ trans('operational/fields.coly-sent') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines as $line)
                                                <?php
                                                $resiDate = $line->resi !== null ? new \DateTime($line->resi->created_date) : null;
                                                $customer = $line->resi !== null ? $line->resi->customer : null;
                                                $route = $line->resi !== null ? $line->resi->route : null;
                                                $itemName = '';
                                                if ($line->resi !== null) {
                                                    $itemName = !empty($line->resi->itemName()) && !empty($line->resi->itemUnit()) ? $line->resi->itemName().', '.$line->resi->itemUnit() : $line->resi->itemName().''.$line->resi->itemUnit();
                                                }
                                                ?>
                                                <tr>
                                                    <td > {{ $line->resi !== null ? $line->resi->resi_number : '' }} </td>
                                                    <td > {{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }} </td>
                                                    <td > {{ $customer !== null ? $customer->customer_name : '' }} </td>
                                                    <td > {{ $route !== null ? $route->route_code : '' }} </td>
                                                    <td > {{ $itemName }} </td>
                                                    <td class="text-right"> {{ number_format($line->resi !== null ? $line->resi->totalColy() : 0) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_coly) }} </td>
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
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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

@section('modal')
@parent
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function() {
});
</script>
@endsection