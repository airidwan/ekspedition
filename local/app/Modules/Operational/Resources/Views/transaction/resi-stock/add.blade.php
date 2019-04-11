<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.resi-stock'))


@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ $title }}  {{  trans('operational/menu.resi-stock') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $modelStock->stock_resi_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabDeliveryReady" data-toggle="tab">{{ trans('operational/fields.ready-delivery') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineDetails" data-toggle="tab">{{ trans('operational/fields.line-detail') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineUnits" data-toggle="tab">{{ trans('operational/fields.line-unit') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <?php
                            $nego = $model->nego()->whereNull('approved')->first();
                            $negoPrice = $nego !== null ? $nego->nego_price : '';
                            $requestedNote = $nego !== null ? $nego->requested_note : '';
                            ?>
                            <div class="tab-pane fade active in" id="tabDeliveryReady">
                                <div class="col-sm-6 portlets">
                                     <div class="form-group">
                                        <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                        <div class="col-sm-8">
                                                <input type="text" class="form-control" id="receiverName" name="receiverName" value="{{ $model->receiver_name }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverAddress" name="receiverAddress" value="{{ $model->receiver_address }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverPhone" name="receiverPhone" value="{{ $model->receiver_phone }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="totalColy" name="totalColy" value="{{ $model->totalColy() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalReceipt" class="col-sm-4 control-label">{{ trans('operational/fields.total-receipt') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="totalReceipt" name="totalReceipt" value="{{ $model->totalReceipt() }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('ready') ? 'has-error' : '' }}">
                                        <label class="col-sm-4 control-label">{{ trans('operational/fields.is-ready') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $ready = count($errors) > 0 ? old('ready') : $modelStock->is_ready_delivery; ?>
                                                <input type="checkbox" id="ready" name="ready" value="TRUE" {{ $ready == 'TRUE' ? 'checked' : '' }} > Ready to Delivery
                                            </label>
                                        </div>
                                    </div>
                                    <?php 
                                        $areaId = !empty($model->delivery_area_id) ? $model->delivery_area_id : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('deliveryArea') ? 'has-error' : '' }}">
                                        <label for="deliveryArea" class="col-sm-4 control-label">{{ trans('operational/fields.area-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                                <select class="form-control" name="deliveryArea" id="deliveryArea" >
                                                    <option value="">{{ trans('shared/common.please-select') }}</option>
                                                    @foreach($optionArea as $area)
                                                    <option value="{{ $area->delivery_area_id }}" {{ $area->delivery_area_id == $areaId ? 'selected' : '' }}>{{ $area->delivery_area_name }}</option>
                                                    @endforeach
                                                </select> 
                                                @if($errors->has('deliveryArea'))
                                                <span class="help-block">{{ $errors->first('deliveryArea') }}</span>
                                                @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="note" name="note" rows="3">{{ count($errors) > 0 ? old('note') : $model->wdl_note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
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
                                    $customer = $model->customer()->first();
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customer !== null ? $customer->customer_name : '' }}" disabled>
                                                <span class="btn input-group-addon" data-modal="modal-lov-customer"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderName" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="senderName" name="senderName" value="{{ $model->sender_name }}" disabled>
                                                <span class="btn input-group-addon" data-modal="modal-lov-sender"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderAddress" name="senderAddress" value="{{ count($errors) > 0 ? old('senderAddress') : $model->sender_address }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderPhone" name="senderPhone" value="{{ $model->sender_phone }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="receiverName" name="receiverName" value="{{ $model->receiver_name }}" disabled>
                                                <span class="btn input-group-addon" data-modal="modal-lov-receiver"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverAddress" name="receiverAddress" value="{{ $model->receiver_address }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverPhone" class="col-sm-4 control-label">{{ trans('operational/fields.phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverPhone" name="receiverPhone" value="{{ $model->receiver_phone }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <?php $route = $model->route()->first(); ?>
                                    <div class="form-group">
                                        <label for="kodeRute" class="col-sm-4 control-label">{{ trans('operational/fields.kode-rute') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="kodeRute" name="kodeRute" value="{{ $route !== null ? $route->route_code : '' }}" disabled>
                                                <span class="btn input-group-addon" data-modal="modal-lov-kode-rute"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $kotaAsal = $route !== null ? $route->cityStart()->first() : null; ?>
                                    <div class="form-group">
                                        <label for="kotaAsal" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaAsal" name="kotaAsal" value="{{ $kotaAsal !== null ? $kotaAsal->city_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <?php $kotaTujuan = $route !== null ? $route->cityEnd()->first() : null; ?>
                                    <input type="hidden" name="kotaTujuan">
                                    <div class="form-group">
                                        <label for="kotaTujuan" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan-transit') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kotaTujuan" name="kotaTujuan" value="{{ $kotaTujuan !== null ? $kotaTujuan->city_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalWeight" class="col-sm-4 control-label">{{ trans('operational/fields.total-weight') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control decimal' id="totalWeight" name="totalWeight" value="{{ $model->totalWeight() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalVolume" class="col-sm-4 control-label">{{ trans('operational/fields.total-volume') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control decimal6' id="totalVolume" name="totalVolume" value="{{ $model->totalVolume() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('operational/fields.total-amount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalAmount" name="totalAmount" value="{{ $model->totalAmount() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount" class="col-sm-4 control-label">{{ trans('operational/fields.discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="discount" name="discount" data-v-min="-99999999999" value="{{ $model->discount }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('shared/common.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->total() }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group">
                                        <label for="itemNameHeader" class="col-sm-12 control-label">{{ trans('operational/fields.item-name') }}</label>
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="itemNameHeader" name="itemNameHeader" rows="3" disabled>{{ count($errors) > 0 ? old('itemNameHeader') : $model->item_name }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-12 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="3" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-12 control-label">{{ trans('operational/fields.payment') }}</label>
                                        <div class="col-sm-12">
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-cash" value="{{ TransactionResiHeader::CASH }}" {{ $model->payment == TransactionResiHeader::CASH ? 'checked' : '' }} disabled> {{ trans('operational/fields.cash') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-bill-to-sender" value="{{ TransactionResiHeader::BILL_TO_SENDER }}" {{ $model->payment == TransactionResiHeader::BILL_TO_SENDER ? 'checked' : '' }} disabled> {{ trans('operational/fields.bill-to-sender') }}
                                            </label>
                                            <label class="radio-inline iradio">
                                                <input type="radio" name="payment" id="payment-bill-to-reciever" value="{{ TransactionResiHeader::BILL_TO_RECIEVER }}" {{ $model->payment == TransactionResiHeader::BILL_TO_RECIEVER ? 'checked' : '' }} disabled> {{ trans('operational/fields.bill-to-receiver') }}
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
                                                    <th>{{ trans('operational/fields.dimension') }}</th>
                                                    <th>{{ trans('operational/fields.volume') }}</th>
                                                    <th>{{ trans('operational/fields.price-weight') }}</th>
                                                    <th>{{ trans('operational/fields.price-volume') }}</th>
                                                    <th>{{ trans('operational/fields.price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lineDetail()->get() as $line)
                                                <tr>
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->coly) }} </td>
                                                    <td class="text-right"> {{ number_format($line->weight, 2) }} </td>
                                                    <td class="text-right"> {{ number_format($line->dimension_long) }} x {{ number_format($line->dimension_width) }} x {{ number_format($line->dimension_height) }} </td>
                                                    <td class="text-right"> {{ number_format($line->volume, 6) }} </td>
                                                    <td class="text-right"> {{ number_format($line->price_weight) }} </td>
                                                    <td class="text-right"> {{ number_format($line->price_volume) }} </td>
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
                                                @foreach($model->lineUnit()->get() as $line)
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                           <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(Gate::check('access', [$resource, 'update']) )
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.save') }}
                                </button>
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
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#deliveryArea').select2();
    });
</script>
@endsection
