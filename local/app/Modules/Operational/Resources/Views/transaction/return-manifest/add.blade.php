@extends('layouts.master')

@section('title', trans('operational/menu.return-manifest'))

<?php 
use App\Modules\Operational\Model\Transaction\ManifestLine; 
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;
?>

@section('content')
<div class="row"> 
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.return-manifest') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->manifest_return_header_id }}">
                        <div class="col-sm-6 portlets">
                        @if(!empty($model->manifest_return_header_id))
                            <div class="form-group {{ $errors->has('returnNumber') ? 'has-error' : '' }}">
                                <label for="returnNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.return-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="returnNumber" name="returnNumber" value="{{ $model->manifest_return_number}}" readonly>
                                        @if($errors->has('returnNumber'))
                                        <span class="help-block">{{ $errors->first('returnNumber') }}</span>
                                        @endif
                                </div>
                            </div>
                            <?php
                                $returnDate = !empty($model->created_date) ? TimezoneDateConverter::getClientDateTime($model->created_date) :  TimezoneDateConverter::getClientDateTime();
                            ?>
                            <div class="form-group {{ $errors->has('returnDate') ? 'has-error' : '' }}">
                                <label for="returnDate" class="col-sm-4 control-label">{{ trans('inventory/fields.return-date') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="returnDate" name="returnDate" value="{{ $returnDate !== null ? $returnDate->format('d-M-Y') : '' }}" disabled>
                                        @if($errors->has('returnDate'))
                                        <span class="help-block">{{ $errors->first('returnDate') }}</span>
                                        @endif
                                </div>
                            </div>
                            @endif
                            <?php
                                $manifest          = $model->manifest;
                                $manifestNumber    = !empty($manifest) ? $manifest->manifest_number : ''; 
                                $description       = !empty($manifest) ? $manifest->description : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('manifestHeaderId') ? 'has-error' : '' }}">
                                <label for="manifestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="manifestHeaderId" name="manifestHeaderId" value="{{ count($errors) > 0 ? old('manifestHeaderId') : $model->manifest_header_id }}">
                                        <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" value="{{ count($errors) > 0 ? old('manifestNumber') : $manifestNumber }}" readonly>
                                        <span class="btn input-group-addon {{ empty($model->manifest_return_header_id) ? 'md-trigger' : '' }}" data-modal="modal-lov-manifest"><i class="fa fa-search"></i></span>
                                    </div>
                                        @if($errors->has('manifestHeaderId'))
                                        <span class="help-block">{{ $errors->first('manifestHeaderId') }}</span>
                                        @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('operational/fields.description-manifest') }} </label>
                                <div class="col-sm-8">
                                        <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $description }}" readonly>
                                        @if($errors->has('description'))
                                        <span class="help-block">{{ $errors->first('description') }}</span>
                                        @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="4" {{ !empty($model->manifest_return_header_id) ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('note') : $model->description }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <hr><br>
                            <h3>{{ trans('operational/fields.manifest-lines') }}</h3>
                            <table id="table-detail" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input name="all-line" id="all-line" type="checkbox" ></th>
                                        <th>{{ trans('operational/fields.resi-number') }}</th>
                                        <th>{{ trans('operational/fields.item-name') }}</th>
                                        <th>{{ trans('operational/fields.total-coly') }}</th>
                                        <th>{{ trans('operational/fields.coly-sent') }}</th>
                                        <th>{{ trans('inventory/fields.return-quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if (count($errors) > 0 && !empty(old('lineIdHidden')))
                                @foreach(old('lineIdHidden') as $manifestLineId)
                                <?php
                                $manifestLine   = ManifestLine::find($manifestLineId);
                                $manifestHeader = $manifestLine->header()->first();
                                $resi           = $manifestLine->resi()->first();
                                
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <input name="manifestLineId[]"  value="{{ $manifestLine->manifest_line_id }}" type="checkbox" class="rows-check" >
                                        </td>
                                        <td>{{ $resi !== null ? $resi->resi_number : '' }}</td>
                                        <td>{{ $resi !== null ? $resi->item_name : '' }}</td>
                                        <td>{{ $resi !== null ? $resi->totalColy() : '' }}</td>
                                        <td>{{ $manifestLine->coly_sent }}</td>
                                        <td>
                                            <input type="number" min="1" max="{{ $manifestLine->quantity_remain }}" class="form-control currency" name="returnQuantity-{{ $manifestLineId }}" value="{{ old('returnQuantity-'.$manifestLineId) }}"></td>
                                        <input type="hidden" name="lineIdHidden[]" value="{{ $manifestLineId }}" />
                                        <input type="hidden" name="quantity-'{{ $manifestLineId }}" value="$manifestLine->coly_sent" />
                                        </tr>
                                @endforeach
                                @endif
                                @if(!empty($model->manifest_return_header_id))
                                    <?php  $lines = $model->lines;
                                           $no=1;
                                    ?>
                                    @foreach($lines as $line)
                                    <?php
                                    $manifestLine   = ManifestLine::find($line->manifest_line_id);
                                    $resi           = $manifestLine->resi()->first();
                                    $manifestHeader = $manifestLine->header()->first();
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                        {{ $no++ }}
                                        </td>
                                        <td>{{ $resi !== null ? $resi->resi_number : '' }}</td>
                                        <td>{{ $resi !== null ? $resi->item_name : '' }}</td>
                                        <td>{{ $resi !== null ? $resi->totalColy() : '' }}</td>
                                        <td class="text-center">{{ $manifestLine->coly_sent }}</td>
                                        <td class="text-center">{{ $line->coly_return }}</td>
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
                                @if(!empty($model->manifest_return_header_id))
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->manifest_return_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(empty($model->manifest_return_header_id))
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                    <button type="submit" name="btn-close-warning" class="btn btn-sm btn-danger">
                                        <i class="fa fa-save"></i> {{ trans('shared/common.close') }}
                                    </button>
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
<div class="md-modal-lg md-fade-in-scale-up" id="modal-lov-manifest">
    <div class="md-content">
        <div class="md-close-btn"><a class="md-close"><i class="fa fa-times"></i></a></div>
        <h3><strong>{{ trans('operational/fields.list-manifest') }}</strong></h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('operational/fields.manifest-number') }}<hr/>
                                    {{ trans('operational/fields.route-code') }}</th>
                                <th>{{ trans('operational/fields.kota-asal') }}<hr/>
                                    {{ trans('operational/fields.kota-tujuan') }}</th>
                                <th>{{ trans('operational/fields.truck') }}<hr/>
                                    {{ trans('operational/fields.driver') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($optionManifest as $manifest)
                            <?php
                                $manifestLines = \DB::table('op.trans_manifest_line')
                                                ->select('trans_manifest_line.*', 'trans_resi_header.item_name', 'trans_resi_header.resi_number')
                                                ->join('op.trans_resi_line', 'trans_resi_line.resi_header_id', '=', 'trans_manifest_line.resi_header_id')
                                                ->join('op.trans_resi_header', 'trans_resi_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                                                ->where('manifest_header_id', '=', $manifest->manifest_header_id)
                                                ->where('quantity_remain', '<>', 0)
                                                ->orderBy('trans_manifest_line.created_date', 'desc')
                                                ->distinct()
                                                ->get();
                                $arrLine = []; 

                                foreach($manifestLines as $line) {
                                    $modelResi = App\Modules\Operational\Model\Transaction\TransactionResiHeader::find($line->resi_header_id);
                                    $line->customer_name = $modelResi->getCustomerName();
                                    $line->total_coly   = $modelResi->totalColy();
                                    $arrLine [] = $line;
                                }

                                $length = strlen($manifest->description);

                            ?>

                            <tr style="cursor: pointer;" data-manifest="{{ json_encode($manifest) }}" data-detail-manifest="{{ json_encode($arrLine) }}">
                                <td>{{ $manifest->manifest_number }}<hr/>
                                    {{ $manifest->route_code }}</td>
                                <td>{{ $manifest->city_start_name }}<hr/>
                                    {{ $manifest->city_end_name }}</td>
                                <td>{{ $manifest->police_number }}<hr/>
                                    {{ $manifest->driver_name }}</td>
                                <td>{{ $length > 50 ? substr($manifest->description, 0, 50).'.....' : $manifest->description }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

    $('#all-line').on('ifChanged', toggleAllLine);

    $('#datatables-lov tbody').on('click', 'tr', function () {
        var dataManifest = $(this).data('manifest');
        var dataDetailManifest = $(this).data('detail-manifest');

        $('#manifestNumber').val(dataManifest.manifest_number);
        $('#description').val(dataManifest.description);
        $('#manifestHeaderId').val(dataManifest.manifest_header_id);

        $('#table-detail tbody').html('');
        for (var i = 0; i < dataDetailManifest.length; i++) {
                var tr = '<tr>' +
                '<td class="text-center">'+
                    '<input name="manifestLineId[]" value="' + dataDetailManifest[i].manifest_line_id +'" type="checkbox" class="rows-check">'+
                '</td>'+
                '<td>' + dataDetailManifest[i].resi_number + '</td>' +
                '<td>' + dataDetailManifest[i].item_name + '</td>' +
                '<td class="text-right">' + dataDetailManifest[i].total_coly + '</td>' +
                '<td class="text-right">' + dataDetailManifest[i].coly_sent + '</td>' +
                '<td>'+
                    '<input type="number" min="1" max="'+ dataDetailManifest[i].quantity_remain +'" class="form-control currency" name="returnQuantity-' + dataDetailManifest[i].manifest_line_id + '" value="'+ dataDetailManifest[i].quantity_remain + '"></td>' +
                '<input type="hidden" name="lineIdHidden[]" value="'+ dataDetailManifest[i].manifest_line_id + '" />'+
                '<input type="hidden" name="resiId-' + dataDetailManifest[i].manifest_line_id + '" value="'+ dataDetailManifest[i].resi_header_id + '" />'+
                '<input type="hidden" name="quantity-' + dataDetailManifest[i].manifest_line_id + '" value="'+ dataDetailManifest[i].coly_sent + '" />'+
                '</tr>';
            $('#table-detail tbody').append(tr);
        }

        $('input:not(.ios-switch)').iCheck({
              checkboxClass: 'icheckbox_square-aero',
              radioClass: 'iradio_square-aero',
              increaseArea: '20%' // optional
        });

        $('#all-line').on('ifChanged', toggleAllLine);
        $('.currency').autoNumeric('init', {mDec: 0});
        $('#modal-lov-manifest').removeClass("md-show");
    });
});

var toggleAllLine = function(){
    var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
    if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
        $inputs.iCheck('check');
    } else {
        $inputs.iCheck('uncheck');
    }
};
</script>
@endsection
