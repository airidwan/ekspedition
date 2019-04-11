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
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
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
                                    <?php $resiDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group">
                                        <label for="resiDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="resiDate" name="resiDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $resiDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $pickupRequestNumber = $model->pickupRequest !== null ? $model->pickupRequest->pickup_request_number : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-request') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" value="{{ $pickupRequestNumber }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $customer = $model->customer()->first();
                                    $customerName = $customer !== null ? $customer->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customerName }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderName" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderName" name="senderName" value="{{ $model->sender_name }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderAddress" name="senderAddress" value="{{ $model->sender_address }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderPhone" name="senderPhone" value="{{ $model->sender_phone }}" disabled />
                                        </div>
                                    </div>
                                    <?php
                                    $customerReceiverName = !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerReceiverName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerReceiverName" name="customerReceiverName" value="{{ $customerReceiverName }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverName" name="receiverName" value="{{ $model->receiver_name }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverAddress" name="receiverAddress" value="{{ $model->receiver_address }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverPhone" name="receiverPhone" value="{{ $model->receiver_phone }}" disabled />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <?php
                                    $routeId = count($errors) > 0 ? intval(old('routeId')) : $model->route_id;
                                    $route = MasterRoute::find($routeId);
                                    $kodeRute = $route !== null ? $route->route_code : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="kodeRute" class="col-sm-4 control-label">{{ trans('operational/fields.kode-rute') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kodeRute" name="kodeRute" value="{{ $kodeRute }}" disabled />
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
                                    <input type="hidden" name="kotaTujuan">
                                    <div class="form-group">
                                        <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaTujuan" name="kotaTujuan" value="{{ $namaKotaTujuan }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalColy" name="totalColy" value="{{ $model->totalColy() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalWeight" class="col-sm-4 control-label">{{ trans('operational/fields.total-weight') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control decimal' id="totalWeight" name="totalWeight" value="{{ $model->totalWeight() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalVolume" class="col-sm-4 control-label">{{ trans('operational/fields.total-volume') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control decimal6' id="totalVolume" name="totalVolume" value="{{ $model->totalVolume() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('operational/fields.total-amount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalAmount" name="totalAmount" value="{{ $model->totalAmount() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount" class="col-sm-4 control-label">{{ trans('operational/fields.discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="discount" name="discount" data-v-min="-99999999999" value="{{ $model->discount }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('shared/common.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->total() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="status" name="status" value="{{ $model->status }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.insurance') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $insurance = $model->insurance; ?>
                                                <input type="checkbox" id="insurance" name="insurance" value="1" {{ $insurance ? 'checked' : '' }} disabled />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group">
                                        <label for="itemNameHeader" class="col-sm-12 control-label">{{ trans('operational/fields.item-name') }}</label>
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="itemNameHeader" name="itemNameHeader" rows="3" disabled />{{ $model->item_name }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-12 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="3" disabled />{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-12 control-label">{{ trans('shared/common.type') }}</label>
                                        <?php $type = $model->type; ?>
                                        <div class="col-sm-12">
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="type" id="type-reguler" value="{{ TransactionResiHeader::REGULER }}" {{ $type == TransactionResiHeader::REGULER ? 'checked' : '' }} disabled /> {{ trans('operational/fields.reguler') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="type" id="type-carter" value="{{ TransactionResiHeader::CARTER }}" {{ $type == TransactionResiHeader::CARTER ? 'checked' : '' }} disabled /> {{ trans('operational/fields.carter') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-12 control-label">{{ trans('operational/fields.payment') }}</label>
                                        <?php $payment = $model->payment; ?>
                                        <div class="col-sm-12">
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-cash" value="{{ TransactionResiHeader::CASH }}" {{ $payment == TransactionResiHeader::CASH ? 'checked' : '' }} disabled /> {{ trans('operational/fields.cash') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-bill-to-sender" value="{{ TransactionResiHeader::BILL_TO_SENDER }}" {{ $payment == TransactionResiHeader::BILL_TO_SENDER ? 'checked' : '' }} disabled /> {{ trans('operational/fields.bill-to-sender') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-bill-to-reciever" value="{{ TransactionResiHeader::BILL_TO_RECIEVER }}" {{ $payment == TransactionResiHeader::BILL_TO_RECIEVER ? 'checked' : '' }} disabled /> {{ trans('operational/fields.bill-to-receiver') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLineDetails">
                                <div class="col-sm-12 portlets">
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->line()->whereNull('unit_id')->get() as $line)
                                                    <tr>
                                                        <td > {{ $line->item_name }} </td>
                                                        <td class="text-right"> {{ number_format($line->coly) }} </td>
                                                        <td class="text-right"> {{ number_format($line->weight, 2) }} </td>
                                                        <td class="text-right"> {{ number_format($line->totalVolume(), 6) }} </td>
                                                        <td class="text-right"> {{ number_format($line->price_weight) }} </td>
                                                        <td class="text-right"> {{ number_format($line->totalPriceVolume()) }} </td>
                                                        <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabLineUnits">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-unit" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-unit') }}</th>
                                                    <th>{{ trans('operational/fields.price-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->line()->whereNotNull('unit_id')->get() as $line)
                                                    <tr>
                                                        <td > {{ $line->item_name }} </td>
                                                        <td class="text-right"> {{ number_format($line->total_unit) }} </td>
                                                        <td class="text-right"> {{ number_format($line->total_price / $line->total_unit) }} </td>
                                                        <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                    </tr>
                                                @endforeach
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
                                            <input type="text" class="form-control currency" id="negoTotal" name="negoTotal" value="{{ $model->total() }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="negoPrice" class="col-sm-4 control-label">{{ trans('operational/fields.nego-price') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="negoPrice" name="negoPrice" value="{{ count($errors) > 0 ? str_replace(',', '', old('negoPrice')) : $negoPrice }}" disabled />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="negoDiscount" class="col-sm-4 control-label">{{ trans('operational/fields.discount') }}</label>
                                        <div class="col-sm-8">
                                            <?php $negoDiscount = !empty($negoPrice) ? $model->total() - $negoPrice : ''; ?>
                                            <input type="text" class="form-control currency" id="negoDiscount" name="negoDiscount" data-v-min="-99999999999" value="{{ $negoDiscount }}" disabled />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="requestedNote" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="requestedNote" name="requestedNote" rows="4" disabled />{{ $requestedNote }} </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                           <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>

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