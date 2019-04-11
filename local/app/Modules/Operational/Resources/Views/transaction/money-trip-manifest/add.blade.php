<?php
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterDriver;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;

$allowUpdateMoneyTrip = true;
if ($model->truck === null || $model->truck->isSewaTrip()) {
    $allowUpdateMoneyTrip = false;
}

foreach ($model->invoiceHeader as $invoice) {
    if ($invoice->notCanceled()) {
        $allowUpdateMoneyTrip = false;
    }
}
?>

@extends('layouts.master')

@section('title', trans('operational/menu.manifest'))

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
                                <a href="#tabMoneyTrip" data-toggle="tab">{{ trans('operational/fields.money-trip') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabMoneyTrip">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('moneyTrip') ? 'has-error' : '' }}">
                                        <label for="moneyTrip" class="col-sm-4 control-label">{{ trans('operational/fields.money-trip') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <?php
                                            $route = $model->route;
                                            if ($model->money_trip !== null) {
                                                $moneyTripValue = $model->money_trip;
                                            } else {
                                                $moneyTrip = $route !== null ? $route->moneyTrip : null;
                                                $moneyTripValue = $moneyTrip !== null ? $moneyTrip->money_trip_rate : 0;
                                            }
                                            ?>
                                            <input type="text" class='form-control currency' id="moneyTrip" name="moneyTrip" value="{{ count($errors) > 0 ? old('moneyTrip') : $moneyTripValue }}" {{ !$allowUpdateMoneyTrip ? 'readonly' : '' }}/>
                                            @if($errors->has('moneyTrip'))
                                            <span class="help-block">{{ $errors->first('moneyTrip') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('moneyTripNotes') ? 'has-error' : '' }}">
                                        <label for="moneyTripNotes" class="col-sm-4 control-label">{{ trans('operational/fields.money-trip-notes') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea id="moneyTripNotes" name="moneyTripNotes" class="form-control"  {{ !$allowUpdateMoneyTrip ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('moneyTripNotes') : $model->money_trip_note }}</textarea>
                                            @if($errors->has('moneyTripNotes'))
                                            <span class="help-block">{{ $errors->first('moneyTripNotes') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
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
                                                <input type="text" id="manifestDate" name="manifestDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $manifestDate->format('d-m-Y') }}" disabled/>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
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
                                            <textarea class="form-control" id="description" name="description" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                    $truck = $model->truck;
                                    $truckNopol = $truck !== null ? $truck->police_number : '';
                                    $truckOwner = $truck !== null ? $truck->owner_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('truckId') ? 'has-error' : '' }}">
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
                                        <label for="truckCategory" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="truckCategory" name="truckCategory" value="{{ $model->truck !== null ? $model->truck->getCategory() : '' }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $driver = $model->driver;
                                    $driverName = $driver !== null ? $driver->driver_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                        <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $driverAssistant = $model->driverAssistant;
                                    $driverAssistantName = $driverAssistant !== null ? $driverAssistant->driver_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('driverAssistantId') ? 'has-error' : '' }}">
                                        <label for="driverAssistantName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverAssistantName" name="driverAssistantName" value="{{ $driverAssistantName }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="status" name="status" class="form-control" value="{{ $model->status }}" disabled />
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
                                                    <td class="text-right"> {{ number_format($line->coly_sent) }} </td>
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
                                @if($allowUpdateMoneyTrip)
                                    <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function() {
});
</script>
@endsection
