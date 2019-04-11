<?php
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
use App\Modules\Operational\Model\Master\MasterRoute;
?>

@extends('layouts.master')

@section('title', trans('operational/menu.resi'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.resi') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">

                <div id="horizontal-form">
                    <form  role="form" id="form-resi" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->resi_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineDetails" data-toggle="tab">{{ trans('operational/fields.line-detail') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineUnits" data-toggle="tab">{{ trans('operational/fields.line-unit') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="{{ !Gate::check('access', [$resource, 'nego']) ? 'hidden' : '' }}">
                                <a href="#tabNego" data-toggle="tab">{{ trans('operational/fields.nego') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-4 portlets">
                                    <div class="form-group">
                                        <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="resiNumber" name="resiNumber" value="{{ $model->resi_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="resiDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $resiDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="resiDate" name="resiDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $resiDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $pickupRequestNumber = $model->pickupRequest !== null ? $model->pickupRequest->pickup_request_number : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-request') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="pickupRequestId" name="pickupRequestId" value="{{ count($errors) > 0 ? old('pickupRequestId') : $model->pickup_request_id }}">
                                                <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" value="{{ count($errors) > 0 ? old('pickupRequestNumber') : $pickupRequestNumber }}" readonly>
                                                <span class="btn input-group-addon {{ $model->isIncomplete() ? 'remove-pickup-request' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="{{ $model->isIncomplete() ? '#modal-lov-pickup-request' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $customer = $model->customer()->first();
                                    $customerName = $customer !== null ? $customer->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="customerId" name="customerId" value="{{ count($errors) > 0 ? old('customerId') : $model->customer_id }}">
                                                <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $customerName }}" readonly>
                                                <span class="btn input-group-addon {{ $model->isIncomplete() ? 'remove-customer' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="{{ $model->isIncomplete() ? '#modal-lov-customer' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('senderName') ? 'has-error' : '' }}">
                                        <label for="senderName" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderName" name="senderName" value="{{ count($errors) > 0 ? old('senderName') : $model->sender_name }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            @if($errors->has('senderName'))
                                            <span class="help-block">{{ $errors->first('senderName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('senderAddress') ? 'has-error' : '' }}">
                                        <label for="senderAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderAddress" name="senderAddress" value="{{ count($errors) > 0 ? old('senderAddress') : $model->sender_address }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            @if($errors->has('senderAddress'))
                                            <span class="help-block">{{ $errors->first('senderAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('senderPhone') ? 'has-error' : '' }}">
                                        <label for="senderPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderPhone" name="senderPhone" value="{{ count($errors) > 0 ? old('senderPhone') : $model->sender_phone }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            @if($errors->has('senderPhone'))
                                            <span class="help-block">{{ $errors->first('senderPhone') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.save-to-customer') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $saveToCustomerSender = count($errors) > 0 ? old('saveToCustomerSender') : 0; ?>
                                                <input type="checkbox" id="saveToCustomerSender" name="saveToCustomerSender" value="1" {{ $saveToCustomerSender ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            </label>
                                        </div>
                                    </div> -->
                                    <?php
                                    $customerReceiverName = !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerReceiverName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="customerReceiverId" name="customerReceiverId" value="{{ count($errors) > 0 ? old('customerReceiverId') : $model->customer_receiver_id }}">
                                                <input type="text" class="form-control" id="customerReceiverName" name="customerReceiverName" value="{{ count($errors) > 0 ? old('customerReceiverName') : $customerReceiverName }}" readonly>
                                                <span class="btn input-group-addon {{ $model->isIncomplete() ? 'remove-customer-receiver' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="{{ $model->isIncomplete() ? '#modal-lov-customer-receiver' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('receiverName') ? 'has-error' : '' }}">
                                        <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverName" name="receiverName" value="{{ count($errors) > 0 ? old('receiverName') : $model->receiver_name }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            @if($errors->has('receiverName'))
                                            <span class="help-block">{{ $errors->first('receiverName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('receiverAddress') ? 'has-error' : '' }}">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverAddress" name="receiverAddress" value="{{ count($errors) > 0 ? old('receiverAddress') : $model->receiver_address }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            @if($errors->has('receiverAddress'))
                                            <span class="help-block">{{ $errors->first('receiverAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('receiverPhone') ? 'has-error' : '' }}">
                                        <label for="receiverPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverPhone" name="receiverPhone" value="{{ count($errors) > 0 ? old('receiverPhone') : $model->receiver_phone }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            @if($errors->has('receiverPhone'))
                                            <span class="help-block">{{ $errors->first('receiverPhone') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.save-to-customer') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $saveToCustomerReceiver = count($errors) > 0 ? old('saveToCustomerReceiver') : 0; ?>
                                                <input type="checkbox" id="saveToCustomerReceiver" name="saveToCustomerReceiver" value="1" {{ $saveToCustomerReceiver ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            </label>
                                        </div>
                                    </div> -->
                                </div>
                                <div class="col-sm-4 portlets">
                                    <?php
                                    $routeId = count($errors) > 0 ? intval(old('routeId')) : $model->route_id;
                                    $route = MasterRoute::find($routeId);
                                    $kodeRute = $route !== null ? $route->route_code : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('routeId') ? 'has-error' : '' }}">
                                        <label for="kodeRute" class="col-sm-4 control-label">{{ trans('operational/fields.kode-rute') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="hidden" id="routeId" name="routeId" value="{{ $route !== null ? $route->route_id : '' }}">
                                            <input type="hidden" name="priceKg" id="priceKg" value="{{ $route !== null ? $route->rate_kg : 0  }}">
                                            <input type="hidden" name="priceM3" id="priceM3" value="{{ $route !== null ? $route->rate_m3 : 0 }}">
                                            <input type="hidden" name="minimumRates" id="minimumRates" value="{{ $route !== null ? $route->minimum_rates : 0 }}">
                                            <input type="text" class="form-control" id="kodeRute" name="kodeRute" value="{{ count($errors) > 0 ? old('kodeRute') : $kodeRute }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
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
                                            <input type="text" class="form-control" id="kotaAsal" name="kotaAsal" value="{{ count($errors) > 0 ? old('kotaAsal') : $namaKotaAsal }}" readonly>
                                        </div>
                                    </div>
                                    <?php
                                    $kotaTujuan = $route !== null ? $route->cityEnd()->first() : null;
                                    $namaKotaTujuan = $kotaTujuan !== null ? $kotaTujuan->city_name : '';
                                    ?>
                                    <input type="hidden" name="kotaTujuan">
                                    <div class="form-group">
                                        <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaTujuan" name="kotaTujuan" value="{{ count($errors) > 0 ? old('kotaTujuan') : $namaKotaTujuan }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalColy" name="totalColy" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalColy')) : $model->totalColy() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalWeight" class="col-sm-4 control-label">{{ trans('operational/fields.total-weight') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control decimal' id="totalWeight" name="totalWeight" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalWeight')) : $model->totalWeight() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalVolume" class="col-sm-4 control-label">{{ trans('operational/fields.total-volume') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control decimal6' id="totalVolume" name="totalVolume" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalVolume')) : $model->totalVolume() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('operational/fields.total-amount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $model->totalAmount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount" class="col-sm-4 control-label">{{ trans('operational/fields.discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="discount" name="discount" data-v-min="-99999999999" value="{{ count($errors) > 0 ? str_replace(',', '', old('discount')) : $model->discount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('shared/common.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ count($errors) > 0 ? str_replace(',', '', old('total')) : $model->total() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="status" name="status" value="{{ $model->status }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.insurance') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $insurance = count($errors) > 0 ? old('insurance') : $model->insurance; ?>
                                                <input type="checkbox" id="insurance" name="insurance" value="1" {{ $insurance ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group {{ $errors->has('itemNameHeader') ? 'has-error' : '' }}">
                                        <label for="itemNameHeader" class="col-sm-12 control-label">{{ trans('operational/fields.item-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="itemNameHeader" name="itemNameHeader" rows="3" {{ !$model->isIncomplete() ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('itemNameHeader') : $model->item_name }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-12 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="3" {{ !$model->isIncomplete() ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label class="col-sm-12 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <?php $type = count($errors) > 0 ? old('type') : $model->type; ?>
                                        <div class="col-sm-12">
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="type" id="type-reguler" value="{{ TransactionResiHeader::REGULER }}" {{ $type == TransactionResiHeader::REGULER ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}> {{ trans('operational/fields.reguler') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="type" id="type-carter" value="{{ TransactionResiHeader::CARTER }}" {{ $type == TransactionResiHeader::CARTER ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}> {{ trans('operational/fields.carter') }}
                                            </label>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('payment') ? 'has-error' : '' }}">
                                        <label class="col-sm-12 control-label">{{ trans('operational/fields.payment') }} <span class="required">*</span></label>
                                        <?php $payment = count($errors) > 0 ? old('payment') : $model->payment; ?>
                                        <div class="col-sm-12">
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-cash" value="{{ TransactionResiHeader::CASH }}" {{ $payment == TransactionResiHeader::CASH ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}> {{ trans('operational/fields.cash') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-bill-to-sender" value="{{ TransactionResiHeader::BILL_TO_SENDER }}" {{ $payment == TransactionResiHeader::BILL_TO_SENDER ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}> {{ trans('operational/fields.bill-to-sender') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-bill-to-reciever" value="{{ TransactionResiHeader::BILL_TO_RECIEVER }}" {{ $payment == TransactionResiHeader::BILL_TO_RECIEVER ? 'checked' : '' }} {{ !$model->isIncomplete() ? 'disabled' : '' }}> {{ trans('operational/fields.bill-to-receiver') }}
                                            </label>
                                            @if($errors->has('payment'))
                                            <span class="help-block">{{ $errors->first('payment') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLineDetails">
                                <div class="col-sm-12 portlets">
                                     @if ($model->isIncomplete())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line-detail">
                                                        <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                                    </a>
                                                    <a id="clear-lines-detail" href="#" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-remove"></i> {{ trans('shared/common.clear') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-detail" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.coly') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}</th>
                                                    <th>{{ trans('operational/fields.volume') }}</th>
                                                    <th>{{ trans('operational/fields.price-weight') }}</th>
                                                    <th>{{ trans('operational/fields.price-volume') }}</th>
                                                    <th>{{ trans('operational/fields.price') }}</th>

                                                    @if($model->isIncomplete())
                                                    <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <?php $dataIndexDetail = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineDetailId', [])); $i++)
                                                <tr data-index-detail="{{ $dataIndexDetail }}">
                                                    <td > {{ old('itemName')[$i] }} </td>
                                                    <td class="text-right"> {{ old('coly')[$i] }} </td>
                                                    <td class="text-right"> {{ old('weight')[$i] }} </td>
                                                    <td class="text-right"> {{ old('priceWeight')[$i] }} </td>
                                                    <td class="text-right"> {{ old('volume')[$i] }} </td>
                                                    <td class="text-right"> {{ old('priceVolume')[$i] }} </td>
                                                    <td class="text-right"> {{ old('totalPrice')[$i] }} </td>
                                                    @if($model->isIncomplete())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line-detail" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line-detail" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineDetailId[]" value="{{ old('lineDetailId')[$i] }}">
                                                        <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                        <input type="hidden" name="coly[]" value="{{ old('coly')[$i] }}">
                                                        <input type="hidden" name="qtyWeight[]" value="{{ old('qtyWeight')[$i] }}">
                                                        <input type="hidden" name="weight[]" value="{{ old('weight')[$i] }}">
                                                        <input type="hidden" name="totalWeightLine[]" value="{{ old('totalWeightLine')[$i] }}">
                                                        <input type="hidden" name="priceWeight[]" value="{{ old('priceWeight')[$i] }}">
                                                        <input type="hidden" name="volume[]" value="{{ old('volume')[$i] }}">
                                                        <input type="hidden" name="priceVolume[]" value="{{ old('priceVolume')[$i] }}">
                                                        <input type="hidden" name="totalPrice[]" value="{{ old('totalPrice')[$i] }}">
                                                        <input type="hidden" name="lineVolume[]" value="{{ old('lineVolume')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndexDetail++; ?>
                                                @endfor

                                                @else
                                                @foreach($model->line()->whereNull('unit_id')->get() as $line)
                                                <tr data-index-detail="{{ $dataIndexDetail }}">
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->coly) }} </td>
                                                    <td class="text-right"> {{ number_format($line->weight, 2) }} </td>
                                                    <td class="text-right"> {{ number_format($line->totalVolume(), 6) }} </td>
                                                    <td class="text-right"> {{ number_format($line->price_weight) }} </td>
                                                    <td class="text-right"> {{ number_format($line->totalPriceVolume()) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                    @if($model->isIncomplete())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line-detail" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line-detail" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineDetailId[]" value="{{ $line->resi_line_id }}">
                                                        <input type="hidden" name="itemName[]" value="{{ $line->item_name }}">
                                                        <input type="hidden" name="coly[]" value="{{ number_format($line->coly) }}">
                                                        <input type="hidden" name="qtyWeight[]" value="{{ number_format($line->qty_weight) }}">
                                                        <input type="hidden" name="weight[]" value="{{ number_format($line->weight_unit, 2) }}">
                                                        <input type="hidden" name="totalWeightLine[]" value="{{ number_format($line->weight, 2) }}">
                                                        <input type="hidden" name="volume[]" value="{{ number_format($line->totalVolume(), 6) }}">
                                                        <input type="hidden" name="priceWeight[]" value="{{ number_format($line->price_weight) }}">
                                                        <input type="hidden" name="priceVolume[]" value="{{ number_format($line->totalPriceVolume()) }}">
                                                        <input type="hidden" name="totalPrice[]" value="{{ number_format($line->total_price) }}">
                                                        <?php
                                                        $lineVolumes = [];
                                                        forEach($line->lineVolume as $lineVolume) {
                                                            $lineVolumes[] = [
                                                                'qty' => $lineVolume->qty_volume,
                                                                'dimensionL' => $lineVolume->dimension_long,
                                                                'dimensionW' => $lineVolume->dimension_width,
                                                                'dimensionH' => $lineVolume->dimension_height,
                                                                'volume' => $lineVolume->total_volume,
                                                            ];
                                                        }
                                                        ?>
                                                        <input type="hidden" name="lineVolume[]" value="{{ json_encode($lineVolumes) }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndexDetail++; ?>

                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabLineUnits">
                                <div class="col-sm-12 portlets">
                                    @if ($model->isIncomplete())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line-unit">
                                                        <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                                    </a>
                                                    <a id="clear-lines-unit" href="#" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-remove"></i> {{ trans('shared/common.clear') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-unit" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-unit') }}</th>
                                                    <th>{{ trans('operational/fields.price-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-price') }}</th>

                                                    @if ($model->isIncomplete())
                                                    <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndexUnit = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineUnitId', [])); $i++)
                                                <tr data-index-unit="{{ $dataIndexUnit }}">
                                                    <td > {{ old('itemNameUnit')[$i] }} </td>
                                                    <td class="text-right"> {{ old('totalUnit')[$i] }} </td>
                                                    <td class="text-right"> {{ old('priceUnit')[$i] }} </td>
                                                    <td class="text-right"> {{ old('totalPriceUnit')[$i] }} </td>
                                                    @if($model->isIncomplete())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line-unit" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line-unit" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineUnitId[]" value="{{ old('lineUnitId')[$i] }}">
                                                        <input type="hidden" name="unitId[]" value="{{ old('unitId')[$i] }}">
                                                        <input type="hidden" name="commodityId[]" value="{{ old('commodityId')[$i] }}">
                                                        <input type="hidden" name="itemNameUnit[]" value="{{ old('itemNameUnit')[$i] }}">
                                                        <input type="hidden" name="totalUnit[]" value="{{ old('totalUnit')[$i] }}">
                                                        <input type="hidden" name="priceUnit[]" value="{{ old('priceUnit')[$i] }}">
                                                        <input type="hidden" name="totalPriceUnit[]" value="{{ old('totalPriceUnit')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndexUnit++; ?>

                                                @endfor
                                                @else
                                                @foreach($model->line()->whereNotNull('unit_id')->get() as $line)
                                                <?php $line = TransactionResiLine::find($line->resi_line_id); ?>
                                                <tr data-index-unit="{{ $dataIndexUnit }}">
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_unit) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price / $line->total_unit) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                    @if($model->isIncomplete())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line-unit" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line-unit" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineUnitId[]" value="{{ $line->resi_line_id }}">
                                                        <input type="hidden" name="unitId[]" value="{{ $line->unit_id }}">
                                                        <input type="hidden" name="commodityId[]" value="{{ !empty($line->shippingPrice) ? $line->shippingPrice->commodity_id : '' }}">
                                                        <input type="hidden" name="itemNameUnit[]" value="{{ $line->item_name }}">
                                                        <input type="hidden" name="totalUnit[]" value="{{ number_format($line->total_unit) }}">
                                                        <input type="hidden" name="priceUnit[]" value="{{ number_format($line->total_price / $line->total_unit) }}">
                                                        <input type="hidden" name="totalPriceUnit[]" value="{{ number_format($line->total_price) }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndexUnit++; ?>
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $nego = $model->nego()->whereNull('approved')->first();
                            $negoPrice = $nego !== null ? $nego->nego_price : '';
                            $requestedNote = $nego !== null ? $nego->requested_note : '';
                            ?>
                            <div class="tab-pane fade {{ !(Gate::check('access', [$resource, 'nego'])) ? 'hidden' : '' }}" id="tabNego">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="negoTotal" class="col-sm-4 control-label">{{ trans('shared/common.total') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="negoTotal" name="negoTotal" value="{{ count($errors) > 0 ? str_replace(',', '', old('negoTotal')) : $model->total() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="negoPrice" class="col-sm-4 control-label">{{ trans('operational/fields.nego-price') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="negoPrice" name="negoPrice" value="{{ count($errors) > 0 ? str_replace(',', '', old('negoPrice')) : $negoPrice }}" {{ !$model->isIncomplete() ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="negoDiscount" class="col-sm-4 control-label">{{ trans('operational/fields.discount') }}</label>
                                        <div class="col-sm-8">
                                            <?php $negoDiscount = !empty($negoPrice) ? $model->total() - $negoPrice : ''; ?>
                                            <input type="text" class="form-control currency" id="negoDiscount" name="negoDiscount" data-v-min="-99999999999" value="{{ count($errors) > 0 ? str_replace(',', '', old('negoDiscount')) : $negoDiscount }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('requestedNote') ? 'has-error' : '' }}">
                                        <label for="requestedNote" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="requestedNote" name="requestedNote" rows="4" {{ !$model->isIncomplete() ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('requestedNote') : $requestedNote }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                           <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if($model->isIncomplete())
                                <button type="submit" name="btn-booking-number" class="btn btn-sm btn-submit btn-primary"><i class="fa fa-save"></i> {{ trans('operational/fields.booking-number') }}</button>
                                <button type="submit" name="btn-save" class="btn btn-sm btn-submit btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->isIncomplete())
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-submit btn-info">
                                    <i class="fa fa-save"></i> {{ trans('operational/fields.approve') }}
                                </button>
                                @endif
                                @if($model->isApproved())
                                    <a href="{{ URL($url . '/print-pdf/' . $model->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                    </a>
                                    <a href="{{ URL($url . '/print-pdf-tanpa-biaya/' . $model->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('operational/fields.print-tanpa-biaya') }}">
                                        <i class="fa fa-print"></i> {{ trans('operational/fields.print-tanpa-biaya') }}
                                    </a>
                                    <a href="{{ URL($url . '/print-voucher/' . $model->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('operational/fields.print-voucher') }}">
                                        <i class="fa fa-print"></i> {{ trans('operational/fields.print-voucher') }}
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
<div id="modal-add-line-unit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-unit">{{ trans('shared/common.add') }}</span> {{ trans('operational/fields.line-unit') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                <div class="col-sm-12 portlets">
                                    <div class="form-group {{ $errors->has('itemNameUnit') ? 'has-error' : '' }}">
                                        <label for="itemNameUnit" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="dataIndexFormUnit" id="dataIndexFormUnit" value="">
                                                <input type="hidden" name="unitId" id="unitId" value="">
                                                <input type="hidden" name="commodityId" id="commodityId" value="">
                                                <input type="text" class="form-control" id="itemNameUnit" name="itemNameUnit" readonly>
                                                <span class="btn input-group-addon" id="show-lov-unit"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="priceUnit" class="col-sm-4 control-label">{{ trans('operational/fields.price-unit') }} <span class="required">*</span> </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="priceUnit" name="priceUnit" value="" readonly>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalUnit" class="col-sm-4 control-label">{{ trans('operational/fields.total-unit') }} <span class="required">*</span> </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="totalUnit" name="totalUnit" value="">
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalPriceUnit" class="col-sm-4 control-label">{{ trans('operational/fields.total-price') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="decimal form-control" id="totalPriceUnit" name="totalPriceUnit" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" id="cancel-save-line-unit" data-dismiss="modal">{{ trans('shared/common.cancel') }}</button>
                <button type="button" class="btn btn-sm btn-primary" id="save-line-unit">
                    <span id="submit-modal-line-unit">{{ trans('shared/common.add') }}</span> {{ trans('operational/fields.line-detail') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modal-lov-unit" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.item-unit') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchUnit" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchUnit" name="searchUnit">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-unit" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                    <th>{{ trans('operational/fields.price-unit') }}</th>
                                    <th>{{ trans('operational/fields.description') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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

<div id="modal-line-detail" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('operational/fields.line-detail') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="dataIndexFormDetail" id="dataIndexFormDetail" value="">
                                <input type="hidden" name="idDetail" id="idDetail" value="">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('itemName') ? 'has-error' : '' }}">
                                        <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemName" name="itemName">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="coly" class="col-sm-4 control-label">{{ trans('operational/fields.coly') }} <span class="required">*</span> </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="coly" name="coly" value="">
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12 portlets"><hr /></div>
                                <div class="col-sm-12 portlets">
                                    <div class="form-group">
                                        <label for="weight" class="col-sm-2 control-label">{{ trans('operational/fields.weight') }}</label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control currency" id="qtyWeight" name="qtyWeight" placeholder="Qty" value="">
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control decimal" id="weight" name="weight" placeholder="Weight Unit" value="">
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control decimal" id="totalWeightLine" name="totalWeightLine" placeholder="Weight" value="" disabled>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12 portlets"><hr /></div>
                                <div class="col-sm-12 portlets">
                                    <div class="form-group">
                                        <label for="volume" class="col-sm-2 control-label">{{ trans('operational/fields.volume') }}</label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control currency" id="qtyVolume" name="qtyVolume" placeholder="Qty" value="">
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="text" class="form-control currency text-right" id="dimensionL" placeholder="L" name="dimensionL" >
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="text" class="form-control currency text-right" id="dimensionH" placeholder="W" name="dimensionH" >
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="text" class="form-control currency text-right" id="dimensionW" placeholder="H" name="dimensionW" >
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control currency" id="totalVolumeLine" name="totalVolumeLine" placeholder="Volume" value="" disabled>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="button" class="btn btn-sm btn-primary" id="add-line-volume"><i class="fa fa-plus"></i> Add Volume</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-10 col-sm-offset-2 portlets">
                                    <table id="table-line-volume" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('operational/fields.qty') }}</th>
                                                <th>{{ trans('operational/fields.dimension') }}</th>
                                                <th>{{ trans('operational/fields.volume') }}</th>
                                                <th width="60px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-12 portlets">
                                    <div class="form-group">
                                        <label for="totalVolumeLineAll" class="col-sm-2 control-label">{{ trans('operational/fields.total-volume') }}</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control currency text-right" id="totalVolumeLineAll" name="totalVolumeLineAll" value="0" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 portlets"><hr /></div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="priceWeight" class="col-sm-4 control-label">{{ trans('operational/fields.price-weight') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="priceWeight" name="priceWeight" value="0" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="priceVolume" class="col-sm-4 control-label">{{ trans('operational/fields.price-volume') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="priceVolume" name="priceVolume" value="0" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 portlets">
                                    <div class="form-group">
                                        <label for="totalPriceDetail" class="col-sm-2 control-label">{{ trans('operational/fields.price') }} </label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control currency text-right" id="totalPriceDetail" name="totalPriceDetail" value="0" disabled>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" id="cancel-save-line-detail" data-dismiss="modal">{{ trans('shared/common.cancel') }}</button>
                <button type="button" class="btn btn-sm btn-primary" id="save-line-detail">
                    <span id="submit-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('operational/fields.line-detail') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modal-lov-pickup-request" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.pickup-request') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="datatables-lov-pickup-request" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="hidden"></th>
                                    <th>{{ trans('marketing/fields.pickup-request-number') }}<hr/>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('operational/fields.customer') }}</th>
                                    <th>{{ trans('shared/common.address') }}<hr/>{{ trans('shared/common.phone') }}</th>
                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                    <th>{{ trans('operational/fields.dimension') }}</th>
                                    <th>{{ trans('operational/fields.weight') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($optionPickupRequest as $pickupRequest)
                                <?php
                                $no = 1;
                                $pickupRequestDate = !empty($pickupRequest->pickup_request_time) ? new \DateTime($pickupRequest->pickup_request_time) : null;
                                ?>
                                <tr style="cursor: pointer;" data-pickup-request="{{ json_encode($pickupRequest) }}">
                                    <td class="hidden">{{ $no++ }}</td>
                                    <td>
                                        {{ $pickupRequest->pickup_request_number }}<hr/>
                                        {{ $pickupRequestDate !== null ? $pickupRequestDate->format('d-m-Y H:i:s') : '' }}
                                    </td>
                                    <td>{{ $pickupRequest->customer_name }}</td>
                                    <td>
                                        {{ $pickupRequest->address }}<hr/>
                                        {{ $pickupRequest->phone_number }}
                                    </td>
                                    <td>{{ $pickupRequest->item_name }}</td>
                                    <td class="text-right">{{ number_format($pickupRequest->total_coly) }}</td>
                                    <td class="text-right">{{ number_format($pickupRequest->dimension,4) }}</td>
                                    <td class="text-right">{{ number_format($pickupRequest->weight,2) }}</td>
                                </tr>
                                @endforeach
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

<div id="modal-lov-customer" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.customer') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="datatables-lov-customer" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.code') }}</th>
                                    <th>{{ trans('shared/common.name') }}</th>
                                    <th>{{ trans('operational/fields.address') }}</th>
                                    <th>{{ trans('operational/fields.phone') }}</th>
                                    <th>{{ trans('operational/fields.description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($optionCustomer as $customer)
                                <tr style="cursor: pointer;" data-customer="{{ json_encode($customer) }}">
                                    <td>{{ $customer->customer_code }}</td>
                                    <td>{{ $customer->customer_name }}</td>
                                    <td>{{ $customer->address }}</td>
                                    <td>{{ $customer->phone_number }}</td>
                                    <td>{{ $customer->description }}</td>
                                </tr>
                                @endforeach
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

<div id="modal-lov-customer-receiver" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.customer') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="datatables-lov-customer-receiver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.code') }}</th>
                                    <th>{{ trans('shared/common.name') }}</th>
                                    <th>{{ trans('operational/fields.address') }}</th>
                                    <th>{{ trans('operational/fields.phone') }}</th>
                                    <th>{{ trans('operational/fields.description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($optionCustomer as $customer)
                                <tr style="cursor: pointer;" data-customer="{{ json_encode($customer) }}">
                                    <td>{{ $customer->customer_code }}</td>
                                    <td>{{ $customer->customer_name }}</td>
                                    <td>{{ $customer->address }}</td>
                                    <td>{{ $customer->phone_number }}</td>
                                    <td>{{ $customer->description }}</td>
                                </tr>
                                @endforeach
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

@if(Session::has('approvedMessage'))
<div id="modal-approved-message" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <h2 class="text-center">{{ Session::get('approvedMessage') }}</h2>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="print-from-modal" href="#" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                </a>
                <a id="print-tanpa-biaya-from-modal" href="{{ URL($url . '/print-pdf-tanpa-biaya/' . $model->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('operational/fields.print-tanpa-biaya') }}">
                    <i class="fa fa-print"></i> {{ trans('operational/fields.print-tanpa-biaya') }}
                </a>
                <a id="print-voucher-from-modal" href="{{ URL($url . '/print-voucher/' . $model->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('operational/fields.print-voucher') }}">
                    <i class="fa fa-print"></i> {{ trans('operational/fields.print-voucher') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('script')
@parent
<script type="text/javascript">
var dataIndexDetail = {{ $dataIndexDetail }};
var dataIndexUnit = {{ $dataIndexUnit }};

$(document).on('ready', function() {
/** UPPROVED MESSAGE **/
@if(Session::has('approvedMessage'))
    $('#modal-approved-message').modal('show');
    $('#print-from-modal').on('click', function(event) {
        event.preventDefault();
        window.open('{{ URL($url . '/print-pdf/' . $model->resi_header_id) }}', '_blank');
        window.location = '{{ URL($url) }}';
    });
@endif

    /** HEADER **/
    $(".remove-pickup-request").on('click', removePickupRequest);
    $("#datatables-lov-pickup-request").dataTable({"pagelength" : 10, "lengthChange": false});
    $('#datatables-lov-pickup-request tbody').on('click', 'tr', selectPickupRequest);

    $(".remove-customer").on('click', removeCustomer);
    $("#datatables-lov-customer").dataTable(
        {
            "pagelength" : 10, 
            "lengthChange": false,
            "columnDefs": [
                { "searchable": false, "targets": [0,2,4] }
              ]
        }
    );
    $('#datatables-lov-customer tbody').on('click', 'tr', selectCustomer);

    $(".remove-customer-receiver").on('click', removeCustomerReceiver);
    $("#datatables-lov-customer-receiver").dataTable(
        {
            "pagelength" : 10, 
            "lengthChange": false,
            "columnDefs": [
                { "searchable": false, "targets": [0,2,4] }
              ]
        }
    );
    $('#datatables-lov-customer-receiver tbody').on('click', 'tr', selectCustomerReceiver);

    $("#kodeRute").on('keyup', keyupKodeRute).autocomplete(autocompleteKodeRute).autocomplete( "instance" )._renderItem = renderAutocompleteKodeRute;

    /** LINE DETAIL **/
    $('.add-line-detail').on('click', addLineDetail);
    $('.edit-line-detail').on('click', editLineDetail);

    $('#qtyWeight').on('keyup', changeWeight);
    $('#weight').on('keyup', changeWeight);

    $('#qtyVolume').on('keyup', calculateVolume);
    $('#dimensionL').on('keyup', calculateVolume);
    $('#dimensionW').on('keyup', calculateVolume);
    $('#dimensionH').on('keyup', calculateVolume);

    $('#add-line-volume').on('click', addLineVolume);
    $('.action-remove-line-volume').on('click', deleteLineVolume);

    $('#save-line-detail').on('click', saveLineDetail);
    $('.delete-line-detail').on('click', deleteLineDetail);
    $('#cancel-save-line-detail').on('click', cancleSaveLineDetail);
    $('#clear-lines-detail').on('click', clearLineDetail);

    /** LINE UNIT **/
    $('.add-line-unit').on('click', addLineUnit);
    $('.edit-line-unit').on('click', editLineUnit);

    $('#show-lov-unit').on('click', showLovUnit);
    $('#searchUnit').on('keyup', loadLovUnit);
    $('#table-lov-unit tbody').on('click', 'tr', selectUnit);

    $('#totalUnit').on('keyup', calculatePriceUnit);
    $('#save-line-unit').on('click', saveLineUnit);
    $('.delete-line-unit').on('click', deleteLineUnit);
    $('#cancel-save-line-unit').on('click', cancelSaveLineUnit);
    $('#clear-lines-unit').on('click', clearLineUnit);
    $('#negoPrice').on('keyup', calculateDiscountNego);

    /** APPROVE ADMIN **/
    $('[name="btn-approve"]').on('click', approveAdmin);

    /** SUBMIT FORM **/
    $('.btn-submit').on('click', function(event){
        $('.btn-submit').hide();
        $('#form-resi').submit();
    });


});

/** HEADER **/
var selectPickupRequest = function () {
    var dataPickupRequest = $(this).data('pickup-request');

    $('#pickupRequestId').val(dataPickupRequest.pickup_request_id);
    $('#pickupRequestNumber').val(dataPickupRequest.pickup_request_number);

    if (dataPickupRequest.customer_id) {
        $('#customerId').val(dataPickupRequest.customer_id);
        $('#customerName').val(dataPickupRequest.customer_name);
    }

    $('#senderName').val(dataPickupRequest.customer_name);
    $('#senderAddress').val(dataPickupRequest.address);
    $('#senderPhone').val(dataPickupRequest.phone_number);
    $('#itemNameHeader').val(dataPickupRequest.item_name);

    $('#modal-lov-pickup-request').modal('hide');
};

var removePickupRequest = function() {
    $('#pickupRequestId').val('');
    $('#pickupRequestNumber').val('');
};

var selectCustomer = function () {
    var dataCustomer = $(this).data('customer');

    $('#customerId').val(dataCustomer.customer_id);
    $('#customerName').val(dataCustomer.customer_name);
    $('#senderName').val(dataCustomer.customer_name);
    $('#senderAddress').val(dataCustomer.address);
    $('#senderPhone').val(dataCustomer.phone_number);
    $('#modal-lov-customer').modal("hide");
};

var removeCustomer = function() {
    $('#customerId').val('');
    $('#customerName').val('');
};

var selectCustomerReceiver = function () {
    var dataCustomer = $(this).data('customer');

    $('#customerReceiverId').val(dataCustomer.customer_id);
    $('#customerReceiverName').val(dataCustomer.customer_name);
    $('#receiverName').val(dataCustomer.customer_name);
    $('#receiverAddress').val(dataCustomer.address);
    $('#receiverPhone').val(dataCustomer.phone_number);
    $('#modal-lov-customer-receiver').modal('hide');
};

var removeCustomerReceiver = function() {
    $('#customerReceiverId').val('');
    $('#customerReceiverName').val('');
};

var keyupKodeRute = function() {
    if ($('#kodeRute').val() == '') {
        $('#kodeRute').val('');
        $('#routeId').val('');
        $('#kotaAsal').val('');
        $('#kotaTujuan').val('');
    }
};

var autocompleteKodeRute = {
    source: "{{ URL($url.'/get-json-route') }}",
    minLength: 1,
    focus: function(event, ui) {
        $("#kodeRute").val(ui.item.resi_number);
        return false;
    },
    select: function(event, ui) {
        $('#kodeRute').val(ui.item.route_code);
        $('#routeId').val(ui.item.route_id);
        $('#kotaAsal').val(ui.item.city_start_name);
        $('#kotaTujuan').val(ui.item.city_end_name);
        $('#priceKg').val(ui.item.rate_kg);
        $('#priceM3').val(ui.item.rate_m3);
        $('#minimumRates').val(ui.item.minimum_rates);

        updateRouteLineDetail();

        $.ajax({
            url: '{{ URL($url . '/get-json-item-unit-rute') }}' + '/' + ui.item.route_id,
            method: 'GET'
        }).done(function(result) {
            updateRouteLineUnit(result);
            calculateTotal();

            $('#itemNameHeader').focus();
        });

        return false;
    }
};

var renderAutocompleteKodeRute = function(ul, item) {
  return $( "<li>" )
    .append(
        '<div>\
        <b>' + item.route_code + '</b><br/>\
        <small>' + item.city_start_name + ' - ' + item.city_end_name + '</small>\
        </div>'
    )
    .appendTo( ul );
};

var updateRouteLineDetail = function() {
    var priceKg = currencyToInt($('#priceKg').val());
    var priceM3 = currencyToInt($('#priceM3').val());
    var minimumRates = currencyToInt($('#minimumRates').val());

    $('#table-line-detail tbody tr').each(function() {
        var weight = currencyToFloat($(this).find('[name="totalWeightLine[]"]').val());
        var volume = currencyToFloat($(this).find('[name="volume[]"]').val());
        var priceWeight = weight * priceKg;
        var priceVolume = volume * priceM3;
        var totalPrice = priceWeight > priceVolume ? priceWeight : priceVolume;

        $(this).find('td:nth-child(5)').html(priceWeight.formatMoney(0));
        $(this).find('td:nth-child(6)').html(priceVolume.formatMoney(0));
        $(this).find('td:nth-child(7)').html(totalPrice.formatMoney(0));
        $(this).find('[name="priceWeight[]"]').val(priceWeight);
        $(this).find('[name="priceVolume[]"]').val(priceVolume);
        $(this).find('[name="totalPrice[]"]').val(totalPrice);
    });
};

var updateRouteLineUnit = function(dataUnit) {
    $('#table-line-unit tbody tr').each(function() {
        var commodityId = $(this).find('[name="commodityId[]"]').val();
        var totalUnit = currencyToInt($(this).find('[name="totalUnit[]"]').val());
        var filterUnit = dataUnit.filter(function(unit) { return unit.commodity_id == commodityId; });
        var priceUnit = filterUnit.length != 0 ? currencyToInt(filterUnit[0].delivery_rate) : 0;
        var totalPriceUnit = totalUnit * priceUnit;

        $(this).find('td:nth-child(3)').html(priceUnit.formatMoney(0));
        $(this).find('td:nth-child(4)').html(totalPriceUnit.formatMoney(0));
        $(this).find('[name="priceUnit[]"]').val(priceUnit);
        $(this).find('[name="totalPriceUnit[]"]').val(totalPriceUnit);
    });
};

/** LINE DETAIL **/
var addLineDetail = function() {
    if ($('#routeId').val() == 0) {
        $('#modal-alert').find('.alert-message').html('{{ trans('operational/fields.choose-route-alert') }}');
        $('#modal-alert').modal('show');
        return;
    }

    clearFormLineDetail();
    $('#title-modal-line-detail').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line-detail').html('{{ trans('shared/common.add') }}');
    $('#modal-line-detail').modal("show");
};

var clearLineDetail = function() {
    $('#table-line-detail tbody').html('');
    calculateTotal();
};

var editLineDetail = function() {
    clearFormLineDetail();

    var $tr = $(this).parent().parent();
    var dataIndexFormDetail = $tr.data('index-detail');
    var lineDetailId = $tr.find('[name="lineDetailId[]"]').val();
    var itemName = $tr.find('[name="itemName[]"]').val();
    var coly = $tr.find('[name="coly[]"]').val();
    var qtyWeight = $tr.find('[name="qtyWeight[]"]').val();
    var weight = $tr.find('[name="weight[]"]').val();
    var totalWeightLine = $tr.find('[name="totalWeightLine[]"]').val();
    var lineVolume = $tr.find('[name="lineVolume[]"]').val() != '' ? JSON.parse($tr.find('[name="lineVolume[]"]').val()) : [];
    var volume = $tr.find('[name="volume[]"]').val();
    var priceWeight = $tr.find('[name="priceWeight[]"]').val();
    var priceVolume = $tr.find('[name="priceVolume[]"]').val();
    var totalPrice = $tr.find('[name="totalPrice[]"]').val();

    $('#dataIndexFormDetail').val(dataIndexFormDetail);
    $('#lineDetailId').val(lineDetailId);
    $('#itemName').val(itemName);
    $('#coly').val(coly);
    $('#qtyWeight').val(qtyWeight);
    $('#weight').val(weight);
    $('#totalWeightLine').val(totalWeightLine);

    lineVolume.forEach(function(lineVolumeUnit) {
        $('#table-line-volume tbody').append(
            '<tr>\
                <td class="text-right">' + parseInt(lineVolumeUnit.qty).formatMoney(0) + '</td>\
                <td class="text-right">' + parseInt(lineVolumeUnit.dimensionL).formatMoney(0) + ' x ' + parseInt(lineVolumeUnit.dimensionW).formatMoney(0) + ' x ' + parseInt(lineVolumeUnit.dimensionH).formatMoney(0) + '</td>\
                <td class="text-right">'+ parseFloat(lineVolumeUnit.volume).formatMoney(6) +'</td>\
                <td class="text-center">\
                    <a href="#" class="btn btn-xs btn-danger action-remove-line-volume"><i class="fa fa-remove"></i></a>\
                    <input type="hidden" name="qtyLineVolume[]" value="' + lineVolumeUnit.qty + '">\
                    <input type="hidden" name="dimensionLLineVolume[]" value="' + lineVolumeUnit.dimensionL + '">\
                    <input type="hidden" name="dimensionWLineVolume[]" value="' + lineVolumeUnit.dimensionW + '">\
                    <input type="hidden" name="dimensionHLineVolume[]" value="' + lineVolumeUnit.dimensionH + '">\
                    <input type="hidden" name="volumeLineVolume[]" value="' + lineVolumeUnit.volume + '">\
                </td>\
            </tr>'
        );

        $('.action-remove-line-volume').on('click', deleteLineVolume);
    });

    $('#totalVolumeLineAll').val(volume);
    $('#priceWeight').val(priceWeight);
    $('#priceVolume').val(priceVolume);
    $('#totalPriceDetail').val(totalPrice);

    $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line-detail').html('{{ trans('shared/common.edit') }}');
    $('#modal-line-detail').modal("show");
};

var deleteLineDetail = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var addLineVolume = function() {
    var dimensionL = $('#dimensionL').val() != '' ? currencyToInt($('#dimensionL').val()) : 0;
    var dimensionW = $('#dimensionW').val() != '' ? currencyToInt($('#dimensionW').val()) : 0;
    var dimensionH = $('#dimensionH').val() != '' ? currencyToInt($('#dimensionH').val()) : 0;
    var qtyVolume = $('#qtyVolume').val() != '' ? currencyToInt($('#qtyVolume').val()) : 1;
    var error = false;

    if (dimensionL <= 0) {
        $('#dimensionL').parent().addClass('has-error');
        error = true;
    } else {
        $('#dimensionL').parent().removeClass('has-error');
    }

    if (dimensionW <= 0) {
        $('#dimensionW').parent().addClass('has-error');
        error = true;
    } else {
        $('#dimensionW').parent().removeClass('has-error');
    }

    if (dimensionH <= 0) {
        $('#dimensionH').parent().addClass('has-error');
        error = true;
    } else {
        $('#dimensionH').parent().removeClass('has-error');
    }

    if (error) {
        return;
    }

    var convertM3 = 1000000;
    var volume = qtyVolume * dimensionL * dimensionW * dimensionH / convertM3;

    $('#table-line-volume tbody').append(
        '<tr>\
            <td class="text-right">' + parseInt(qtyVolume).formatMoney(0) + '</td>\
            <td class="text-right">' + dimensionL.formatMoney(0) + ' x ' + dimensionW.formatMoney(0) + ' x ' + dimensionH.formatMoney(0) + '</td>\
            <td class="text-right">'+ volume.formatMoney(6) +'</td>\
            <td class="text-center">\
                <a href="#" class="btn btn-xs btn-danger action-remove-line-volume"><i class="fa fa-remove"></i></a>\
                <input type="hidden" name="qtyLineVolume[]" value="' + qtyVolume + '">\
                <input type="hidden" name="dimensionLLineVolume[]" value="' + dimensionL + '">\
                <input type="hidden" name="dimensionWLineVolume[]" value="' + dimensionW + '">\
                <input type="hidden" name="dimensionHLineVolume[]" value="' + dimensionH + '">\
                <input type="hidden" name="volumeLineVolume[]" value="' + volume + '">\
            </td>\
        </tr>'
    )

    changeTotalVolumeLine();

    $('#qtyVolume').val('');
    $('#dimensionW').val('');
    $('#dimensionH').val('');
    $('#dimensionL').val('');
    $('#totalVolumeLine').val('');

    $('.action-remove-line-volume').on('click', deleteLineVolume);
};

var deleteLineVolume = function() {
    $(this).parent().parent().remove();
    changeTotalVolumeLine();
};

var changeWeight = function(){
    calculateWeight();
    calculatePriceWeight();
    calculatePriceLineDetail();
};

var changeTotalVolumeLine = function(){
    calculateTotalVolumeLine();
    calculatePriceVolumeLine();
    calculatePriceLineDetail();
};

var calculateTotalVolumeLine = function(){
    var totalVolume = 0;
    $('#table-line-volume tbody tr').each(function() {
        var volume = currencyToFloat($(this).find('[name="volumeLineVolume[]"]').val());
        totalVolume += volume;
    });

    $('#totalVolumeLineAll').val(totalVolume).autoNumeric('update', {mDec: 6});
};

var calculatePriceWeight = function() {
    var totalWeightLine = currencyToFloat($('#totalWeightLine').val()); 
    var priceKg = currencyToInt($('#priceKg').val());
    var priceWeight = totalWeightLine * priceKg;

    $('#priceWeight').val(priceWeight).autoNumeric('update', {mDec: 0});
};

var calculatePriceVolumeLine = function() {
    var totalVolume = currencyToFloat($('#totalVolumeLineAll').val());
    var priceM3 = currencyToInt($('#priceM3').val());

    $('#priceVolume').val(totalVolume * priceM3).autoNumeric('update', {mDec: 0});
};

var calculatePriceLineDetail = function() {
    var priceWeight = currencyToInt($('#priceWeight').val());
    var priceVolume = currencyToInt($('#priceVolume').val());
    var priceLineDetail = priceWeight > priceVolume ? priceWeight : priceVolume;

    $('#totalPriceDetail').val(priceLineDetail);
    $('#totalPriceDetail').autoNumeric('update', {mDec: 0});
};

var saveLineDetail = function() {
    var dataIndexFormDetail = $('#dataIndexFormDetail').val();
    var itemName = $('#itemName').val();
    var coly = $('#coly').val();
    var qtyWeight = $('#qtyWeight').val();
    var weight = $('#weight').val();
    var totalWeightLine = $('#totalWeightLine').val();
    var volume = $('#totalVolumeLineAll').val();
    var priceWeight = $('#priceWeight').val();
    var priceVolume = $('#priceVolume').val();
    var price = $('#totalPriceDetail').val();
    var error = false;

    if (coly == '' || coly <= 0) {
        $('#coly').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#coly').parent().parent().removeClass('has-error');
    }

    if (error) {
        return;
    }

    var lineVolume = [];
    $('#table-line-volume tbody tr').each(function() {
        lineVolume.push({
            qty: $(this).find('[name="qtyLineVolume[]"]').val(),
            dimensionL: $(this).find('[name="dimensionLLineVolume[]"]').val(),
            dimensionW: $(this).find('[name="dimensionWLineVolume[]"]').val(),
            dimensionH: $(this).find('[name="dimensionHLineVolume[]"]').val(),
            volume: $(this).find('[name="volumeLineVolume[]"]').val()
        })
    });

    var htmlTr = '<td >' + itemName + '</td>\
                    <td class="text-right">' + coly + '</td>\
                    <td class="text-right">' + totalWeightLine + '</td>\
                    <td class="text-right">' + volume + '</td>\
                    <td class="text-right">' + priceWeight + '</td>\
                    <td class="text-right">' + priceVolume + '</td>\
                    <td class="text-right">' + price + '</td>\
                    <td class="text-center">\
                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line-detail" ><i class="fa fa-pencil"></i></a> \
                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line-detail" ><i class="fa fa-remove"></i></a>\
                        <input type="hidden" name="lineDetailId[]" value="">\
                        <input type="hidden" name="itemName[]" value="' + itemName + '">\
                        <input type="hidden" name="coly[]" value="' + coly + '">\
                        <input type="hidden" name="qtyWeight[]" value="' + qtyWeight + '">\
                        <input type="hidden" name="weight[]" value="' + weight + '">\
                        <input type="hidden" name="totalWeightLine[]" value="' + totalWeightLine + '">\
                        <input type="hidden" name="volume[]" value="' + volume + '">\
                        <input type="hidden" name="priceWeight[]" value="' + priceWeight + '">\
                        <input type="hidden" name="priceVolume[]" value="' + priceVolume + '">\
                        <input type="hidden" name="totalPrice[]" value="' + price + '">\
                        <input type="hidden" name="lineVolume[]" value=\'' + JSON.stringify(lineVolume) + '\'>\
                    </td>';

    if (dataIndexFormDetail != '') {
        $('tr[data-index-detail="' + dataIndexFormDetail + '"]').html(htmlTr);
        dataIndexDetail++;
    } else {
        $('#table-line-detail tbody').append(
            '<tr data-index-detail="' + dataIndexDetail + '">' + htmlTr + '</tr>'
        );
        dataIndexDetail++;
    }

    $('.edit-line-detail').on('click', editLineDetail);
    $('.delete-line-detail').on('click', deleteLineDetail);

    calculateTotal();

    dataIndexDetail++;
    $('#modal-line-detail').modal("hide");
};

var cancleSaveLineDetail = function() {
    $('#modal-line-detail').modal("hide");
};

var clearFormLineDetail = function() {
    $('#dataIndexFormDetail').val('');
    $('#itemName').val('');
    $('#coly').val('');
    $('#qtyWeight').val('');
    $('#weight').val('');
    $('#totalWeightLine').val('');
    $('#qtyVolume').val('');
    $('#dimensionL').val('');
    $('#dimensionW').val('');
    $('#dimensionH').val('');
    $('#totalVolumeLine').val('');
    $('#table-line-volume tbody').html('');
    $('#totalVolumeLineAll').val('');
    $('#priceWeight').val(0);
    $('#priceVolume').val(0);
    $('#totalPriceDetail').val(0);

    $('#coly').parent().parent().removeClass('has-error');
    $('#dimensionL').parent().removeClass('has-error');
    $('#dimensionW').parent().removeClass('has-error');
    $('#dimensionH').parent().removeClass('has-error');
};

/** LINE UNIT **/
var showLovUnit = function() {
    $('#searchUnit').val('');
    loadLovUnit(function() {
        $('#modal-lov-unit').modal('show');
    });
};

var xhrUnit;
var loadLovUnit = function(callback) {
    if(xhrUnit && xhrUnit.readyState != 4){
        xhrUnit.abort();
    }
    xhrUnit = $.ajax({
        url: '{{ URL($url . '/get-json-item-unit-rute') }}' + '/' + $('#routeId').val(),
        data: {search: $('#searchUnit').val()},
        success: function(data) {
            $('#table-lov-unit tbody').html('');
            data.slice(0, 10).forEach(function(item) {
                $('#table-lov-unit tbody').append(
                    '<tr style="cursor: pointer;" data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.commodity_name + '</td>\
                        <td class="text-right">' + parseInt(item.delivery_rate).formatMoney(0, '.', ',') + '</td>\
                        <td>' + (item.description || '') + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectUnit = function () {
    var dataUnit = $(this).data('json');

    $('#unitId').val(dataUnit.shipping_price_id);
    $('#priceUnit').val(dataUnit.delivery_rate);
    $('#priceUnit').autoNumeric('update', {mDec: 0});
    $('#commodityId').val(dataUnit.commodity_id);
    $('#itemNameUnit').val(dataUnit.commodity_name);
    $('#descriptionUnit').val(dataUnit.description);

    calculatePriceUnit();
    $('#modal-lov-unit').modal('hide');
};

var addLineUnit = function() {
    if ($('#routeId').val() == 0) {
        $('#modal-alert').find('.alert-message').html('{{ trans('operational/fields.choose-route-alert') }}');
        $('#modal-alert').modal('show');
        return;
    }

    clearFormLineUnit();

    $('#title-modal-line-unit').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line-unit').html('{{ trans('shared/common.add') }}');
    $('#modal-add-line-unit').modal('show');
};

var clearLineUnit = function() {
    $('#table-line-unit tbody').html('');
    calculateTotal();
};

var editLineUnit = function() {
    clearFormLineUnit();

    var $tr = $(this).parent().parent();
    var dataIndexFormUnit = $tr.data('index-unit');
    var commodityId = $tr.find('[name="commodityId[]"]').val();
    var itemNameUnit = $tr.find('[name="itemNameUnit[]"]').val();
    var totalUnit = $tr.find('[name="totalUnit[]"]').val();
    var priceUnit = $tr.find('[name="priceUnit[]"]').val();
    var totalPriceUnit = $tr.find('[name="totalPriceUnit[]"]').val();

    $('#dataIndexFormUnit').val(dataIndexFormUnit);
    $('#commodityId').val(commodityId);
    $('#itemNameUnit').val(itemNameUnit);
    $('#totalUnit').val(totalUnit);
    $('#priceUnit').val(priceUnit);
    $('#totalPriceUnit').val(totalPriceUnit);

    $('#title-modal-line-unit').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line-unit').html('{{ trans('shared/common.edit') }}');
    $('#modal-add-line-unit').modal("show");
};

var deleteLineUnit = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var cancelSaveLineUnit = function() {
    $('#modal-add-line-unit').modal("hide");
};

var saveLineUnit = function() {
    var dataIndexFormUnit = $('#dataIndexFormUnit').val();
    var unitId = $('#unitId').val();
    var commodityId = $('#commodityId').val();
    var itemNameUnit = $('#itemNameUnit').val();
    var priceUnit = $('#priceUnit').val();
    var totalUnit = $('#totalUnit').val();
    var totalPriceUnit = $('#totalPriceUnit').val();
    var error = false;

    if (totalUnit == '' || totalUnit <= 0) {
        $('#totalUnit').parent().parent().addClass('has-error');
        $('#totalUnit').parent().find('span.help-block').html('Total Unit is required');
        error = true;
    } else {
        $('#totalUnit').parent().parent().removeClass('has-error');
        $('#totalUnit').parent().find('span.help-block').html('');
    }

    if (!unitId) {
        $('#unitId').parent().parent().addClass('has-error');
        $('#unitId').parent().find('span.help-block').html('Item Unit is required');
        error = true;
    } else {
        $('#unitId').parent().parent().removeClass('has-error');
        $('#unitId').parent().find('span.help-block').html('');
    }

    if (error) {
        return;
    }

    var htmlTr = '<td >' + itemNameUnit + '</td>' +
        '<td class="text-right">' + totalUnit + '</td>' +
        '<td class="text-right">' + priceUnit + '</td>' +
        '<td class="text-right">' + totalPriceUnit + '</td>' +
        '<td class="text-center">' +
        '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line-unit" ><i class="fa fa-pencil"></i></a> ' +
        '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line-unit" ><i class="fa fa-remove"></i></a>' +
        '<input type="hidden" name="lineUnitId[]" value="">' +
        '<input type="hidden" name="unitId[]" value="' + unitId + '">' +
        '<input type="hidden" name="commodityId[]" value="' + commodityId + '">' +
        '<input type="hidden" name="itemNameUnit[]" value="' + itemNameUnit + '">' +
        '<input type="hidden" name="totalUnit[]" value="' + totalUnit + '">' +
        '<input type="hidden" name="priceUnit[]" value="' + priceUnit + '">' +
        '<input type="hidden" name="totalPriceUnit[]" value="' + totalPriceUnit + '">' +
        '</td>';

    if (dataIndexFormUnit != '') {
        $('tr[data-index-unit="' + dataIndexFormUnit + '"]').html(htmlTr);
        dataIndexUnit++;
    } else {
        $('#table-line-unit tbody').append(
            '<tr data-index-unit="' + dataIndexUnit + '">' + htmlTr + '</tr>'
        );
        dataIndexUnit++;
    }

    $('.edit-line-unit').on('click', editLineUnit);

    $('.delete-line-unit').on('click', function() {
        $(this).parent().parent().remove();
        calculateTotal();
    });

    dataIndexUnit++;
    calculateTotal();

    $('#modal-add-line-unit').modal("hide");
};

var clearFormLineUnit = function() {
    $('#dataIndexFormUnit').val('');
    $('#commodityId').val('');
    $('#itemNameUnit').val('');
    $('#totalUnit').val(0);
    $('#descriptionUnit').val('');
    $('#priceUnit').val(0);
    $('#totalPriceUnit').val(0);

    $('#totalUnit').parent().parent().parent().removeClass('has-error');
    $('#totalUnit').parent().parent().find('span.help-block').html('');
};

var calculatePriceUnit = function() {
    var priceUnit = currencyToInt($('#priceUnit').val()); 
    var totalUnit = currencyToInt($('#totalUnit').val()); 
    $('#totalPriceUnit').val(priceUnit * totalUnit);  
    $('#totalPriceUnit').autoNumeric('update', {mDec: 0});
};

/** APPROVE ADMIN **/
var approveAdmin = function(event) {
    if ($('#negoPrice').val() !== '') {
        event.preventDefault();
        $('#modal-alert').find('.alert-message').html('{{ trans('operational/fields.nego-price-must-empty') }}');
        $('#modal-alert').modal('show');
        return;
    }
};

/** PERHITUNGAN HARGA **/
var calculateWeight = function() {
    var qtyWeight = $('#qtyWeight').val() != '' ? currencyToInt($('#qtyWeight').val()) : 1;
    var weight = currencyToFloat($('#weight').val());
    var totalWeightLine = qtyWeight * weight;

    $('#totalWeightLine').val(totalWeightLine).autoNumeric('update', {mDec: 2});
};

var calculateVolume = function() {
    var qtyVolume = $('#qtyVolume').val() != '' ? currencyToInt($('#qtyVolume').val()) : 1;
    var dimensionL = currencyToInt($('#dimensionL').val());
    var dimensionW = currencyToInt($('#dimensionW').val());
    var dimensionH = currencyToInt($('#dimensionH').val());
    var convertM3 = 1000000;
    var totalVolumeLine = qtyVolume * dimensionL * dimensionW * dimensionH / convertM3;

    $('#totalVolumeLine').val(totalVolumeLine).autoNumeric('update', {mDec: 6});
};

var calculateDiscountNego = function() {
    var total = currencyToFloat($('#negoTotal').val());
    var negoPrice = $('#negoPrice').val() !== '' ? currencyToFloat($('#negoPrice').val()) : '';
    var negoDiscount = negoPrice !== '' ? total - negoPrice : '';

    $('#negoDiscount').val(negoDiscount);
    $('#negoDiscount').autoNumeric('update', {mDec: 0});
};

var calculateTotal = function() {
    /** hitung total line detail */
    var totalWeight = 0;
    var totalVolume = 0;
    var totalAmount = 0;
    var totalColy = 0;
    var discount = $('#discount').val() !== '' ? currencyToFloat($('#discount').val()) : 0;
    var minimumRates = $('#minimumRates').val() !== '' ? currencyToInt($('#minimumRates').val()) : 0;

    $('#table-line-detail tbody tr').each(function (i, row) {
        var coly = currencyToInt($(row).find('[name="coly[]"]').val());
        var weight = currencyToFloat($(row).find('[name="totalWeightLine[]"]').val());
        var volume = currencyToFloat($(row).find('[name="volume[]"]').val());
        var priceWeight = currencyToInt($(row).find('[name="priceWeight[]"]').val());
        var priceVolume = currencyToInt($(row).find('[name="priceVolume[]"]').val());
        var totalPrice = currencyToInt($(row).find('[name="totalPrice[]"]').val());

        if (priceWeight >= totalPrice) {
            totalWeight += weight;
        } else if (priceVolume >= totalPrice) {
            totalVolume += volume;
        }

        totalColy += coly;
        totalAmount += totalPrice;
    });

    /** hitung total line unit */
    $('#table-line-unit tbody tr').each(function (i, row) {
        var totalPriceUnit = currencyToFloat($(row).find('[name="totalPriceUnit[]"]').val());
        var totalUnit = currencyToFloat($(row).find('[name="totalUnit[]"]').val());

        totalColy += totalUnit;
        totalAmount += totalPriceUnit;
    });

    var pembulatan = 100;
    totalAmount = totalAmount > minimumRates ? totalAmount : minimumRates;
    totalAmount = Math.floor(totalAmount / pembulatan) * pembulatan;
    var total = totalAmount - discount;

    $('#totalColy').val(totalColy).autoNumeric('update', {mDec: 0});
    $('#totalWeight').val(totalWeight).autoNumeric('update', {mDec: 2});
    $('#totalVolume').val(totalVolume).autoNumeric('update', {mDec: 6});
    $('#totalAmount').val(totalAmount).autoNumeric('update', {mDec: 0});
    $('#total').val(total).autoNumeric('update', {mDec: 0});
    $('#negoTotal').val(total).autoNumeric('update', {mDec: 0});

    calculateDiscountNego();
};
</script>
@endsection
