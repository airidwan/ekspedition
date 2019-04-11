@extends('layouts.master')

@section('title', trans('operational/menu.customer-taking'))
<?php 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader; 
    use App\Modules\Operational\Model\Transaction\CustomerTaking; 
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.customer-taking') }}</h2>
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
                                <label for="customerTakingNumber" class="col-sm-4 control-label">{{ trans('operational/fields.customer-taking-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerTakingNumber" name="customerTakingNumber" value="{{ !empty($filters['customerTakingNumber']) ? $filters['customerTakingNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
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
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('operational/fields.customer-taking-number') }}</th>
                                <th>{{ trans('operational/fields.resi-number') }}</th>
                                <th>{{ trans('operational/fields.customer') }}</th>
                                <th>{{ trans('operational/fields.receiver') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>{{ trans('operational/fields.weight') }} (kg)</th>
                                <th>{{ trans('operational/fields.dimension') }} (m<sup>3</sup>)</th>
                                <th>{{ trans('operational/fields.total-coly') }}</th>
                                <th>{{ trans('operational/fields.coly-wh') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.time') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                $modelResi = TransactionResiHeader::find($model->resi_header_id);
                                 $date = !empty($model->customer_taking_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->customer_taking_time) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->customer_taking_number }}</td>
                                <td>{{ $modelResi->resi_number }}</td>
                                <td>{{ $modelResi->getCustomerName() }}</td>
                                <td>{{ $modelResi->receiver_name }}</td>
                                <td>{{ $modelResi->item_name }}</td>
                                <td class="text-right">
                                    {{ number_format($modelResi->totalWeightAll(), 2) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($modelResi->totalVolumeAll(), 6) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($modelResi->totalColy()) }}
                                </td>
                                <td class="text-right">{{ !empty($model->coly) ? $model->coly : 0 }}</td>
                                <td>{{ $model->note }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td>{{ !empty($date) ? $date->format('H:i') : '' }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->customer_taking_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    <a href="{{ URL($url . '/print-pdf-detail/' . $model->customer_taking_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
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