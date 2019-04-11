<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.resi-all-branch'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.resi-all-branch') }}</h2>
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
                                <label for="branchId" class="col-sm-4 control-label">{{ trans('operational/fields.branch') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="branchId">
                                        <option value="">ALL</option>
                                        @foreach($optionBranch as $branch)
                                            <option value="{{ $branch->branch_id }}" {{ !empty($filters['branchId']) && $filters['branchId'] == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
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
                            <div class="form-group">
                                <label for="payment" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }}</label>
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
                                <label for="insurance" class="col-sm-4 control-label">{{ trans('operational/fields.insurance') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="insurance">
                                        <option value="">ALL</option>
                                        <option value="insurance" {{ !empty($filters['insurance']) && $filters['insurance'] == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                        <option value="nonInsurance" {{ !empty($filters['insurance']) && $filters['insurance'] == 'nonInsurance' ? 'selected' : '' }}>Non Insurance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                            <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
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
                                    {{ trans('operational/fields.sender') }}<hr/>
                                    {{ trans('operational/fields.address') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.customer') }}<hr/>
                                    {{ trans('operational/fields.receiver') }}<hr/>
                                    {{ trans('operational/fields.address') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.route') }}<hr/>
                                    {{ trans('operational/fields.payment') }}<hr/>
                                    {{ trans('operational/fields.insurance') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.item-name') }}<hr/>
                                    {{ trans('operational/fields.item-unit') }}
                                </th>
                                <th>{{ trans('operational/fields.coly') }}</th>
                                <th>
                                    {{ trans('operational/fields.weight') }}<hr/>
                                    {{ trans('operational/fields.total-price') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.volume') }}<hr/>
                                    {{ trans('operational/fields.total-price') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.qty-unit') }}<hr/>
                                    {{ trans('operational/fields.total-price') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.total-amount') }}<hr/>
                                    {{ trans('operational/fields.discount') }}<hr/>
                                    {{ trans('shared/common.total') }}<hr/>
                                </th>
                                <th>{{ trans('operational/fields.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = TransactionResiHeader::find($model->resi_header_id);
                            $resiDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->resi_number }}<hr/>
                                    {{ $resiDate !== null ? $resiDate->format('d-m-Y') : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->customer) ? $model->customer->customer_name : '' }}<hr/>
                                    {{ $model->sender_name }}<hr/>
                                    {{ $model->sender_address }}
                                </td>
                                <td>
                                    {{ !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : '' }}<hr/>
                                    {{ $model->receiver_name }}<hr/>
                                    {{ $model->receiver_address }}
                                </td>
                                <td class="text-center">
                                    {{ $model->route !== null ? $model->route->route_code : '' }}<hr/>
                                    {{ $model->getSingkatanPayment() }}<hr/>
                                    <i class="fa {{ $model->insurance ? 'fa-check' : 'fa-remove' }}"></i>
                                </td>
                                <td>
                                    {{ $model->itemName() }}<hr/>
                                    {{ $model->itemUnit() }}
                                </td>
                                <td class="text-right">{{ number_format($model->totalColy()) }}</td>
                                <td class="text-right">
                                    {{ number_format($model->totalWeight(), 2) }}<hr/>
                                    {{ number_format($model->totalWeightPrice()) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($model->totalVolume(), 6) }}<hr/>
                                    {{ number_format($model->totalVolumePrice()) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($model->totalUnit()) }}<hr/>
                                    {{ number_format($model->totalUnitPrice()) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($model->totalAmount()) }}<hr/>
                                    {{ number_format($model->discount) }}<hr/>
                                    {{ number_format($model->total()) }}
                                </td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>

                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->resi_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    @if($model->isApproved())
                                        <a href="{{ URL($urlResi . '/print-pdf/' . $model->resi_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    @endif
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
