<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.resi-outstanding'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.resi-outstanding') }}</h2>
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
                                <label for="customer" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}" data-role="tagsinput">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customer" name="customer" value="{{ !empty($filters['customer']) ? $filters['customer'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sender" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="sender" name="sender" value="{{ !empty($filters['sender']) ? $filters['sender'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiver" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiver" name="receiver" value="{{ !empty($filters['receiver']) ? $filters['receiver'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="route" class="col-sm-4 control-label">{{ trans('operational/fields.route') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="route">
                                        <option value="">ALL</option>
                                        @foreach($optionRoute as $route)
                                            <option value="{{ $route->route_id }}" {{ !empty($filters['route']) && $filters['route'] == $route->route_id ? 'selected' : '' }}>{{ $route->route_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="payment">
                                        <option value="">ALL</option>
                                        @foreach($optionPayment as $payment)
                                            <option value="{{ $payment }}" {{ !empty($filters['payment']) && $filters['payment'] == $payment ? 'selected' : '' }}>{{ $payment }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">{{ trans('shared/common.date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateFrom']) ? $filters['dateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateTo']) ? $filters['dateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
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
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>
                                    {{ trans('operational/fields.resi-number') }}<hr/>
                                    {{ trans('operational/fields.date') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.customer') }}<hr/>
                                    {{ trans('operational/fields.sender') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.customer') }}<hr/>
                                    {{ trans('operational/fields.receiver') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.route') }}<hr/>
                                    {{ trans('operational/fields.payment') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.item-name') }}<hr/>
                                    {{ trans('operational/fields.item-unit') }}
                                </th>
                                <th>{{ trans('operational/fields.total-coly') }}</th>
                                <th>{{ trans('operational/fields.coly-received') }}</th>
                                <th>{{ trans('operational/fields.coly-taken') }}</th>
                                <th>{{ trans('operational/fields.coly-remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $modelResi = TransactionResiHeader::find($model->resi_header_id);
                            $resiDate = !empty($modelResi->created_date) ? new \DateTime($modelResi->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $modelResi->resi_number }}<hr/>
                                    {{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}
                                </td>
                                <td>
                                    {{ !empty($modelResi->customer) ? $modelResi->customer->customer_name : '' }}<hr/>
                                    {{ $modelResi->sender_name }}
                                </td>
                                <td>
                                    {{ !empty($modelResi->customerReceiver) ? $modelResi->customerReceiver->customer_name : '' }}<hr/>
                                    {{ $modelResi->receiver_name }}
                                </td>
                                <td class="text-center">
                                    {{ $modelResi->route !== null ? $modelResi->route->route_code : '' }}<hr/>
                                    {{ $modelResi->getSingkatanPayment() }}
                                </td>
                                <td>
                                    {{ $modelResi->itemName() }}<hr/>
                                    {{ $modelResi->itemUnit() }}
                                </td>
                                <td class="text-right">{{ number_format($modelResi->totalColy()) }}</td>
                                <td class="text-right">{{ number_format($model->coly_received) }}</td>
                                <td class="text-right">{{ number_format($model->coly_taken) }}</td>
                                <td class="text-right">{{ number_format($modelResi->totalColy() - $model->coly_received - $model->coly_taken) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
