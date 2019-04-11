<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.approve-nego-resi'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.approve-nego-resi') }}</h2>
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
                        <div class="col-sm-6 portlets">
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
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
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
                                <th>{{ trans('operational/fields.customer') }}</th>
                                <th>
                                    {{ trans('operational/fields.sender') }}<hr/>
                                    {{ trans('operational/fields.address') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.receiver') }}<hr/>
                                    {{ trans('operational/fields.address') }}
                                </th>
                                <th>{{ trans('operational/fields.route') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>{{ trans('operational/fields.coly') }}</th>
                                <th>
                                    {{ trans('operational/fields.total-amount') }}<hr/>
                                    {{ trans('operational/fields.discount') }}<hr/>
                                    {{ trans('shared/common.total') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.nego-price') }}<hr/>
                                    {{ trans('operational/fields.discount') }}
                                </th>
                                <th>{{ trans('operational/fields.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="50px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = TransactionResiHeader::find($model->resi_header_id);
                            $resiDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            $customer = $model->customer()->first();
                            $route = $model->route()->first();
                            $activeNego = $model->activeNego();
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->resi_number }}<hr/>
                                    {{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ $customer !== null ? $customer->customer_name : '' }}</td>
                                <td>
                                    {{ $model->sender_name }}<hr/>
                                    {{ $model->sender_address }}
                                </td>
                                <td>
                                    {{ $model->receiver_name }}<hr/>
                                    {{ $model->receiver_address }}
                                </td>
                                <td>{{ $route !== null ? $route->route_code : '' }}</td>
                                <td>
                                    {{ $model->itemName() }}<hr/>
                                    {{ $model->itemUnit() }}
                                </td>
                                <td class="text-right">{{ number_format($model->totalColy()) }}</td>
                                <td class="text-right">
                                    {{ number_format($model->totalAmount()) }}<hr/>
                                    {{ number_format($model->discount) }}<hr/>
                                    {{ number_format($model->total()) }}
                                </td>
                                <td class="text-right">
                                    {{ $activeNego !== null ? number_format($activeNego->nego_price) : '' }}<hr/>
                                    {{ $activeNego !== null ? number_format($model->total() - $activeNego->nego_price) : 0 }}
                                </td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>

                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->resi_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
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
