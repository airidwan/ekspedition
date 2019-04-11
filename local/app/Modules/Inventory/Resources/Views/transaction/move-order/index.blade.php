<?php use App\Modules\Inventory\Model\Transaction\MoveOrderHeader; ?>

@extends('layouts.master')

@section('title', trans('inventory/menu.move-order'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> {{ trans('inventory/menu.move-order') }}</h2>
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
                                <label for="moNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.mo-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="moNumber" name="moNumber" value="{{ !empty($filters['moNumber']) ? $filters['moNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="serviceNumber" class="col-sm-4 control-label">{{ trans('asset/fields.service-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="serviceNumber" name="serviceNumber" value="{{ !empty($filters['serviceNumber']) ? $filters['serviceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <option value="" >ALL</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type }}" {{ !empty($filters['type']) && $filters['type'] == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ !empty($filters['itemCode']) ? $filters['itemCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemName" name="itemName" value="{{ !empty($filters['itemName']) ? $filters['itemName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pic" class="col-sm-4 control-label">{{ trans('inventory/fields.pic') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pic" name="pic" value="{{ !empty($filters['pic']) ? $filters['pic'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label"></label>
                                <div class="col-sm-8">
                                    <?php $jenis = !empty($filters['jenis']) ? $filters['jenis'] : 'headers' ?>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio1" value="headers" {{ $jenis == 'headers' ? 'checked' : '' }}> Headers
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio2" value="lines" {{ $jenis == 'lines' ? 'checked' : '' }}> Lines
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="warehouse" class="col-sm-4 control-label">{{ trans('inventory/fields.warehouse') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="warehouse" name="warehouse">
                                        <option value="" >ALL</option>
                                        @foreach($optionWarehouse as $warehouse)
                                        <option value="{{ $warehouse->wh_id }}" {{ !empty($filters['warehouse']) && $filters['warehouse'] == $warehouse->wh_id ? 'selected' : '' }}>{{ $warehouse->wh_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="" >ALL</option>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverName" name="driverName" value="{{ !empty($filters['driverName']) ? $filters['driverName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
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
                    @if (empty($filters['jenis']) || $filters['jenis'] == 'headers')
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.mo-number') }}</th>
                                <th>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('asset/fields.service-number') }}</th>
                                <th>{{ trans('operational/fields.driver') }}</th>
                                <th>{{ trans('operational/fields.truck') }}</th>
                                <th>{{ trans('inventory/fields.pic') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th width="80px">{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null; ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->mo_number }}</td>
                                <td>{{ $model->type }}</td>
                                <td>{{ $model->service_number }}</td>
                                <td>{{ $model->driver_name }}</td>
                                <td>{{ $model->police_number }}</td>
                                <td>{{ !empty($model->vendor_name) ? $model->vendor_name : $model->pic }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->mo_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->status != MoveOrderHeader::COMPLETE && $model->status != MoveOrderHeader::CANCELED)
                                    <a data-id="{{ $model->mo_header_id }}" data-label="{{ $model->mo_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} MO" data-modal="modal-cancel">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan
                                    @if($model->status == MoveOrderHeader::COMPLETE)
                                    <a href="{{ URL($url . '/print-pdf/' . $model->mo_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}" target="_blank">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                             <tr>
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.item-code') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>{{ trans('inventory/fields.warehouse') }}</th>
                                <th>{{ trans('inventory/fields.qty-need') }}</th>
                                <th>{{ trans('inventory/fields.uom') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $date      = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->item_code }}</td>
                                <td>{{ $model->item_name }}</td>
                                <td>{{ $model->wh_code }}</td>
                                <td>{{ $model->qty_need }}</td>
                                <td>{{ $model->uom_code }}</td>
                                <td>{{ $model->line_description }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->mo_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
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
<div class="md-modal md-3d-flip-horizontal" id="modal-cancel">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('inventory/menu.move-order') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel-mo') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="cancel-id" name="id" >
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
                                <button id="btn-cancel-mo" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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

        $('.cancel-action').on('click', function() {
            $("#cancel-id").val($(this).data('id'));
            $("#cancel-text").html('{{ trans('purchasing/fields.cancel-confirmation', ['variable' => trans('inventory/menu.move-order')]) }} ' + $(this).data('label') + '?');
            clearFormCancel()
        });

        $('#btn-cancel-mo').on('click', function(event) {
            event.preventDefault();
            if ($('textarea[name="reason"]').val() == '') {
                $(this).parent().parent().parent().addClass('has-error');
                $(this).parent().parent().parent().find('span.help-block').html('Reason is required');
                return
            } else {
                clearFormCancel()
            }

            $('#form-cancel').trigger('submit');
        });
    });

    var clearFormCancel = function() {
        $('#form-cancel').removeClass('has-error');
        $('#form-cancel').find('span.help-block').html('');
    };
</script>
@endsection
