@extends('layouts.master')

@section('title', trans('inventory/menu.warehouse-transfer'))

<?php use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader; ?>


@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> {{ trans('inventory/menu.warehouse-transfer') }}</h2>
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
                                <label for="whtNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.wht-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="whtNumber" name="whtNumber" value="{{ !empty($filters['whtNumber']) ? $filters['whtNumber'] : '' }}">
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
                                <label for="fromWarehouse" class="col-sm-4 control-label">{{ trans('inventory/fields.from-wh') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="fromWarehouse" name="fromWarehouse">
                                        <option value="" >ALL</option>
                                        @foreach($optionWarehouse as $fromWarehouse)
                                        <option value="{{ $fromWarehouse->wh_id }}" {{ !empty($filters['fromWarehouse']) && $filters['fromWarehouse'] == $fromWarehouse->wh_id ? 'selected' : '' }}>{{ $fromWarehouse->wh_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="toWarehouse" class="col-sm-4 control-label">{{ trans('inventory/fields.to-wh') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="toWarehouse" name="toWarehouse">
                                        <option value="" >ALL</option>
                                        @foreach($optionWarehouse as $toWarehouse)
                                        <option value="{{ $toWarehouse->wh_id }}" {{ !empty($filters['toWarehouse']) && $filters['toWarehouse'] == $toWarehouse->wh_id ? 'selected' : '' }}>{{ $toWarehouse->wh_code }}</option>
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
                                <th>{{ trans('inventory/fields.wht-number') }}</th>
                                <th>{{ trans('inventory/fields.pic') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="120px">{{ trans('shared/common.action') }}</th>
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
                                <td>{{ $model->wht_number }}</td>
                                <td>{{ $model->pic }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->wht_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->status != WarehouseTransferHeader::COMPLETE && $model->status != WarehouseTransferHeader::CANCELED)
                                    <a data-id="{{ $model->wht_header_id }}" data-label="{{ $model->wht_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} WHT" data-modal="modal-cancel">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan
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
                                <th>{{ trans('inventory/fields.wht-number') }}</th>
                                <th>{{ trans('inventory/fields.item-code') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}</th>
                                <th>{{ trans('inventory/fields.from-wh') }}</th>
                                <th>{{ trans('inventory/fields.qty-need') }}</th>
                                <th>{{ trans('inventory/fields.uom') }}</th>
                                <th>{{ trans('inventory/fields.to-wh') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th width="120px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $date  = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->wht_number }}</td>
                                <td>{{ $model->item_code }}</td>
                                <td>{{ $model->item_name }}</td>
                                <td>{{ $model->wh_from_code }}</td>
                                <td class="text-center">{{ $model->qty_need }}</td>
                                <td>{{ $model->uom_code }}</td>
                                <td>{{ $model->wh_to_code }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->wht_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->status != WarehouseTransferHeader::COMPLETE && $model->status != WarehouseTransferHeader::CANCELED)
                                    <a data-id="{{ $model->wht_header_id }}" data-label="{{ $model->wht_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} WHT" data-modal="modal-cancel">
                                        <i class="fa fa-remove"></i>
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
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('inventory/menu.warehouse-transfer') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel-wt') }}">
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
                                <button id="btn-cancel-wt" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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
            $("#cancel-text").html('{{ trans('purchasing/fields.cancel-confirmation', ['variable' => trans('inventory/menu.warehouse-transfer')]) }} ' + $(this).data('label') + '?');
            clearFormCancel()
        });

        $('#btn-cancel-wt').on('click', function(event) {
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
