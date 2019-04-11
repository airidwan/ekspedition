@extends('layouts.master')

<?php
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
?>

@section('title', trans('purchasing/menu.purchase-order'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('purchasing/menu.purchase-order') }}</h2>
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
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ !empty($filters['poNumber']) ? $filters['poNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ !empty($filters['itemCode']) ? $filters['itemCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="itemDescription" class="col-sm-4 control-label">{{ trans('purchasing/fields.item-description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemDescription" name="itemDescription" value="{{ !empty($filters['itemDescription']) ? $filters['itemDescription'] : '' }}">
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
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('purchasing/fields.supplier') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="supplier" name="supplier">
                                        <option value="">ALL</option>
                                        @foreach($optionsSupplier as $supplier)
                                        <option value="{{ $supplier->vendor_id }}" {{ !empty($filters['supplier']) && $filters['supplier'] == $supplier->vendor_id ? 'selected' : '' }}>{{ $supplier->vendor_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('purchasing/fields.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <option value="">ALL</option>
                                        @foreach($optionsType as $type)
                                        <option value="{{ $type->type_id }}" {{ !empty($filters['type']) && $filters['type'] == $type->type_id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionsStatus as $status)
                                        <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('purchasing/fields.po-number') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('purchasing/fields.supplier') }}</th>
                                <th>{{ trans('purchasing/fields.type') }}</th>
                                <th>{{ trans('shared/common.tanggal') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                @if (!empty($filters['status']) && $filters['status'] == \App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader::CANCELED)
                                <th>{{ trans('shared/common.canceled-date') }}</th>
                                <th>{{ trans('shared/common.reason') }}</th>
                                @endif
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $poDate = !empty($model->po_date) ? new \DateTime($model->po_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->po_number }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->vendor_name }}</td>
                                <td>{{ $model->type_name }}</td>
                                <td>{{ $poDate !== null ? $poDate->format('d-m-Y') : '' }}</td>
                                <td>{{ $model->status }}</td>

                                @if (!empty($filters['status']) && $filters['status'] == \App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader::CANCELED)
                                <?php $canceledDate = new \DateTime($model->canceled_date); ?>
                                <td>{{ $canceledDate->format('d-M-Y') }}</td>
                                <td>{{ $model->canceled_reason }}</td>
                                @endif

                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    @if($model->status == PurchaseOrderHeader::APPROVED || $model->status == PurchaseOrderHeader::CLOSED)
                                    <a href="{{ URL($url . '/print-pdf-detail/' . $model->header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}" target="_blank">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    @endif
                                    @if(Gate::check('access', [$resource, 'delete']) && $model->status == PurchaseOrderHeader::INCOMPLETE)
                                    <a data-id="{{ $model->header_id }}" data-label="{{ $model->po_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan

                                    @if(Gate::check('access', [$resource, 'delete']) && $model->status == PurchaseOrderHeader::APPROVED)
                                    <a data-id="{{ $model->header_id }}" data-label="{{ $model->po_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} PO" data-modal="modal-cancel">
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('purchasing/fields.po-number') }}</th>
                                <th>{{ trans('purchasing/fields.type') }}</th>
                                <th>{{ trans('purchasing/fields.item') }}</th>
                                <th>{{ trans('purchasing/fields.item-description') }}</th>
                                <th>{{ trans('purchasing/fields.wh') }}</th>
                                <th>{{ trans('purchasing/fields.qty') }}</th>
                                <th>{{ trans('purchasing/fields.uom') }}</th>
                                <th>{{ trans('purchasing/fields.item-category') }}</th>
                                <th>{{ trans('purchasing/fields.unit-price') }}</th>
                                <th>{{ trans('purchasing/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $poDate = !empty($model->po_date) ? new \DateTime($model->po_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->po_number }}</td>
                                <td>{{ $model->type_name }}</td>
                                <td>{{ $model->item_code }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->wh_code }}</td>
                                <td class="text-right">{{ $model->quantity_need }}</td>
                                <td>{{ $model->uom_code }}</td>
                                <td>{{ $model->category_description }}</td>
                                <td class="text-right">{{ number_format($model->unit_price) }}</td>
                                <td class="text-right">{{ number_format($model->total_price) }}</td>
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
<div class="md-modal md-3d-flip-horizontal" id="modal-delete">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('purchasing/menu.purchase-order') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="delete-text">Are you sure want to cancel ?</h4>
                    <form role="form" method="post" action="{{ URL($url . '/delete') }}" class="text-right">
                        {{ csrf_field() }}
                        <input type="hidden" id="delete-id" name="id" >
                        <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                        <button type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="md-modal md-3d-flip-horizontal" id="modal-cancel">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('purchasing/menu.purchase-order') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel-po') }}">
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
                                <button id="btn-cancel-po" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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
        $('#kota').select2();
        $('.delete-action').on('click', function() {
            $("#delete-id").val($(this).data('id'));
            $("#delete-text").html('{{ trans('shared/common.cancel-confirmation', ['variable' => trans('purchasing/menu.purchase-order')]) }} ' + $(this).data('label') + '?');
        });

        $('.cancel-action').on('click', function() {
            $("#cancel-id").val($(this).data('id'));
            $("#cancel-text").html('{{ trans('purchasing/fields.cancel-confirmation', ['variable' => trans('purchasing/menu.purchase-order')]) }} ' + $(this).data('label') + '?');
            clearFormCancel()
        });

        $('#btn-cancel-po').on('click', function(event) {
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
