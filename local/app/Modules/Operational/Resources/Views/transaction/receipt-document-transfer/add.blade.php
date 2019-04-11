@extends('layouts.master')

@section('title', trans('operational/menu.receipt-document-transfer'))

<?php use App\Modules\Operational\Model\Transaction\DocumentTransferLine; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.receipt-document-transfer') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->document_transfer_header_id }}">
                        <div class="col-sm-6 portlets">
                        @if(!empty($model->document_transfer_header_id))
                            <div class="form-group {{ $errors->has('receiptNumber') ? 'has-error' : '' }}">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('operational/fields.receipt-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ $model->receipt_document_transfer_number}}" readonly>
                                        @if($errors->has('receiptNumber'))
                                        <span class="help-block">{{ $errors->first('receiptNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                                $receiptDate = new \DateTime($model->created_date);
                            ?>
                            <div class="form-group {{ $errors->has('receiptDate') ? 'has-error' : '' }}">
                                <label for="receiptDate" class="col-sm-4 control-label">{{ trans('operational/fields.receipt-date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="receiptDate" name="receiptDate" value="{{ $receiptDate !== null ? $receiptDate->format('d-M-Y') : '' }}" readonly>
                                        @if($errors->has('receiptDate'))
                                        <span class="help-block">{{ $errors->first('receiptDate') }}</span>
                                        @endif
                                </div>
                            </div>
                            @endif
                            <?php
                                $documentTransfer          = $model->documentTransferHeader;
                                $documentTransferNumber    = !empty($documentTransfer) ? $documentTransfer->document_transfer_number : '';
                                $description = !empty($documentTransfer) ? $documentTransfer->description : '';
                            ?>
                            <div class="form-group {{ $errors->has('documentTransferHeaderId') ? 'has-error' : '' }}">
                                <label for="documentTransferHeaderId" class="col-sm-4 control-label">{{ trans('operational/fields.document-transfer-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="documentTransferHeaderId" name="documentTransferHeaderId" value="{{ count($errors) > 0 ? old('documentTransferHeaderId') : $model->document_transfer_header_id }}">
                                        <input type="text" class="form-control" id="documentTransferNumber" name="documentTransferNumber" value="{{ count($errors) > 0 ? old('documentTransferNumber') : $documentTransferNumber }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="{{ empty($model->document_transfer_header_id) ? 'modal' : '' }}" data-target="#modal-document-transfer"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('documentTransferHeaderId'))
                                        <span class="help-block">{{ $errors->first('documentTransferHeaderId') }}</span>
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
                            <strong> {{ trans('operational/fields.document-transfer-lines') }} </strong>
                            <table id="table-detail" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>
                                            @if(empty($model->document_transfer_header_id))
                                                <input name="all-line" id="all-line" type="checkbox" >
                                            @endif
                                        </th>
                                        <th>{{ trans('operational/fields.resi-number') }}</th>
                                        <th>{{ trans('operational/fields.item-name') }}</th>
                                        <th>{{ trans('operational/fields.sender-name') }}</th>
                                        <th>{{ trans('operational/fields.receiver-name') }}</th>
                                        <th>{{ trans('shared/common.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if (count($errors) > 0 && !empty(old('lineIdHidden')))
                                @foreach(old('lineIdHidden') as $documentTransferLineId)
                                <?php
                                $documentTransferLine = DocumentTransferLine::find($documentTransferLineId);
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <input name="documentTransferLineId[]" value="{{ $documentTransferLine->line_id }}" type="checkbox" class="rows-check">
                                        </td>
                                        <td>{{ $documentTransferLine->resi->resi_number }}</td>
                                        <td>{{ $documentTransferLine->resi->item_name }}</td>
                                        <td>{{ $documentTransferLine->resi->sender_name }}</td>
                                        <td>{{ $documentTransferLine->resi->receiver_name }}</td>
                                        <td>{{ $documentTransferLine->resi->description }}</td>
                                        <input type="hidden" name="lineIdHidden[]" value="{{ $documentTransferLineId }}" />
                                        </tr>
                                @endforeach
                                @endif
                                @if(!empty($model->document_transfer_header_id))
                                    <?php  $lines = $model->lines;
                                           $no=1;
                                    ?>
                                    @foreach($lines as $line)
                                    <?php
                                    $documentTransferLine = DocumentTransferLine::find($line->document_transfer_line_id);
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                        {{ $no++ }}
                                        </td>
                                        <td>{{ $documentTransferLine->resi->resi_number }}</td>
                                        <td>{{ $documentTransferLine->resi->item_name }}</td>
                                        <td>{{ $documentTransferLine->resi->sender_name }}</td>
                                        <td>{{ $documentTransferLine->resi->receiver_name }}</td>
                                        <td>{{ $documentTransferLine->resi->description }}</td>
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
                                @if(empty($model->document_transfer_header_id))
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
<div id="modal-document-transfer" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Document Transfer List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.document-transfer-number') }}</th>
                            <th>{{ trans('operational/fields.driver-name') }}</th>
                            <th>{{ trans('operational/fields.truck-code') }}</th>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('shared/common.date') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionDocumentTransfer as $documentTransfer)
                        <?php
                             $date    = !empty($documentTransfer->created_date) ? new \DateTime($documentTransfer->created_date) : null;
                            $documentTransferLines = \DB::table('op.trans_document_transfer_line')
                                            ->select('trans_document_transfer_line.*', 'trans_resi_header.resi_number', 'trans_resi_header.item_name', 'trans_resi_header.sender_name', 'trans_resi_header.receiver_name', 'trans_resi_header.description')
                                            ->leftJoin('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_document_transfer_line.resi_header_id')
                                            ->where('trans_document_transfer_line.document_transfer_header_id', '=', $documentTransfer->document_transfer_header_id)
                                            ->whereNull('trans_document_transfer_line.receipt_branch_id')
                                            ->get();
                        ?>
                        <tr style="cursor: pointer;" data-document-transfer="{{ json_encode($documentTransfer) }}" data-detail-document-transfer="{{ json_encode($documentTransferLines) }}">
                            <td>{{ $documentTransfer->document_transfer_number }}</td>
                            <td>{{ $documentTransfer->driver_name }}</td>
                            <td>{{ $documentTransfer->truck_code }}</td>
                            <td>{{ $documentTransfer->police_number }}</td>
                            <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                            <td>{{ $documentTransfer->description }}</td>
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
            var data = $(this).data('document-transfer');
            var dataDetail = $(this).data('detail-document-transfer');

            $('#documentTransferNumber').val(data.document_transfer_number);
            $('#description').val(data.description);
            $('#documentTransferHeaderId').val(data.document_transfer_header_id);

            $('#table-detail tbody').html('');
            for (var i = 0; i < dataDetail.length; i++) {
                    var tr = '<tr>' +
                    '<td class="text-center">'+
                        '<input name="documentTransferLineId[]" value="' + dataDetail[i].document_transfer_line_id +'" type="checkbox" class="rows-check">'+
                    '</td>'+
                    '<td>' + dataDetail[i].resi_number + '</td>' +
                    '<td>' + dataDetail[i].item_name + '</td>' +
                    '<td>' + dataDetail[i].sender_name + '</td>' +
                    '<td>' + dataDetail[i].receiver_name + '</td>' +
                    '<td>' + dataDetail[i].description + '</td>' +
                    '<input type="hidden" name="lineIdHidden[]" value="'+ dataDetail[i].document_transfer_line_id + '" />'+
                    '</tr>';
                $('#table-detail tbody').append(tr);
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

            $('#modal-document-transfer').modal("hide");
        });
    });
</script>
@endsection
