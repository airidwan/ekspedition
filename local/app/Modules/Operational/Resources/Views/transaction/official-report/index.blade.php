@extends('layouts.master')

@section('title', trans('operational/menu.official-report'))
<?php 
use App\Modules\Operational\Model\Transaction\OfficialReport;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.official-report') }}</h2>
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
                                <label for="officialReportNumber" class="col-sm-4 control-label">{{ trans('operational/fields.official-report-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="officialReportNumber" name="officialReportNumber" value="{{ !empty($filters['officialReportNumber']) ? $filters['officialReportNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="personName" class="col-sm-4 control-label">{{ trans('operational/fields.person-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="personName" name="personName" value="{{ !empty($filters['personName']) ? $filters['personName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="category" id="category">
                                            <option value="">ALL</option>
                                        @foreach($optionCategory as $category)
                                            <option value="{{ $category }}" {{ !empty($filters['category']) && $filters['category'] == $category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="status" id="status">
                                            <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                            <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>    
                                <th>{{ trans('operational/fields.official-report-number') }}</th>
                                <th width="10%">
                                    {{ trans('shared/common.category') }}<hr/>
                                    {{ trans('shared/common.date') }}
                                </th>
                                <th>{{ trans('operational/fields.resi-number') }}</th>
                                <th>{{ trans('operational/fields.person-name') }}</th>
                                <th width="20%">{{ trans('shared/common.description') }}</th>
                                <th width="10%">
                                    {{ trans('shared/common.created-by') }}<hr/>
                                    {{ trans('shared/common.date') }}
                                </th>
                                <th width= "10%">
                                    {{ trans('shared/common.respon-by') }}<hr/>
                                    {{ trans('shared/common.date') }}
                                </th>
                                <th width="20%">{{ trans('shared/common.respon') }}</th>
                                <th >{{ trans('shared/common.status') }}</th>
                                <th width="100px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <?php
                            $officialReportDate = !empty($model->datetime) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->datetime) : null;
                            $createdDate = !empty($model->created_date) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->created_date) : null;
                            $responDate = !empty($model->respon_date) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->respon_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td >{{ $model->official_report_number }}</td>
                                <td >
                                    {{ $model->category }}<hr/>
                                    {{ $officialReportDate !== null ? $officialReportDate->format('d-m-Y H:i') : '' }}
                                </td>
                                <td>{{ $model->resi_number }}</td>
                                <td>{{ $model->person_name }}</td>
                                <td >{{ substr($model->description, 0, 150)}}@if(strlen($model->description) > 150)......@endif</td>
                                <td >
                                    {{ $model->created_name }}<hr/>
                                    {{ $officialReportDate !== null ? $officialReportDate->format('d-m-Y H:i') : '' }}
                                </td>
                                <td >
                                    {{ $model->response_name }}<hr/>
                                    {{ $responDate !== null ? $responDate->format('d-m-Y H:i') : '' }}
                                </td>
                                <td>{{ substr($model->respon, 0, 150)}}@if(strlen($model->respon) > 150)......@endif</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    @if($model->category == OfficialReport::UMUM || $model->category == OfficialReport::INVOICE_REQUEST || $model->created_by == \Auth::user()->id)
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->official_report_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
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
