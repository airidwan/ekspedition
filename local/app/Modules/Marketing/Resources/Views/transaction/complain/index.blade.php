@extends('layouts.master')

@section('title', trans('marketing/menu.complain'))
<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-users"></i> {{ trans('marketing/menu.complain') }}</h2>
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
                                <label for="complainNumber" class="col-sm-4 control-label">{{ trans('marketing/fields.complain-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="complainNumber" name="complainNumber" value="{{ !empty($filters['complainNumber']) ? $filters['complainNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="" >ALL</option>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
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
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('marketing/fields.complain-number') }}<hr/>{{ trans('operational/fields.resi-number') }}</th>
                                <th>{{ trans('marketing/fields.callers-name') }}<hr/>{{ trans('marketing/fields.callers-phone') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>{{ trans('operational/fields.weight') }} (kg)<hr/>{{ trans('operational/fields.dimension') }} (m<sup>3</sup>)</th>
                                <th>{{ trans('marketing/fields.comment') }}</th>
                                <th>{{ trans('marketing/fields.temp-respon') }}</th>
                                <th>{{ trans('marketing/fields.last-respon') }}</th>
                                <th>{{ trans('shared/common.date') }}<hr/>{{ trans('shared/common.created-by') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                $modelResi = TransactionResiHeader::find($model->resi_id);
                                 $date      = !empty($model->complain_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->complain_time) : null;
                                 $user      = App\User::find($model->created_by);
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->complain_number }}<hr/>{{ $model->resi_number }}</td>
                                <td>{{ $model->name }}<hr/>{{ $model->callers_phone }}</td>
                                <td>{{ $model->item_name }}</td>
                                <td class="text-right">
                                    {{ number_format($modelResi->totalWeightAll(), 2) }}<hr/>
                                    {{ number_format($modelResi->totalVolumeAll(), 6) }}
                                </td>
                                <td >{{ substr($model->comment, 0, 50)}}@if(strlen($model->comment) > 50)......@endif</td>
                                <td >{{ substr($model->temporary_respon, 0, 50)}}@if(strlen($model->temporary_respon) > 50)......@endif</td>
                                <td >{{ substr($model->last_respon, 0, 50)}}@if(strlen($model->last_respon) > 50)......@endif</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y H:i') : '' }}<hr/>{{ !empty($user) ? $user->full_name : '' }} </td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->complain_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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