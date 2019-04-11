@extends('layouts.master')

@section('title', trans('inventory/menu.receipt-po'))

<?php use App\Modules\Purchasing\Model\Transaction\PurchaseOrderLine; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.receipt-po') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->receipt_id }}">
                        <div class="col-sm-6 portlets">
                            @if(!empty($model->receipt_id))
                            <div class="form-group {{ $errors->has('receiptNumber') ? 'has-error' : '' }}">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ $model->receipt_number}}" readonly>
                                        @if($errors->has('receiptNumber'))
                                        <span class="help-block">{{ $errors->first('receiptNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                                $receiptDate = new \DateTime($model->receipt_date);
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
                                $po          = $model->po;
                                $poNumber    = !empty($po) ? $po->po_number : ''; 
                                $description = !empty($po) ? $po->description : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('poNumber') ? 'has-error' : '' }}">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.po-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="poHeaderId" name="poHeaderId" value="{{ count($errors) > 0 ? old('poHeaderId') : $model->po_header_id }}">
                                        <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ count($errors) > 0 ? old('poNumber') : $poNumber }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="{{ empty($model->receipt_id) ? 'modal' : '' }}" data-target="#modal-lov-po"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('poNumber') }}</span>
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
                            <h3>{{ trans('inventory/fields.po-lines') }}</h3>
                            <table id="table-detail" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input name="all-line" id="all-line" type="checkbox" ></th>
                                        <th>{{ trans('inventory/fields.item') }}</th>
                                        <th>{{ trans('shared/common.description') }}</th>
                                        <th>{{ trans('inventory/fields.order-quantity') }}</th>
                                        <th>{{ trans('inventory/fields.uom') }}</th>
                                        <th>{{ trans('shared/common.category') }}</th>
                                        <th>{{ trans('inventory/fields.po-number') }}</th>
                                        <th>{{ trans('inventory/fields.receipt-quantity') }}</th>
                                        <th>{{ trans('inventory/fields.wh') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if (count($errors) > 0 && !empty(old('lineIdHidden')))
                                @foreach(old('lineIdHidden') as $poLineId)
                                <?php
                                $poLine = PurchaseOrderLine::find($poLineId);
                                $item   = $poLine->item()->first();
                                $uom    = $item !== null ? $item->uom : null;
                                $category = $item !== null ? $item->category : null;
                                $poHeader = $poLine->purchaseOrderHeader()->first();
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <input name="poLineId[]" value="{{ $poLine->line_id }}" type="checkbox" class="rows-check">
                                        </td>
                                        <td>{{ $item !== null ? $item->item_code : '' }}</td>
                                        <td>{{ $item !== null ? $item->description : '' }}</td>
                                        <td>{{ $poLine->quantity_need }}</td>
                                        <td>{{ $uom !== null ? $uom->uom_code : '' }}</td>
                                        <td>{{ $category !== null ? $category->description : '' }}</td>
                                        <td>{{ $poHeader !== null ? $poHeader->po_number : '' }}</td>
                                        <td>
                                            <input type="number" min="1" max="{{ $poLine->quantity_remain }}" class="form-control currency" name="receiptQuantity-{{ $poLineId }}" value="{{ old('receiptQuantity-'.$poLineId) }}"></td>
                                        <td>
                                            <select class="form-control" name="whId-{{ $poLineId }}" >'+
                                                @foreach($optionWarehouse as $warehouse)
                                                <option value="{{ $warehouse->wh_id }}" {{ $warehouse->wh_id == old('whId-'.$poLineId) ? 'selected' : '' }}>{{ $warehouse->wh_code }}</option>
                                                @endforeach
                                            </select> 
                                        </td>
                                        <input type="hidden" name="lineIdHidden[]" value="{{ $poLineId }}" />
                                        <input type="hidden" name="unitPrice-{{ $poLineId }}" value="{{ $poLine !== null ? $poLine->unit_price : '' }}" />
                                        <input type="hidden" name="categoryCode-{{ $poLineId }}" value="{{ $category !== null ? $category->category_code : '' }}" />
                                        <input type="hidden" name="quantity-{{ $poLineId }}" value="{{ $poLine->quantity_need }}" />
                                        </tr>
                                @endforeach
                                @endif
                                @if(!empty($model->receipt_id))
                                    <?php  $lines = $model->lines;
                                           $no=1;
                                    ?>
                                    @foreach($lines as $line)
                                    <?php      
                                    $poLine = PurchaseOrderLine::find($line->po_line_id);
                                    $item   = $poLine->item()->first();
                                    $wh     = $poLine->warehouse;
                                    $uom    = $item !== null ? $item->uom : null;
                                    $category = $item !== null ? $item->category : null;
                                    $poHeader = $poLine->purchaseOrderHeader()->first();
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                        {{ $no++ }}
                                        </td>
                                        <td>{{ $item !== null ? $item->item_code : '' }}</td>
                                        <td>{{ $item !== null ? $item->description : '' }}</td>
                                        <td class="text-center">{{ $poLine->quantity_need }}</td>
                                        <td>{{ $uom !== null ? $uom->uom_code : '' }}</td>
                                        <td>{{ $category !== null ? $category->description : '' }}</td>
                                        <td>{{ $poHeader !== null ? $poHeader->po_number : '' }}</td>
                                        <td class="text-center">{{ $line->receipt_quantity }}</td>
                                        <td>{{ $wh !== null ? $wh->wh_code : '' }}</td>
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
                                @if(empty($model->receipt_id))
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
<div id="modal-lov-po" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('inventory/fields.list-po') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('purchasing/fields.po-number') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('inventory/fields.vendor-code') }}</th>
                            <th>{{ trans('inventory/fields.vendor-name') }}</th>
                            <th>{{ trans('shared/common.type') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionPO as $po)
                        <?php
                            $poLines = \DB::table('po.v_po_lines')
                                            ->where('header_id', '=', $po->header_id)
                                            ->where('quantity_remain', '<>', 0)
                                            ->orderBy('po_number', 'desc')
                                            ->get();
                        ?>
                        <tr style="cursor: pointer;" data-po="{{ json_encode($po) }}" data-detail-po="{{ json_encode($poLines) }}">
                            <td>{{ $po->po_number }}</td>
                            <td>{{ $po->description }}</td>
                            <td>{{ $po->vendor_code }}</td>
                            <td>{{ $po->vendor_name }}</td>
                            <td>{{ $po->type_name }}</td>
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
            var dataPO = $(this).data('po');
            var dataDetailPO = $(this).data('detail-po');

            $('#poNumber').val(dataPO.po_number);
            $('#description').val(dataPO.description);
            $('#poHeaderId').val(dataPO.header_id);

            $('#table-detail tbody').html('');
            for (var i = 0; i < dataDetailPO.length; i++) {
                    var tr = '<tr>' +
                    '<td class="text-center">'+
                        '<input name="poLineId[]" value="' + dataDetailPO[i].line_id +'" type="checkbox" class="rows-check">'+
                    '</td>'+
                    '<td>' + dataDetailPO[i].item_code + '</td>' +
                    '<td>' + dataDetailPO[i].item_description + '</td>' +
                    '<td>' + dataDetailPO[i].quantity_need + '</td>' +
                    '<td>' + dataDetailPO[i].uom_code + '</td>' +
                    '<td>' + dataDetailPO[i].category_description + '</td>' +
                    '<td>' + dataDetailPO[i].po_number + '</td>' +
                    '<td>'+
                        '<input type="number" min="1" max="'+ dataDetailPO[i].quantity_remain +'" class="form-control currency" name="receiptQuantity-' + dataDetailPO[i].line_id + '" value="'+ dataDetailPO[i].quantity_remain + '"></td>' + 
                    '<td>'+
                        '<select class="form-control" name="whId-' + dataDetailPO[i].line_id + '" >'+
                            @foreach($optionWarehouse as $warehouse)
                            '<option value="{{ $warehouse->wh_id }}">{{ $warehouse->wh_code }}</option>'+
                            @endforeach
                        '</select>'+ 
                    '</td>' +
                    '<input type="hidden" name="lineIdHidden[]" value="'+ dataDetailPO[i].line_id + '" />'+
                    '<input type="hidden" name="unitPrice-' + dataDetailPO[i].line_id + '" value="'+ dataDetailPO[i].unit_price + '" />'+
                    '<input type="hidden" name="categoryCode-' + dataDetailPO[i].line_id + '" value="'+ dataDetailPO[i].category_code + '" />'+
                    '<input type="hidden" name="quantity-' + dataDetailPO[i].line_id + '" value="'+ dataDetailPO[i].quantity_need + '" />'+
                    '</tr>';
                $('#table-detail tbody').append(tr);

                $('[name="whId-'+ dataDetailPO[i].line_id +'"]').val(dataDetailPO[i].wh_id);
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

            $('#modal-lov-po').modal("hide");
        });
    });
</script>
@endsection
