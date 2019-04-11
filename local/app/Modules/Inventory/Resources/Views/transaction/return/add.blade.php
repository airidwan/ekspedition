 @extends('layouts.master')

@section('title', trans('inventory/menu.return-po'))

<?php 
    use App\Modules\Inventory\Model\Transaction\ReceiptLine; 
    use App\Modules\Inventory\Model\Transaction\ReceiptHeader; 
    use App\Modules\Inventory\Model\Master\MasterCategory; 
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.return-po') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->return_id }}">
                        <div class="col-sm-6 portlets">
                            @if(!empty($model->return_id))
                            <div class="form-group {{ $errors->has('returnNumber') ? 'has-error' : '' }}">
                                <label for="returnNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.return-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="returnNumber" name="returnNumber" value="{{ $model->return_number}}" readonly>
                                        @if($errors->has('returnNumber'))
                                        <span class="help-block">{{ $errors->first('returnNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                                $returnDate = new \DateTime($model->return_date);
                            ?>
                            <div class="form-group {{ $errors->has('returnDate') ? 'has-error' : '' }}">
                                <label for="returnDate" class="col-sm-4 control-label">{{ trans('inventory/fields.return-date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="returnDate" name="returnDate" value="{{ $returnDate !== null ? $returnDate->format('d-M-Y') : '' }}" readonly>
                                        @if($errors->has('returnDate'))
                                        <span class="help-block">{{ $errors->first('returnDate') }}</span>
                                        @endif
                                </div>
                            </div>
                            @endif
                            <?php 
                                $receipt       = $model->receipt;
                                $receiptNumber = !empty($receipt) ? $receipt->receipt_number : '';
                                $po = !empty($receipt) ? $receipt->po : null;
                                $poNumber = !empty($po) ? $po->po_number : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('receiptNumber') ? 'has-error' : '' }}">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ count($errors) > 0 ? old('receiptNumber') : $receiptNumber }}" readonly>
                                        <input type="hidden" class="form-control" id="receiptId" name="receiptId" value="{{ count($errors) > 0 ? old('receiptId') : '' }}" >
                                        <span class="btn input-group-addon" data-toggle="{{ empty($model->return_id) ? 'modal' : '' }}" data-target="#modal-lov-receipt"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('receiptNumber'))
                                        <span class="help-block">{{ $errors->first('receiptNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('poNumber') ? 'has-error' : '' }}">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ count($errors) > 0 ? old('poNumber') : $poNumber }}" readonly>
                                        @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('poNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <hr><br>
                            <h3>{{ trans('inventory/fields.receipt') }}</h3>
                            <table id="table-detail" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input name="all-line" id="all-line" type="checkbox" ></th>
                                        <th>{{ trans('inventory/fields.item') }}</th>
                                        <th>{{ trans('operational/fields.item-name') }}</th>
                                        <th>{{ trans('inventory/fields.uom') }}</th>
                                        <th>{{ trans('shared/common.category') }}</th>
                                        <th>{{ trans('inventory/fields.wh') }}</th>
                                        <th>{{ trans('inventory/fields.receipt-quantity') }}</th>
                                        <th>{{ trans('inventory/fields.return-quantity') }}</th>
                                        <th width="400px">{{ trans('shared/common.note') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($errors) > 0 && !empty(old('lineIdHidden')))
                                    @foreach(old('lineIdHidden') as $receiptLineId)
                                    <?php
                                    $receiptLine   = ReceiptLine::find($receiptLineId);
                                    $receiptHeader = $receiptLine !== null ? $receiptLine->header : null;
                                    $poHeader      = $receiptHeader !== null ? $receiptHeader->po : null;
                                    $poLine           = $receiptLine !== null ? $receiptLine->poLine : null;
                                    $item          = $poLine !== null ? $poLine->item()->first() : null;
                                    $wh            = $receiptLine !== null ? $receiptLine->wh : null;
                                    $uom           = $item !== null ? $item->uom : null;
                                    $category      = $item !== null ? $item->category : null;
                                    $max            = $receiptLine->receipt_quantity - $receiptLine->return_quantity; 
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <input name="receiptLineId[]" value="{{ $receiptLine->receipt_line_id }}" type="checkbox" class="rows-check">
                                            </td>
                                            <td>{{ $item !== null ? $item->item_code : '' }}</td>
                                            <td>{{ $item !== null ? $item->description : '' }}</td>
                                            <td>{{ $uom !== null ? $uom->uom_code : '' }}</td>
                                            <td>{{ $category !== null ? $category->description : '' }}</td>
                                            <td>{{ $wh !== null ? $wh->wh_code : '' }}</td>
                                            <td>{{ $receiptLine->receipt_quantity }}</td>
                                            <td>
                                                <input type="number" min="1" max="{{ $max }}" class="form-control currency" name="returnQuantity-{{ $receiptLineId }}" value="{{ old('returnQuantity-'.$receiptLineId) }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="note-{{ $receiptLineId }}" value="{{ old('note-'.$receiptLineId) }}">
                                            </td>
                                            <input type="hidden" name="lineIdHidden[]" value="{{ $receiptLineId }}" />
                                            <input type="hidden" name="itemId-{{ $receiptLineId }}" value="{{ $item !== null ? $item->item_id : '' }}" />
                                            <input type="hidden" name="whId-{{ $receiptLineId }}" value="{{ $wh !== null ? $wh->wh_id : '' }}" />
                                            <input type="hidden" name="categoryCode-{{ $receiptLineId }}" value="{{ $category !== null ? $category->category_code : '' }}" />
                                            <input type="hidden" name="quantityReceipt-'{{ $receiptLineId }}" value="$receiptLine->receipt_quantity" />
                                            </tr>
                                    @endforeach
                                    @endif
                                    @if(!empty($model->receipt_id))
                                    <?php  $lines = $model->lines;
                                           $no=1;
                                    ?>
                                    @foreach($lines as $line)
                                    <?php      
                                    $receiptLine   = ReceiptLine::find($line->receipt_line_id);
                                    $poLine          = !empty($receiptLine) ? $receiptLine->poLine : null;
                                    $item          = !empty($poLine) ? $poLine->item : null;
                                    $wh            = !empty($poLine) ? $poLine->warehouse : null;
                                    $uom           = $item !== null ? $item->uom : null;
                                    $category        = $item !== null ? $item->category : null;
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                        {{ $no++ }}
                                        </td>
                                        <td>{{ $item !== null ? $item->item_code : '' }}</td>
                                        <td>{{ $item !== null ? $item->description : '' }}</td>
                                        <td>{{ $uom !== null ? $uom->uom_code : '' }}</td>
                                        <td>{{ $category !== null ? $category->description : '' }}</td>
                                        <td>{{ $wh !== null ? $wh->wh_code : '' }}</td>
                                        <td class="text-right">{{ $receiptLine !== null ? $receiptLine->receipt_quantity : '' }}</td>
                                        <td class="text-right">{{ $line->return_quantity }}</td>
                                        <td>{{ $line->note }}</td>
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
<div id="modal-lov-receipt" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('inventory/fields.list-receipt') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('inventory/fields.receipt-number') }}</th>
                            <th>{{ trans('inventory/fields.receipt-date') }}</th>
                            <th>{{ trans('purchasing/fields.po-number') }}</th>
                            <th>{{ trans('purchasing/fields.po-date') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionReceipt as $receipt)
                        <?php
                            $receiptLines = \DB::table('inv.v_trans_receipt_line')
                                            ->where('receipt_id', '=', $receipt->receipt_id)
                                            ->whereRaw('v_trans_receipt_line.receipt_quantity > v_trans_receipt_line.return_quantity')
                                            ->where('v_trans_receipt_line.category_code', '<>', MasterCategory::JS)
                                            ->orderBy('receipt_number', 'desc')
                                            ->get();
                        ?>
                        <tr style="cursor: pointer;" data-po="{{ json_encode($receipt) }}" data-line-receipt="{{ json_encode($receiptLines) }}">
                            <?php
                                 $receiptDate = !empty($receipt->receipt_date) ? new \DateTime($receipt->receipt_date) : null;
                                 $poDate = !empty($receipt->po_date) ? new \DateTime($receipt->po_date) : null;
                             ?>
                            <td>{{ $receipt->receipt_number }}</td>
                            <td>{{ !empty($receiptDate) ? $receiptDate->format('d-M-Y') : '' }}</td>
                            <td>{{ $receipt->po_number }}</td>
                            <td>{{ !empty($poDate) ? $poDate->format('d-M-Y') : '' }}</td>
                            <td>{{ $receipt->description_po }}</td>
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
            var dataReceipt = $(this).data('po');
            var dataLineReceipt = $(this).data('line-receipt');

            $('#receiptNumber').val(dataReceipt.receipt_number);
            $('#receiptId').val(dataReceipt.receipt_id);
            $('#poNumber').val(dataReceipt.po_number);

            $('#table-detail tbody').html('');
            for (var i = 0; i < dataLineReceipt.length; i++) {
                    var max = dataLineReceipt[i].receipt_quantity - dataLineReceipt[i].return_quantity; 
                    var tr = '<tr>' +
                    '<td class="text-center">'+
                        '<input name="receiptLineId[]" value="' + dataLineReceipt[i].receipt_line_id +'" type="checkbox" class="rows-check" >'+
                    '</td>'+
                    '<td>' + dataLineReceipt[i].item_code + '</td>' +
                    '<td>' + dataLineReceipt[i].description_item + '</td>' +
                    '<td>' + dataLineReceipt[i].uom_code + '</td>' +
                    '<td>' + dataLineReceipt[i].description_category + '</td>' +
                    '<td>' + dataLineReceipt[i].wh_code + '</td>' +
                    '<td>' + dataLineReceipt[i].receipt_quantity + '</td>' +
                    '<td>'+
                        '<input type="number" min="1" max="'+ max +'" class="form-control currency" name="returnQuantity-' + dataLineReceipt[i].receipt_line_id + '" value="'+ max + '"></td>' + 
                    '<td>'+
                        '<input type="text" class="form-control" name="note-' + dataLineReceipt[i].receipt_line_id + '" value=""></td>' + 
                    '<input type="hidden" name="lineIdHidden[]" value="'+ dataLineReceipt[i].receipt_line_id + '" />'+
                    '<input type="hidden" name="itemId-' + dataLineReceipt[i].receipt_line_id + '" value="'+ dataLineReceipt[i].item_id + '" />'+
                    '<input type="hidden" name="whId-' + dataLineReceipt[i].receipt_line_id + '" value="'+ dataLineReceipt[i].wh_id + '" />'+
                    '<input type="hidden" name="categoryCode-' + dataLineReceipt[i].receipt_line_id + '" value="'+ dataLineReceipt[i].category_code + '" />'+
                    '</tr>';
                $('#table-detail tbody').append(tr);

            }

            $('input:not(.ios-switch)').iCheck({
                  checkboxClass: 'icheckbox_square-aero',
                  radioClass: 'iradio_square-aero',
                  increaseArea: '20%' 
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

            $('#modal-lov-receipt').modal("hide");
        });
    });
</script>
@endsection
