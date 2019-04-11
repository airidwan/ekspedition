@extends('layouts.master')

@section('title', trans('inventory/menu.receipt-branch-transfer'))

<?php use App\Modules\Inventory\Model\Transaction\BranchTransferLine; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.receipt-branch-transfer') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->receipt_bt_header_id }}">
                        <div class="col-sm-6 portlets">
                        @if(!empty($model->receipt_bt_header_id))
                            <div class="form-group {{ $errors->has('receiptNumber') ? 'has-error' : '' }}">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ $model->receipt_bt_number}}" readonly>
                                        @if($errors->has('receiptNumber'))
                                        <span class="help-block">{{ $errors->first('receiptNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                                $receiptDate = new \DateTime($model->receipt_bt_date);
                            ?>
                            <div class="form-group {{ $errors->has('receiptDate') ? 'has-error' : '' }}">
                                <label for="receiptDate" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="receiptDate" name="receiptDate" value="{{ $receiptDate !== null ? $receiptDate->format('d-M-Y') : '' }}" readonly>
                                        @if($errors->has('receiptDate'))
                                        <span class="help-block">{{ $errors->first('receiptDate') }}</span>
                                        @endif
                                </div>
                            </div>
                            @endif
                            <?php 
                                $bt          = $model->branchTransferHeader;
                                $btNumber    = !empty($bt) ? $bt->bt_number : ''; 
                                $description = !empty($bt) ? $bt->description : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('btHeaderId') ? 'has-error' : '' }}">
                                <label for="btHeaderId" class="col-sm-4 control-label">{{ trans('inventory/fields.bt-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="btHeaderId" name="btHeaderId" value="{{ count($errors) > 0 ? old('btHeaderId') : $model->bt_header_id }}">
                                        <input type="text" class="form-control" id="btNumber" name="btNumber" value="{{ count($errors) > 0 ? old('btNumber') : $btNumber }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="{{ empty($model->receipt_bt_header_id) ? 'modal' : '' }}" data-target="#modal-lov-bt"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('btHeaderId'))
                                        <span class="help-block">{{ $errors->first('btHeaderId') }}</span>
                                        @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} </label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $description }}" readonly>
                                        @if($errors->has('description'))
                                        <span class="help-block">{{ $errors->first('description') }}</span>
                                        @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <hr><br>
                            <strong> {{ trans('inventory/fields.bt-lines') }} </strong>
                            <table id="table-detail" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input name="all-line" id="all-line" type="checkbox" ></th>
                                        <th>{{ trans('inventory/fields.item') }}</th>
                                        <th>{{ trans('inventory/fields.item-description') }}</th>
                                        <th>{{ trans('inventory/fields.order-quantity') }}</th>
                                        <th>{{ trans('inventory/fields.uom') }}</th>
                                        <th>{{ trans('shared/common.category') }}</th>
                                        <th>{{ trans('inventory/fields.receipt-quantity') }}</th>
                                        <th>{{ trans('inventory/fields.wh') }}</th>
                                        <th>{{ trans('shared/common.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if (count($errors) > 0 && !empty(old('lineIdHidden')))
                                @foreach(old('lineIdHidden') as $btLineId)
                                <?php
                                $btLine = BranchTransferLine::find($btLineId);
                                $item   = $btLine->item()->first();
                                $uom    = $item !== null ? $item->uom : null;
                                $category = $item !== null ? $item->category : null;
                                $btHeader = $btLine->header()->first();
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <input name="btLineId[]" value="{{ $btLineId }}" type="checkbox" class="rows-check">
                                        </td>
                                        <td>{{ $item !== null ? $item->item_code : '' }}</td>
                                        <td>{{ $item !== null ? $item->description : '' }}</td>
                                        <td>{{ $btLine->qty_need }}</td>
                                        <td>{{ $uom !== null ? $uom->uom_code : '' }}</td>
                                        <td>{{ $category !== null ? $category->description : '' }}</td>
                                        <td>
                                            <input type="number" min="1" max="{{ $btLine->qty_remain }}" class="form-control currency" name="receiptQuantity-{{ $btLineId }}" value="{{ old('receiptQuantity-'.$btLineId) }}"></td>
                                        <td>
                                            <select class="form-control" name="whId-{{ $btLineId }}" >'+
                                                @foreach($optionWarehouse as $warehouse)
                                                <option value="{{ $warehouse->wh_id }}" {{ $warehouse->wh_id == old('whId-'.$btLineId) ? 'selected' : '' }}>{{ $warehouse->wh_code }}</option>
                                                @endforeach
                                            </select> 
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="descriptionLine-{{ $btLineId }}" value="{{ old('descriptionLine-'.$btLineId) }}">
                                        </td>
                                        <input type="hidden" name="lineIdHidden[]" value="{{ $btLineId }}" />
                                        <input type="hidden" name="quantity-'{{ $btLineId }}" value="$btLine->qty_need" />
                                        </tr>
                                @endforeach
                                @endif
                                @if(!empty($model->receipt_bt_header_id))
                                    <?php  $lines = $model->lines;
                                           $no=1;
                                    ?>
                                    @foreach($lines as $line)
                                    <?php      
                                    $btLine = BranchTransferLine::find($line->bt_line_id);
                                    $item   = $btLine->item()->first();
                                    $wh     = $btLine->toWarehouse;
                                    $uom    = $item !== null ? $item->uom : null;
                                    $category = $item !== null ? $item->category : null;
                                    $btHeader = $btLine->header()->first();
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                        {{ $no++ }}
                                        </td>
                                        <td>{{ $item !== null ? $item->item_code : '' }}</td>
                                        <td>{{ $item !== null ? $item->description : '' }}</td>
                                        <td class="text-center">{{ $btLine->qty_need }}</td>
                                        <td>{{ $uom !== null ? $uom->uom_code : '' }}</td>
                                        <td>{{ $category !== null ? $category->description : '' }}</td>
                                        <td class="text-center">{{ $line->receipt_bt_quantity }}</td>
                                        <td>{{ $wh !== null ? $wh->wh_code : '' }}</td>
                                        <td >{{ $line->description }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}
                                </a>
                                @if(empty($model->receipt_bt_header_id))
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-lov-bt" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Branch Transfer List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('inventory/fields.bt-number') }}</th>
                            <th>{{ trans('operational/fields.driver-name') }}</th>
                            <th>{{ trans('operational/fields.truck-code') }}</th>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('shared/common.date') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionBT as $bt)
                        <?php
                             $date    = !empty($bt->created_date) ? new \DateTime($bt->created_date) : null;
                            $btLines = \DB::table('inv.trans_bt_line')
                                            ->select('trans_bt_line.*', 'v_mst_item.item_code', 'v_mst_item.description as item_description', 'v_mst_item.uom_code', 'v_mst_item.category_description', 'mst_warehouse.wh_id', 'mst_warehouse.wh_code' )
                                            ->leftJoin('inv.v_mst_item', 'v_mst_item.item_id', '=', 'trans_bt_line.item_id')
                                            ->leftJoin('inv.mst_warehouse', 'mst_warehouse.wh_id', '=', 'trans_bt_line.to_wh_id')
                                            ->where('bt_header_id', '=', $bt->bt_header_id)
                                            ->where('qty_remain', '<>', 0)
                                            ->get();
                        ?>
                        <tr style="cursor: pointer;" data-bt="{{ json_encode($bt) }}" data-detail-bt="{{ json_encode($btLines) }}">
                            <td>{{ $bt->bt_number }}</td>
                            <td>{{ $bt->driver_name }}</td>
                            <td>{{ $bt->truck_code }}</td>
                            <td>{{ $bt->police_number }}</td>
                            <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                            <td>{{ $bt->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function() {
        $("#datatables-lov").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var dataBT = $(this).data('bt');
            var dataDetailBT = $(this).data('detail-bt');

            $('#btNumber').val(dataBT.bt_number);
            $('#description').val(dataBT.description);
            $('#btHeaderId').val(dataBT.bt_header_id);

            $('#table-detail tbody').html('');
            for (var i = 0; i < dataDetailBT.length; i++) {
                    var tr = '<tr>' +
                    '<td class="text-center">'+
                        '<input name="btLineId[]" value="' + dataDetailBT[i].bt_line_id +'" type="checkbox" class="rows-check">'+
                    '</td>'+
                    '<td>' + dataDetailBT[i].item_code + '</td>' +
                    '<td>' + dataDetailBT[i].item_description + '</td>' +
                    '<td>' + dataDetailBT[i].qty_need + '</td>' +
                    '<td>' + dataDetailBT[i].uom_code + '</td>' +
                    '<td>' + dataDetailBT[i].category_description + '</td>' +
                    '<td>'+
                        '<input type="number" min="1" max="'+ dataDetailBT[i].qty_remain +'" class="form-control currency" name="receiptQuantity-' + dataDetailBT[i].bt_line_id + '" value="'+ dataDetailBT[i].qty_remain + '"></td>' + 
                    '<td>'+
                        '<select class="form-control" name="whId-' + dataDetailBT[i].bt_line_id + '" >'+
                            @foreach($optionWarehouse as $warehouse)
                            '<option value="{{ $warehouse->wh_id }}">{{ $warehouse->wh_code }}</option>'+
                            @endforeach
                        '</select>'+ 
                    '</td>' +
                    '<td>'+
                        '<input type="text" class="form-control" name="descriptionLine-' + dataDetailBT[i].bt_line_id + '" value="'+ dataDetailBT[i].description + '"></td>' + 
                    '<input type="hidden" name="lineIdHidden[]" value="'+ dataDetailBT[i].bt_line_id + '" />'+
                    '<input type="hidden" name="categoryCode-' + dataDetailBT[i].bt_line_id + '" value="'+ dataDetailBT[i].category_code + '" />'+
                    '<input type="hidden" name="description-' + dataDetailBT[i].bt_line_id + '" value="'+ dataDetailBT[i].description + '" />'+
                    '<input type="hidden" name="quantity-' + dataDetailBT[i].bt_line_id + '" value="'+ dataDetailBT[i].qty_need + '" />'+
                    '</tr>';
                $('#table-detail tbody').append(tr);

                $('[name="whId-'+ dataDetailBT[i].bt_line_id +'"]').val(dataDetailBT[i].wh_id);
            }

            $('input:not(.ios-switch)').iCheck({
                  checkboxClass: 'icheckbox_square-aero',
                  radioClass: 'iradio_square-aero',
                  increaseArea: '20%' // optional
            });
            
            $('#all-line').on('ifChanged', function(){
                var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
                if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                    $inputs.iCheck('check');
                } else {
                    $inputs.iCheck('uncheck');
                }
            });

            $('.currency').autoNumeric('init', {mDec: 0});

            $('#modal-lov-bt').modal('hide');
            
        });
    });
</script>
@endsection
