@extends('layouts.master')

@section('title', trans('operational/menu.tracking-resi'))

@section('header')
@parent
<style type="text/css">
.modal-data-title{
    background-color: #eee;
    font-weight: bold;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.tracking-resi') }}</h2>
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
                        <?php
                        $resiNumber  = '';
                        if (!empty(old('resiNumber'))) {
                            $resiNumber = old('resiNumber');
                        } elseif (!empty($filters['resiNumber'])) {
                            $resiNumber = $filters['resiNumber'];
                        }
                        ?>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ $resiNumber }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <h4>Goods Position</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>    
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('operational/fields.position') }}</th>
                                <th>{{ trans('operational/fields.coly') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            @foreach($data as $item)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $item['date'] }}</td>
                                <td>{{ $item['position'] }}</td>
                                <td class="text-right">{{ number_format($item['coly']) }}</td>
                                <td>{{ $item['description'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr>
                <h4>History Transaction</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>    
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.transaction') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('shared/common.user') }}</th>
                                <th>{{ trans('shared/common.data') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            @foreach($history as $item)
                            <?php $date = !empty($item->transaction_date) ? new \DateTime($item->transaction_date) : null; ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y H:i') : '' }}</td>
                                <td>{{ $item->transaction_name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->full_name }}</td>
                                <td class="text-center">
                                    <a href="#" data-json="{{ $item->data }}" data-toggle="tooltip" class="btn-data btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-search"></i>
                                    </a>
                                </td>
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

@section('modal')
@parent
<div id="modal-lov-data" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 portlets">
                        <table class="table table-bordered" cellspacing="0" width="100%">
                            <tbody>
                                <tr><td colspan="2" class="modal-data-title">Header</td></tr>
                                <tr><td colspan="2" id="modal-data-header"></td></tr>
                                <tr><td colspan="2" class="modal-data-title">Line Detail</td></tr>
                            </tbody>
                            <tbody id="modal-data-line-detail">
                                <tr><td >Line Detail</td><td >Line Volume</td></tr>
                            </tbody>
                            <tbody>
                                <tr><td colspan="2" class="modal-data-title">Line Unit</td></tr>
                            </tbody>
                            <tbody id="modal-data-line-unit">
                                <tr><td colspan="2" >Line Unit</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
$(document).on('ready', function(){
    $('.btn-data').on('click', showModalData);
});

var showModalData = function() {
    var data = $(this).data('json');
    $('#modal-lov-data').find('.modal-title').html(data.resi_number);
    console.log(data.total_amount);
    $('#modal-data-header').html(
        '{{ trans('operational/fields.customer-sender') }}: ' + data.customer_sender + ', ' +
        '{{ trans('operational/fields.sender') }}: ' + data.sender_name + ', ' +
        '{{ trans('operational/fields.sender-address') }}: ' + data.sender_address + ', ' +
        '{{ trans('operational/fields.sender-phone') }}: ' + data.sender_phone + ', ' +
        '{{ trans('operational/fields.customer-receiver') }}: ' + data.customer_receiver + ', ' +
        '{{ trans('operational/fields.receiver') }}: ' + data.receiver_name + ', ' +
        '{{ trans('operational/fields.receiver-address') }}: ' + data.receiver_address + ', ' +
        '{{ trans('operational/fields.receiver-phone') }}: ' + data.receiver_phone + ', ' +
        '{{ trans('operational/fields.route-code') }}: ' + data.route_code + ', ' +
        '{{ trans('operational/fields.item-name') }}: ' + data.item_name + ', ' +
        '{{ trans('operational/fields.total-weight') }}: ' + data.total_weight + ', ' +
        '{{ trans('operational/fields.total-volume') }}: ' + data.total_volume + ', ' +
        '{{ trans('shared/common.type') }}: ' + data.type + ', ' +
        '{{ trans('operational/fields.payment') }}: ' + data.payment + ', ' +
        '{{ trans('operational/fields.total-amount') }}: ' + data.total_amount + ', ' +
        '{{ trans('operational/fields.discount') }}: ' + data.discount + ', ' +
        '{{ trans('shared/common.total') }}: ' + data.total + ', ' +
        '{{ trans('shared/common.description') }}: ' + data.description + ', ' +
        '{{ trans('marketing/fields.pickup-request-number') }}: ' + data.pickup_request_number + ', ' +
        '{{ trans('shared/common.status') }}: ' + data.status + ', ' +
        '{{ trans('shared/common.branch') }}: ' + data.branch_code
    );

    $('#modal-data-line-detail').html('');
    data.line_details.forEach(function(line) {
        var htmlTr = '<tr><td>' +
                        '{{ trans('operational/fields.item-name') }}: ' + line.item_name + ', ' +
                        '{{ trans('operational/fields.coly') }}: ' + line.coly + ', ' +
                        '{{ trans('operational/fields.qty-weight') }}: ' + line.qty_weight + ', ' +
                        '{{ trans('operational/fields.weight-unit') }}: ' + line.weight_unit + ', ' +
                        '{{ trans('operational/fields.weight') }}: ' + line.weight + ', ' +
                        '{{ trans('operational/fields.price-weight') }}: ' + line.price_weight + ', ' +
                        '{{ trans('operational/fields.volume') }}: ' + line.volume + ', ' +
                        '{{ trans('operational/fields.price-volume') }}: ' + line.price_volume + ', ' +
                        '{{ trans('operational/fields.price') }}: ' + line.price +
                        '</td><td>';
        line.volumes.forEach(function(volume) {
            htmlTr += '{{ trans('operational/fields.qty-volume') }}: ' + volume.qty_volume + ', ' +
                            'L: ' + volume.long + ', ' +
                            'W: ' + volume.width + ', ' +
                            'H: ' + volume.height + ', ' +
                            '{{ trans('operational/fields.volume') }}: ' + volume.volume + ', ' +
                            '{{ trans('operational/fields.total-volume') }}: ' + volume.total_volume + '<hr/>';
        });

        htmlTr += '</td></tr>';

        $('#modal-data-line-detail').append(htmlTr);
    })

    $('#modal-data-line-unit').html('');
    data.line_units.forEach(function(line) {
        $('#modal-data-line-unit').append(
            '<tr><td colspan="2" >' +
            '{{ trans('operational/fields.item-name') }}: ' + line.item_name + ', ' +
            '{{ trans('operational/fields.coly') }}: ' + line.coly + ', ' +
            '{{ trans('operational/fields.price') }}: ' + line.price +
            '</td></tr>'
        );
    })

    $('#modal-lov-data').modal('show');
};

var toTitleCase = function(str)
{
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
};
</script>


@endsection