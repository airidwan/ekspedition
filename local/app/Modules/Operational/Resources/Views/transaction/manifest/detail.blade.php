<?php
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
?>

@extends('layouts.master')

@section('title', trans('operational/menu.manifest'))

@section('header')
@parent
<style type="text/css">
#table-lov-po tbody tr{
    cursor: pointer;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.manifest') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->manifest_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabMoneyTrip" data-toggle="tab">{{ trans('operational/fields.money-trip') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="manifestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="manifestNumber" name="manifestNumber" value="{{ $model->manifest_number }}" disabled />
                                        </div>
                                    </div>
                                    <?php $manifestDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group">
                                        <label for="manifestDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="manifestDate" name="manifestDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $manifestDate->format('d-m-Y') }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="status" name="status" class="form-control" value="{{ $model->status }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $route = $model->route;
                                    $headerRouteCode = $route !== null ? $route->route_code : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="headerRouteCode" class="col-sm-4 control-label">{{ trans('operational/fields.rute') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="headerRouteCode" name="headerRouteCode" value="{{ $headerRouteCode }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $kotaAsal = $route !== null ? $route->cityStart()->first() : null;
                                    $namaKotaAsal = $kotaAsal !== null ? $kotaAsal->city_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="kotaAsal" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaAsal" name="kotaAsal" value="{{ $namaKotaAsal }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $kotaTujuan = $route !== null ? $route->cityEnd()->first() : null;
                                    $namaKotaTujuan = $kotaTujuan !== null ? $kotaTujuan->city_name : '';
                                    ?>
                                     <div class="form-group">
                                        <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaTujuan" name="kotaTujuan" value="{{ $namaKotaTujuan }}" disabled />
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" disabled />{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                    $truck = $model->truck;
                                    $truckNopol = $truck !== null ? $truck->police_number : '';
                                    $truckOwner = $truck !== null ? $truck->owner_name : '';
                                    $truckCategory = $truck !== null ? $truck->category : '';
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
                                    <?php
                                    $po       = $model->po;
                                    $poNumber = $po !== null ? $po->po_number : '';
                                    ?>
                                    <div id="showLovPo" class="form-group hidden">
                                        <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ $poNumber }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $driver = $model->driver;
                                    $driverName = $driver !== null ? $driver->driver_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $driverAssistant = $model->driverAssistant;
                                    $driverAssistantName = $driverAssistant !== null ? $driverAssistant->driver_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="driverAssistantName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverAssistantName" name="driverAssistantName" value="{{ $driverAssistantName }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="totalColyHeader" name="totalColyHeader" class="form-control currency" value="{{ $model->totalColy() }}" disabled />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive" style="overflow-x: hidden;">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                                    <th>{{ trans('operational/fields.route-code') }}</th>
                                                    <th>{{ trans('shared/common.customer') }}</th>
                                                    <th>{{ trans('operational/fields.receiver') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                                    <th>{{ trans('operational/fields.coly-sent') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->line as $line)
                                                    <?php
                                                    $resi = $line->resi;
                                                    $resiDate = $resi !== null ? new \DateTime($resi->created_date) : null;
                                                    $customer = $resi !== null ? $resi->customer : null;
                                                    $route = $resi !== null ? $resi->route : null;
                                                    ?>
                                                    <tr>
                                                        <td > {{ $resi !== null ? $resi->resi_number : '' }} </td>
                                                        <td > {{ $route !== null ? $route->route_code : '' }} </td>
                                                        <td > {{ !empty($resi->customerReceiver) ? $resi->customerReceiver->customer_name : '' }} </td>
                                                        <td > {{ !empty($resi) ? $resi->receiver_name : '' }} </td>
                                                        <td > {{ !empty($resi) ? $resi->getItemAndUnitNames() : '' }} </td>
                                                        <td class="text-right"> {{ number_format($resi !== null ? $resi->totalColy() : 0) }} </td>
                                                        <td class="text-right"> {{ number_format($line->coly_sent) }} </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabMoneyTrip">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="moneyTrip" class="col-sm-4 control-label">{{ trans('operational/fields.money-trip') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="moneyTrip" name="moneyTrip" value="{{ $model->money_trip }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="moneyTripNotes" class="col-sm-4 control-label">{{ trans('operational/fields.money-trip-notes') }} </label>
                                        <div class="col-sm-8">
                                            <textarea id="moneyTripNotes" name="moneyTripNotes" class="form-control" disabled>{{ $model->money_trip_note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                           <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(in_array($model->status, $model->canPrint()))
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->manifest_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('operational/fields.manifest') }}
                                </a>
                                @endif
                                @if(in_array($model->status, $model->canPrint()))
                                <a href="{{ URL($url.'/print-pdf-report/'.$model->manifest_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('operational/fields.report') }}
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
