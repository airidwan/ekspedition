@extends('layouts.master')

@section('title', trans('inventory/menu.master-stock'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> {{ trans('inventory/menu.master-stock') }}</h2>
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
                                <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ !empty($filters['itemCode']) ? $filters['itemCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.deskripsi') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="warehouse" class="col-sm-4 control-label">{{ trans('operational/fields.warehouse') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="warehouse" name="warehouse">
                                        <option value="">ALL</option>
                                        @foreach($optionsWarehouse as $warehouse)
                                        <option value="{{ $warehouse->wh_id }}" {{ !empty($filters['warehouse']) && $filters['warehouse'] == $warehouse->wh_id ? 'selected' : '' }}>{{ $warehouse->description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
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
                                <th>{{ trans('inventory/fields.item-code') }}</th>
                                <th>{{ trans('shared/common.deskripsi') }}</th>
                                <th>{{ trans('shared/common.kategori') }}</th>
                                <th>{{ trans('operational/fields.warehouse-code') }}</th>
                                <th>{{ trans('operational/fields.warehouse') }}</th>
                                <th>{{ trans('inventory/fields.stock') }}</th>
                                <th>{{ trans('inventory/fields.uom') }}</th>
                                <th>{{ trans('inventory/fields.average-cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->item_code }}</td>
                                <td>{{ $model->item_description }}</td>
                                <td>{{ $model->category_description }}</td>
                                <td>{{ $model->wh_code }}</td>
                                <td>{{ $model->warehouse_description }}</td>
                                <td class="text-right">{{ number_format($model->stock) }}</td>
                                <td>{{ $model->uom_code }}</td>
                                <td class="text-right">{{ number_format($model->average_cost) }}</td>
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
<div class="md-modal md-3d-flip-horizontal" id="modal-delete">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.delete') }}</strong> {{ trans('inventory/menu.master-stock') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="delete-text">Are you sure want to delete ?</h4>
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
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function(){
    $('#category').select2();
    $('#warehouse').select2();
    $('.delete-action').on('click', function() {
        $("#delete-id").val($(this).data('id'));
        $("#delete-text").html('{{ trans('shared/common.delete-confirmation', ['variable' => trans('inventory/menu.master-stock')]) }} ' + $(this).data('label') + '?');
    });
});
</script>
@endsection
