@extends('layouts.master')

@section('title', trans('operational/menu.resi-stock-correction'))
<?php 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
    use App\Modules\Operational\Model\Transaction\ResiStockCorrection; 
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.resi-stock-correction') }}</h2>
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
                                <label for="resiStockCorrection" class="col-sm-4 control-label">{{ trans('operational/fields.correction-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiStockCorrection" name="resiStockCorrection" value="{{ !empty($filters['resiStockCorrection']) ? $filters['resiStockCorrection'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="officialReportNumber" class="col-sm-4 control-label">{{ trans('operational/fields.official-report-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="officialReportNumber" name="officialReportNumber" value="{{ !empty($filters['officialReportNumber']) ? $filters['officialReportNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = !empty($filters['type']) ? $filters['type'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="doCtNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-ct-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="doCtNumber" name="doCtNumber" value="{{ !empty($filters['doCtNumber']) ? $filters['doCtNumber'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="note" name="note" value="{{ !empty($filters['note']) ? $filters['note'] : '' }}">
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
                                <th>{{ trans('operational/fields.correction-number') }}</th>
                                <th>{{ trans('operational/fields.official-report-number') }}</th>
                                <th>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('operational/fields.do-ct-number') }}</th>
                                <th>{{ trans('operational/fields.resi-number') }}</th>
                                <th>{{ trans('operational/fields.total-coly') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $time = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->resi_stock_correction_number }}</td>
                                <td>{{ $model->official_report_number }}</td>
                                <td>{{ $model->type }}</td>
                                @if($model->type == ResiStockCorrection::DELIVERY_ORDER)
                                <td>{{ $model->delivery_order_number }}</td>
                                <td>{{ $model->do_resi_number }}</td>
                                @else
                                <td>{{ $model->customer_taking_transact_number }}</td>
                                <td>{{ $model->ct_resi_number }}</td>
                                @endif
                                <td class="text-center">{{ number_format($model->total_coly) }}</td>
                                <td>{{ $model->note }}</td>
                                <td>{{ !empty($time) ? $time->format('d-M-Y') : '' }}</td>
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