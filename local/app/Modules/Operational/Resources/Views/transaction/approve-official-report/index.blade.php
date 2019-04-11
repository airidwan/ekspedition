@extends('layouts.master')

@section('title', trans('operational/menu.approve-official-report'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.approve-official-report') }}</h2>
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
                                <th>
                                    {{ trans('shared/common.date') }}
                                </th>
                                <th>{{ trans('operational/fields.person-name') }}</th>
                                <th width="30%">{{ trans('shared/common.description') }}</th>
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
                                <td width="170px">{{ $model->official_report_number }}</td>
                                <td width="200px">
                                    {{ $officialReportDate !== null ? $officialReportDate->format('d-m-Y H:i') : '' }}
                                </td>
                                <td>{{ $model->person_name }}</td>
                                <td>{{ substr($model->description, 0, 250)}}@if(strlen($model->description) > 250)......@endif</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->official_report_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
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
