<?php use App\Modules\Operational\Model\Transaction\ManifestHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.manifest'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.manifest') }}</h2>
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
                                <label for="manifestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" value="{{ !empty($filters['manifestNumber']) ? $filters['manifestNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driver" name="driver" value="{{ !empty($filters['driver']) ? $filters['driver'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driverAssistant" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverAssistant" name="driverAssistant" value="{{ !empty($filters['driverAssistant']) ? $filters['driverAssistant'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nopolTruck" class="col-sm-4 control-label">{{ trans('operational/fields.nopol-truck') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="nopolTruck" name="nopolTruck" value="{{ !empty($filters['nopolTruck']) ? $filters['nopolTruck'] : '' }}">
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
                                <label for="truckCategory" class="col-sm-4 control-label">{{ trans('operational/fields.truck-category') }}</label>
                                <div class="col-sm-8">
                                    <?php $truckCategoryString = !empty($filters['truckCategory']) ? $filters['truckCategory'] : ''; ?>
                                    <select class="form-control" name="truckCategory">
                                        <option value="">ALL</option>
                                        @foreach($truckCategory as $option)
                                            <option value="{{ $option->lookup_code }}" {{ $truckCategoryString == $option->lookup_code ? 'selected' : '' }}>{{ $option->meaning }}</option>
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
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <?php $status = !empty($filters['status']) ? $filters['status'] : ''; ?>
                                    <select class="form-control" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $option)
                                            <option value="{{ $option }}" {{ $status == $option ? 'selected' : '' }}>{{ $option }}</option>
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
                                    {{ trans('operational/fields.manifest-number') }}<hr/>
                                    {{ trans('operational/fields.date') }}
                                </th>
                                <th>{{ trans('operational/fields.route') }}</th>
                                <th>
                                    {{ trans('operational/fields.kota-asal') }}<hr/>
                                    {{ trans('operational/fields.kota-tujuan') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.nopol-truck') }}<hr/>
                                    {{ trans('operational/fields.truck-owner') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.driver') }}<hr/>
                                    {{ trans('operational/fields.driver-assistant') }}
                                </th>
                                <th>{{ trans('operational/fields.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = ManifestHeader::find($model->manifest_header_id);
                            $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            $route = $model->route;
                            $startCity = $route !== null ? $route->cityStart : null;
                            $endCity = $route !== null ? $route->cityEnd : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->manifest_number }}<hr/>
                                    {{ $date !== null ? $date->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ $route !== null ? $route->route_code : '' }}</td>
                                <td>
                                    {{ $startCity !== null ? $startCity->city_name : '' }}<hr/>
                                    {{ $endCity !== null ? $endCity->city_name : '' }}
                                </td>
                                <td>
                                    {{ $model->truck !== null ? $model->truck->police_number : '' }} - 
                                    {{ $model->truck !== null ? $model->truck->getCategory() : '' }}<hr/>
                                    {{ $model->truck !== null ? $model->truck->owner_name : '' }}
                                    {{ $model->po !== null ? ' - '.$model->po->po_number : '' }}
                                </td>
                                <td>
                                    {{ $model->driver !== null ? $model->driver->driver_name : '' }}<hr/>
                                    {{ $model->driverAssistant !== null ? $model->driverAssistant->driver_name : '' }}
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    @if(Gate::check('access', [$resource, 'update']))
                                    <a href="{{ URL($url . '/edit/' . $model->manifest_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endif
                                    @if(Gate::check('access', [$resource, 'close']) && $model->isArrived())
                                    <a data-id="{{ $model->manifest_header_id }}" data-label="{{ $model->manifest_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger close-action" data-original-title="{{ trans('shared/common.close') }}" data-modal="modal-close">
                                        <i class="fa fa-lock"></i>
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

@section('modal')
@parent
<div class="md-modal md-3d-flip-horizontal" id="modal-close">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.close') }}</strong> {{ trans('operational/menu.manifest') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="close-text">Are you sure want to close ?</h4>
                    <form id="form-close" role="form" method="post" action="{{ URL($url . '/close') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="close-id" name="id" >
                        <div class="form-group">
                            <h4 for="reason" class="col-sm-4 control-label">{{ trans('shared/common.reason') }} <span class="required">*</span></h4>
                            <div class="col-sm-8">
                                <textarea name="reason" class="form-control" rows="4"></textarea>
                            </div>
                            <span class="help-block text-center"></span>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12 text-right">
                                <br>
                                <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                                <button id="btn-close" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        $('.close-action').on('click', function() {
            $("#close-id").val($(this).data('id'));
            $("#close-text").html('{{ trans('shared/common.close-confirmation', ['variable' => trans('operational/menu.manifest')]) }} ' + $(this).data('label') + '?');
            clearFormClose()
        });

        $('#btn-close').on('click', function(event) {
            event.preventDefault();
            if ($('textarea[name="reason"]').val() == '') {
                $(this).parent().parent().parent().addClass('has-error');
                $(this).parent().parent().parent().find('span.help-block').html('Reason is required');
                return
            } else {
                clearFormClose()
            }

            $('#form-close').trigger('submit');
        });
    });

    var clearFormClose = function() {
        $('#form-close').removeClass('has-error');
        $('#form-close').find('span.help-block').html('');
    };
</script>
@endsection

