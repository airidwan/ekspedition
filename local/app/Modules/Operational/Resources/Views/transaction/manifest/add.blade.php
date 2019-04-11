<?php
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;
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
                                    <div class="form-group">
                                        <label for="manifestDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $manifestDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="manifestDate" name="manifestDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $manifestDate->format('d-m-Y') }}" disabled />
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        @if($errors->has('manifestDate'))
                                        <span class="help-block">{{ $errors->first('manifestDate') }}</span>
                                        @endif
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
                                    <div class="form-group {{ $errors->has('routeId') ? 'has-error' : '' }}">
                                        <label for="headerRouteCode" class="col-sm-4 control-label">{{ trans('operational/fields.rute') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="routeId" name="routeId" value="{{ count($errors) > 0 ? old('routeId') : $model->route_id }}">
                                                <input type="text" class="form-control" id="headerRouteCode" name="headerRouteCode" value="{{ count($errors) > 0 ? old('headerRouteCode') : $headerRouteCode }}" readonly />
                                                <span class="btn input-group-addon" data-toggle="{{ $model->status == ManifestHeader::OPEN ? 'modal' : '' }}" data-target="#modal-lov-route"><i class="fa fa-search"></i></span>

                                            </div>
                                            @if($errors->has('routeId'))
                                            <span class="help-block">{{ $errors->first('routeId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $kotaAsal = $route !== null ? $route->cityStart()->first() : null;
                                    $namaKotaAsal = $kotaAsal !== null ? $kotaAsal->city_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="kotaAsal" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaAsal" name="kotaAsal" value="{{ count($errors) > 0 ? old('kotaAsal') : $namaKotaAsal }}" readonly />
                                        </div>
                                    </div>
                                    <?php
                                    $kotaTujuan = $route !== null ? $route->cityEnd()->first() : null;
                                    $namaKotaTujuan = $kotaTujuan !== null ? $kotaTujuan->city_name : '';
                                    ?>
                                     <div class="form-group">
                                        <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaTujuan" name="kotaTujuan" value="{{ count($errors) > 0 ? old('kotaTujuan') : $namaKotaTujuan }}" readonly />
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" {{ $model->status == ManifestHeader::OPEN || $model->status == ManifestHeader::REQUEST_APPROVE || $model->status == ManifestHeader::APPROVED ? '' : 'readonly' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
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
                                    <div class="form-group {{ $errors->has('truckId') ? 'has-error' : '' }}">
                                        <label for="truck" class="col-sm-4 control-label">{{ trans('operational/fields.truck') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}" readonly />
                                                <input type="hidden" id="truckCategory" name="truckCategory" value="{{ count($errors) > 0 ? old('truckCategory') : $truckCategory }}" readonly />
                                                <input type="text" class="form-control" id="truckNopol" name="truckNopol" value="{{ count($errors) > 0 ? old('truckNopol') : $truckNopol }}" readonly />
                                                <span class="btn input-group-addon" data-toggle="{{ $model->isOpen() ? 'modal' : '' }}" data-target="#modal-lov-truck"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('truckId'))
                                            <span class="help-block">{{ $errors->first('truckId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="truckOwner" class="col-sm-4 control-label">{{ trans('operational/fields.truck-owner') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckOwner" name="truckOwner" value="{{ count($errors) > 0 ? old('truckOwner') : $truckOwner }}" readonly />
                                        </div>
                                    </div>
                                    <?php
                                    $po       = $model->po;
                                    $poNumber = $po !== null ? $po->po_number : '';
                                    ?>
                                    <div id="showLovPo" class="form-group hidden {{ $errors->has('poHeaderId') ? 'has-error' : '' }}">
                                        <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }} </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="poHeaderId" id="poHeaderId" value="{{ count($errors) > 0 ? old('poHeaderId') : $model->po_header_id }}">
                                                <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ count($errors) > 0 ? old('poNumber') : $poNumber }}" readonly>
                                                <span class="btn input-group-addon {{ $model->isOpen() ? 'remove-po' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" id="{{ $model->isOpen() ? 'modalPo' : '' }}" ><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('poHeaderId'))
                                                <span class="help-block">{{ $errors->first('poHeaderId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $driver = $model->driver;
                                    $driverName = $driver !== null ? $driver->driver_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                        <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}" readonly />
                                                <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly />
                                                <span class="btn input-group-addon {{ $model->isOpen() ? 'show-modal-driver' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverId'))
                                            <span class="help-block">{{ $errors->first('driverId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $driverAssistant = $model->driverAssistant;
                                    $driverAssistantName = $driverAssistant !== null ? $driverAssistant->driver_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('driverAssistantId') ? 'has-error' : '' }}">
                                        <label for="driverAssistantName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }} </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="driverAssistantId" name="driverAssistantId" value="{{ count($errors) > 0 ? old('driverAssistantId') : $model->driver_assistant_id }}" readonly />
                                                <input type="text" class="form-control" id="driverAssistantName" name="driverAssistantName" value="{{ count($errors) > 0 ? old('driverAssistantName') : $driverAssistantName }}" readonly />
                                                <span class="btn input-group-addon {{ $model->isOpen() ? 'remove-driver-assistant' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon {{ $model->isOpen() ? 'show-modal-driver-assistant' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverAssistantId'))
                                            <span class="help-block">{{ $errors->first('driverAssistantId') }}</span>
                                            @endif
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
                                <div class="col-sm-6 portlets {{ !($model->isOpen() || $model->isRequestApprove() || $model->isApproved()) ? 'hidden' : '' }}">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" id="resiId" name="resiId" value="" />
                                            <input type="hidden" id="customerReceiver" name="customerReceiver" value="" />
                                            <input type="hidden" id="receiver" name="receiver" value="" />
                                            <input type="hidden" id="itemName" name="itemName" value="" />
                                            <input type="text" id="resiNumber" name="resiNumber" class="form-control" value="" {{ $model->isClosedWarning() || $model->isClosed() ? 'disabled' : '' }}/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.rute') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="routeCode" name="routeCode" class="form-control" value="" disabled/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets {{ !($model->isOpen() || $model->isRequestApprove() || $model->isApproved()) ? 'hidden' : '' }}">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="totalColy" name="totalColy" class="form-control" value="" disabled/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.coly-sent') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="colySent" name="colySent" class="form-control" value="" {{ $model->isClosedWarning() || $model->isClosed() ? 'disabled' : '' }}/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 portlets">
                                     @if ($model->isOpen() || $model->isRequestApprove() || $model->isApproved())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line">
                                                        <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr/>
                                    @endif
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

                                                    @if ($model->isOpen() || $model->isRequestApprove() || $model->isApproved())
                                                    <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <?php $dataIndexLine = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index-line="{{ $dataIndexLine }}">
                                                    <td > {{ old('resiNumber')[$i] }} </td>
                                                    <td > {{ old('routeCode')[$i] }} </td>
                                                    <td > {{ old('customerReceiver')[$i] }} </td>
                                                    <td > {{ old('receiver')[$i] }} </td>
                                                    <td > {{ old('itemName')[$i] }} </td>
                                                    <td class="text-right"> {{ old('totalColy')[$i] }} </td>
                                                    <td class="text-right"> {{ old('colySent')[$i] }}</td>

                                                    @if ($model->isOpen() || $model->isRequestApprove() || $model->isApproved())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="resiId[]" value="{{ old('resiId')[$i] }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="routeCode[]" value="{{ old('routeCode')[$i] }}">
                                                        <input type="hidden" name="customerReceiver[]" value="{{ old('customerReceiver')[$i] }}">
                                                        <input type="hidden" name="receiver[]" value="{{ old('receiver')[$i] }}">
                                                        <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                        <input type="hidden" name="totalColy[]" value="{{ old('totalColy')[$i] }}">
                                                        <input type="hidden" name="colySent[]" value="{{ old('colySent')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndexLine++; ?>
                                                @endfor

                                                @else
                                                <?php
                                                $lines = \DB::table('op.trans_manifest_line')
                                                                ->select('trans_manifest_line.*')
                                                                ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_manifest_line.resi_header_id')
                                                                ->where('trans_manifest_line.manifest_header_id', '=', $model->manifest_header_id)
                                                                ->orderBy('trans_resi_header.resi_number', 'asc')
                                                                ->get()
                                                ?>
                                                @foreach($lines as $line)
                                                <?php
                                                $line = ManifestLine::find($line->manifest_line_id);
                                                $resi = $line->resi;
                                                $resiDate = $resi !== null ? new \DateTime($resi->created_date) : null;
                                                $customer = $resi !== null ? $resi->customer : null;
                                                $route = $resi !== null ? $resi->route : null;
                                                ?>
                                                <tr data-index-line="{{ $dataIndexLine }}">
                                                    <td > {{ $resi !== null ? $resi->resi_number : '' }} </td>
                                                    <td > {{ $route !== null ? $route->route_code : '' }} </td>
                                                    <td > {{ !empty($resi->customerReceiver) ? $resi->customerReceiver->customer_name : '' }} </td>
                                                    <td > {{ !empty($resi) ? $resi->receiver_name : '' }} </td>
                                                    <td > {{ !empty($resi) ? $resi->getItemAndUnitNames() : '' }} </td>
                                                    <td class="text-right"> {{ number_format($resi !== null ? $resi->totalColy() : 0) }} </td>
                                                    <td class="text-right"> {{ number_format($line->coly_sent) }} </td>

                                                    @if ($model->isOpen() || $model->isRequestApprove() || $model->isApproved())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ $line->manifest_line_id }}">
                                                        <input type="hidden" name="resiId[]" value="{{ $line->resi_header_id }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ $resi !== null ? $resi->resi_number : '' }}">
                                                        <input type="hidden" name="routeCode[]" value="{{ $route !== null ? $route->route_code : '' }}">
                                                        <input type="hidden" name="customerReceiver[]" value="{{ !empty($resi->customerReceiver) ? $resi->customerReceiver->customer_name : '' }}">
                                                        <input type="hidden" name="receiver[]" value="{{ !empty($resi) ? $resi->receiver_name : '' }}">
                                                        <input type="hidden" name="itemName[]" value="{{ !empty($resi) ? $resi->getItemAndUnitNames() : '' }}">
                                                        <input type="hidden" name="totalColy[]" value="{{ number_format($resi !== null ? $resi->totalColy() : 0) }}">
                                                        <input type="hidden" name="colySent[]" value="{{ number_format($line->coly_sent) }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndexLine++; ?>

                                                @endforeach
                                                @endif
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
                                @if ($model->isOpen() || $model->isRequestApprove() || $model->isApproved())
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif

                                @if ($model->isOpen())
                                <button type="submit" name="btn-request-approve" class="btn btn-sm btn-info"><i class="fa fa-share"></i> {{ trans('shared/common.request-approve') }}</button>
                                @endif
                                @if(in_array($model->status, $model->canPrint()))
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->manifest_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('operational/fields.manifest') }} A
                                </a>
                                @endif
                                @if(in_array($model->status, $model->canPrint()))
                                <a href="{{ URL($url.'/print-pdf-detail-b/'.$model->manifest_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('operational/fields.manifest') }} B
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

@section('modal')
@parent
<div id="modal-lov-route" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.rute') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-route" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.kode-rute') }}</th>
                            <th>{{ trans('operational/fields.kota-asal') }}</th>
                            <th>{{ trans('operational/fields.kota-tujuan') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionRoute as $rute)
                        <tr style="cursor: pointer;" data-rute="{{ json_encode($rute) }}">
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

<div id="modal-lov-truck" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.truck') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-truck" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('operational/fields.brand') }}</th>
                                <th>{{ trans('operational/fields.type') }}</th>
                                <th>{{ trans('operational/fields.police-number') }}</th>
                                <th>{{ trans('operational/fields.owner-name') }}</th>
                                <th>{{ trans('shared/common.category') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($optionTruck as $truck)
                            <tr style="cursor: pointer;" data-truck="{{ json_encode($truck) }}">
                                <td>{{ $truck->truck_brand }}</td>
                                <td>{{ $truck->truck_type }}</td>
                                <td>{{ $truck->police_number }}</td>
                                <td>{{ $truck->owner_name }}</td>
                                <td>{{ $truck->truck_category }}</td>
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

<div id="modal-lov-driver" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">
                    <strong>{{ trans('operational/fields.driver') }}</strong>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="datatables-lov-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.code') }}</th>
                                    <th>{{ trans('shared/common.name') }}</th>
                                    <th>{{ trans('operational/fields.nickname') }}</th>
                                    <th>{{ trans('operational/fields.position') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.phone') }}</th>
                                </tr>
                            </thead>
                                @foreach ($optionDriver as $driver)
                                <tr style="cursor: pointer;" data-driver="{{ json_encode($driver) }}">
                                    <td>{{ $driver->driver_code }}</td>
                                    <td>{{ $driver->driver_name }}</td>
                                    <td>{{ $driver->driver_nickname }}</td>
                                    <td>{{ $driver->position }}</td>
                                    <td>{{ $driver->type }}</td>
                                    <td>{{ $driver->phone_number }}</td>
                                </tr>
                                @endforeach
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-lov-driver-assistant" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">
                    <strong>{{ trans('operational/fields.driver-assistant') }}</strong>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="datatables-lov-assistant" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.code') }}</th>
                                    <th>{{ trans('shared/common.name') }}</th>
                                    <th>{{ trans('operational/fields.nickname') }}</th>
                                    <th>{{ trans('operational/fields.position') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.phone') }}</th>
                                </tr>
                            </thead>
                                @foreach ($optionAssistant as $assistant)
                                <tr style="cursor: pointer;" data-assistant="{{ json_encode($assistant) }}">
                                    <td>{{ $assistant->driver_code }}</td>
                                    <td>{{ $assistant->driver_name }}</td>
                                    <td>{{ $assistant->driver_nickname }}</td>
                                    <td>{{ $assistant->position }}</td>
                                    <td>{{ $assistant->type }}</td>
                                    <td>{{ $assistant->phone_number }}</td>
                                </tr>
                                @endforeach
                            <tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-lov-po" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('purchasing/fields.purchase-order') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchPo" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchPo" name="searchPo">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-po" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('purchasing/fields.po-number') }}</th>
                                    <th>{{ trans('payable/fields.vendor-code') }}</th>
                                    <th>{{ trans('payable/fields.vendor-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
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
var dataIndexLine = {{ $dataIndexLine }};

$(document).on('ready', function() {
    /** HEADER **/
    enableLovPo();

    $("#datatables-lov-route").dataTable({"pagelength" : 10, "lengthChange": false});
    $('#datatables-lov-route tbody').on('click', 'tr', selectRute);

    $("#datatables-lov-truck").dataTable({"pagelength" : 10, "lengthChange": false});
    $('#datatables-lov-truck tbody').on('click', 'tr', selectTruck);

    $(".show-modal-driver").on('click', showModalDriver)
    $("#datatables-lov-driver").dataTable({"pagelength" : 10, "lengthChange": false});
    $('#datatables-lov-driver tbody').on('click', 'tr', selectDriver);

    $(".remove-driver-assistant").on('click', removeDriverAssistant);
    $(".show-modal-driver-assistant").on('click', showModalDriverAssistant)
    $("#datatables-lov-assistant").dataTable({"pagelength" : 10, "lengthChange": false});
    $('#datatables-lov-assistant tbody').on('click', 'tr', selectAssistant);

    $('#modalPo').on('click', showLovPo);
    $('#searchPo').on('keyup', loadLovPo);
    $('#table-lov-po tbody').on('click', 'tr', selectPo);

    $(".remove-po").on('click', function() {
        $('#poHeaderId').val('');
        $('#poNumber').val('');
    });

    /** LINE **/

    $dataTableLine = $("#table-line").DataTable({"bPaginate": false, "aaSorting": []});

    $('#colySent').on("keypress", keypressFormLine);
    $('#resiNumber').on("keypress", keypressFormLine);
    $("#resiNumber").on('keyup', keyupResiNumber).autocomplete(autocompleteResiNumber).autocomplete( "instance" )._renderItem = renderAutocompleteResiNumber;

    $('.add-line').on('click', addLine);
    $('#table-line tbody').on('click', '.delete-line', deleteLine);
    $('#clear-lines').on('click', clearLine);
    $('[name="btn-save"]').on('click', saveManifest);
    $('[name="btn-request-approve"]').on('click', requestApproveManifest);

    
});

    

/** HEADER **/
var selectRute = function () {
    var data = $(this).data('rute');

    $('#routeId').val(data.route_id);
    $('#headerRouteCode').val(data.route_code);
    $('#kotaAsal').val(data.city_start_name);
    $('#kotaTujuan').val(data.city_end_name);
    $('#modal-lov-route').modal("hide");
};

var selectTruck = function () {
    var data = $(this).data('truck');

    $('#truckId').val(data.truck_id);
    $('#truckNopol').val(data.police_number);
    $('#truckOwner').val(data.owner_name);
    $('#truckCategory').val(data.category);

    $('#poHeaderId').val('');
    $('#poNumber').val('');

    enableLovPo();

    $('#modal-lov-truck').modal("hide");
};

    var showLovPo = function() {
        $('#searchPo').val('');
        loadLovPo(function() {
            $('#modal-lov-po').modal('show');
        });
    };

    var xhrPo;
    var loadLovPo = function(callback) {
        if(xhrPo && xhrPo.readyState != 4){
            xhrPo.abort();
        }
        xhrPo = $.ajax({
            url: '{{ URL($url.'/get-json-po') }}',
            data: {search: $('#searchPo').val()},
            success: function(data) {
                $('#table-lov-po tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-po tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.po_number + '</td>\
                            <td>' + item.kode_vendor + '</td>\
                            <td>' + item.nama_vendor + '</td>\
                            <td>' + item.vendor_address + '</td>\
                            <td>' + item.description + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectPo = function() {
        var data = $(this).data('json');
        $('#poHeaderId').val(data.header_id);
        $('#poNumber').val(data.po_number);

        $('#modal-lov-po').modal('hide');
    };

    var enableLovPo = function () {
        if ($('#truckCategory').val() == '{{ MasterTruck::SEWA_TRIP }}') {
            $('#showLovPo').removeClass('hidden');
        }else{
            $('#showLovPo').addClass('hidden');
        }
    };


    var showModalDriver = function() {
        $('#modal-lov-po').modal("show");
    };

    var showModalDriver = function() {
        if (!isValidShowModalDriverAssistant()) {
            return;
        }

        getDriverAndAssistantSalaryPromise().then(function(data) {
            // if (!data.driver_salary) {
            //     $('#modal-alert').find('.alert-message').html('Driver salary for this Route and Truck is not exist');
            //     $('#modal-alert').modal('show');
            //     return false;
            // }

            $('#modal-lov-driver').modal("show");
        });
    };

    var getDriverAndAssistantSalaryPromise = function() {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '{{ URL($url . '/get-driver-and-assistant-salary') }}',
                data: {routeId: $('#routeId').val(), truckId: $('#truckId').val(), "_token": "{{ csrf_token() }}"},
                method: 'POST'
            }).done(function(result) {
                resolve(result);
            });
        })
    };

    var removeDriverAssistant = function() {
        $('#driverAssistantId').val('');
        $('#driverAssistantName').val('');
    };

    var showModalDriverAssistant = function() {
        if (!isValidShowModalDriverAssistant()) {
            return;
        }

        getDriverAndAssistantSalaryPromise().then(function(data) {
            // if (!data.driver_assistant_salary) {
            //     $('#modal-alert').find('.alert-message').html('Driver Assistant salary for this Route and Truck is not exist');
            //     $('#modal-alert').modal('show');
            //     return false;
            // }

            $('#modal-lov-driver-assistant').modal("show");
        });
    };

    var isValidShowModalDriverAssistant = function() {
        if (! $('#routeId').val()) {
            $('#modal-alert').find('.alert-message').html('Choose Route First');
            $('#modal-alert').modal('show');
            return false;
        }

        if (! $('#truckId').val()) {
            $('#modal-alert').find('.alert-message').html('Choose Truck First');
            $('#modal-alert').modal('show');
            return false;
        }

        return true;
    };

    var selectDriver = function () {
        var data = $(this).data('driver');

        if (data.driver_id == $('#driverAssistantId').val()) {
            $('#modal-alert').find('.alert-message').html('Driver and assistant must different!');
            $('#modal-alert').modal('show');
            return false;
        }

        $('#driverId').val(data.driver_id);
        $('#driverName').val(data.driver_name);
        $('#modal-lov-driver').modal("hide");
    };

    var selectAssistant = function () {
        var data = $(this).data('assistant');

        if ($('#driverId').val() == data.driver_id) {
            $('#modal-alert').find('.alert-message').html('Driver and assistant must different!');
            $('#modal-alert').modal('show');
            return false;
        }

        $('#driverAssistantId').val(data.driver_id);
        $('#driverAssistantName').val(data.driver_name);
        $('#modal-lov-driver-assistant').modal("hide");
    };

    /** LINE **/
    var keyupResiNumber = function() {
        var resiNumber = $('#resiNumber').val();
        clearFormAddLine();

        $('#resiNumber').val(resiNumber);
    };

    var autocompleteResiNumber = {
        source: "{{ URL($url.'/get-json-resi') }}",
        minLength: 1,
        focus: function(event, ui) {
            $("#resiNumber").val(ui.item.resi_number);
            return false;
        },
        select: function(event, ui) {
            $("#resiId").val(ui.item.resi_header_id);
            $("#resiNumber").val(ui.item.resi_number);
            $("#routeCode").val(ui.item.route_code);
            $("#customerReceiver").val(ui.item.customer_receiver_name);
            $("#receiver").val(ui.item.receiver_name);
            $("#itemName").val(ui.item.item_name);
            $("#totalColy").val(ui.item.coly_wh);
            $("#colySent").focus();

            return false;
        }
    };

    var renderAutocompleteResiNumber = function(ul, item) {
      return $( "<li>" )
        .append(
            '<div>\
            ' + item.resi_number + ' <b>(' + item.total_coly + ' / ' + item.coly_wh + ')</b><br/>\
            <small>' + item.route_code + ' / ' + item.receiver + '</small>\
            </div>'
        )
        .appendTo( ul );
    };

    var keypressFormLine = function(e) {
        if (e.keyCode == 13) {
            addLine();
            return false;
        }
    };

    var addLine = function(event) {
        var resiId = $('#resiId').val();
        var resiNumber = $('#resiNumber').val();
        var routeCode = $('#routeCode').val();
        var customerReceiver = $('#customerReceiver').val();
        var receiver = $('#receiver').val();
        var itemName = $('#itemName').val();
        var totalColy = $('#totalColy').val();
        var colySent = $('#colySent').val();
        var error = false;

        if (resiId == '' || resiId <= 0) {
            $('#resiNumber').parent().parent().addClass('has-error');
            error = true;
        } else {
            $('#resiNumber').parent().parent().removeClass('has-error');
        }

        if (colySent == '' || colySent <= 0) {
            $('#colySent').parent().parent().addClass('has-error');
            error = true;
        } else {
            $('#colySent').parent().parent().removeClass('has-error');
        }

        if (error) {
            return;
        }

        var htmlTr = '<td >' + resiNumber + '</td>' +
            '<td>' + routeCode + '</td>' +
            '<td>' + customerReceiver + '</td>' +
            '<td>' + receiver + '</td>' +
            '<td>' + itemName + '</td>' +
            '<td class="text-right">' + totalColy + '</td>' +
            '<td class="text-right">' + colySent + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="">' +
            '<input type="hidden" name="resiId[]" value="' + resiId + '">' +
            '<input type="hidden" name="resiNumber[]" value="' + resiNumber + '">' +
            '<input type="hidden" name="routeCode[]" value="' + routeCode + '">' +
            '<input type="hidden" name="customerReceiver[]" value="' + customerReceiver + '">' +
            '<input type="hidden" name="receiver[]" value="' + receiver + '">' +
            '<input type="hidden" name="itemName[]" value="' + itemName + '">' +
            '<input type="hidden" name="totalColy[]" value="' + totalColy + '">' +
            '<input type="hidden" name="colySent[]" value="' + colySent + '">' +
            '</td>';

        $dataTableLine.row.add( $('<tr>' + htmlTr + '</tr>')[0] ).draw( false );
        $('#resiNumber').focus();

        calculateTotalColy();
        clearFormAddLine();

        $('.delete-line').on('click', deleteLine);
    };

    var clearFormAddLine = function() {
        $("#resiId").val('');
        $("#resiNumber").val('');
        $("#routeCode").val('');
        $("#customerReceiver").val('');
        $("#receiver").val('');
        $("#itemName").val('');
        $("#totalColy").val('');
        $("#colySent").val('');
    };

    var clearLine = function() {
        $('#table-line tbody').html('');
    };

    var deleteLine = function() {
        $dataTableLine.row($(this).parents('tr')).remove().draw();

        calculateTotalColy();
    };

    var calculateTotalColy = function() {
        var totalColy = 0;
        $('#table-line tbody tr').each(function (i, row) {
            totalColy += parseInt($(row).find('[name="colySent[]"]').val());
        });

        $('#totalColyHeader').val(totalColy);
        $('#totalColyHeader').autoNumeric('update', {mDec: 0});
    };

    var calculateTotalWeight = function() {
        var totalWeight = 0;
        $('#table-line tbody tr').each(function (i, row) {
            totalWeight += parseInt($(row).find('[name="approximateWeight[]"]').val());
        });

        $('#totalWeight').val(totalWeight);
        $('#totalWeight').autoNumeric('update', {mDec: 0});
    };

    var saveManifest = function(event) {
        event.preventDefault();
        $dataTableLine.search( '' ).columns().search( '' ).draw();
        $('#add-form').append('<input type="hidden" name="btn-save" />');
        $('#add-form').submit();
    };

    var requestApproveManifest = function(event) {
        event.preventDefault();
        $dataTableLine.search( '' ).columns().search( '' ).draw();
        $('#add-form').append('<input type="hidden" name="btn-request-approve" />');
        $('#add-form').submit();
    };
</script>
@endsection
