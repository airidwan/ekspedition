@extends('layouts.web')

@section('title', trans('general-ledger/menu.cash-flow'))
<?php 
use App\Modules\Generalledger\Http\Controllers\Report\CashFlowWebController;
?>

@section('header')
@parent
<style type="text/css">
.full-content-center {
    max-width: none;
    margin-top: 10px;
    text-align: inherit;
}
.price {
    font-weight: bold;
    font-size: 48px;
}

table.scroll {
    width: 100%; /* Optional */
    /* border-collapse: collapse; */
    border-spacing: 0;
    /*border: 2px solid black;*/
}

table.scroll tbody,
table.scroll thead { display: block; }

thead tr th { 
    height: 30px;
    line-height: 30px;
    /*text-align: left;*/
}

table.scroll tbody {
    height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
}

/*tbody { border-top: 2px solid black; }*/

tbody td, thead th {
    width: 5%; /* Optional */
    border-right: 1px solid black;
}

tbody td:last-child, thead th:last-child {
    border-right: none;
}
</style>
@endsection

@section('content')
<div class="row" style="font-size:14pt;">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('general-ledger/menu.cash-flow') }}</h2>
            </div>
            <div class="widget-content padding" style="font-size:14pt">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">Tanggal dari</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateFrom']) ? $filters['dateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">Tanggal sampai</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateTo']) ? $filters['dateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                            $branchId  = '';
                            if (!empty(old('branchId'))) {
                                $branchId = old('branchId');
                            } elseif (!empty($filters['branchId'])) {
                                $branchId = $filters['branchId'];
                            }
                            ?>
                            <div class="form-group {{ $errors->has('branchId') ? 'has-error' : '' }}">
                                <label for="branchId" class="col-sm-4 control-label">Cabang </label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="branchId" id="branchId">
                                        <option value="" >Semua Cabang</option>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ $branchId == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('branchId'))
                                        <span class="help-block">{{ $errors->first('branchId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type" class="col-sm-4 control-label">Tipe</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="type">
                                        <option value="">Semua Kas</option>
                                        @foreach($optionType as $type)
                                            <option value="{{ $type }}" {{ !empty($filters['type']) && $filters['type'] == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-money"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                @if($filters['type'] == CashFlowWebController::CASH_IN || empty($filters['type']))
                <h4>Kas Masuk</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered scroll" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>    
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('operational/fields.resi') }}</th>
                                <th>{{ trans('operational/fields.route') }}</th>
                                <th>{{ trans('operational/fields.sender') }}</th>
                                <th>{{ trans('operational/fields.receiver') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('general-ledger/fields.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; $totalIncome = 0;?>
                            @foreach($income as $item)
                            <?php 
                            $date = !empty($item->created_date) ? new \DateTime($item->created_date) : null; 
                            $totalIncome += $item->amount;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y H:i') : '' }}</td>
                                <td>{{ $item->resi_number }}</td>
                                <td>{{ $item->route_code }}</td>
                                <td>{{ $item->sender_name }}</td>
                                <td>{{ $item->receiver_name }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-right">{{ number_format($item->amount) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total Kas Masuk <strong>Rp. {{ number_format($totalIncome) }}</strong> </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
                @if($filters['type'] == CashFlowWebController::CASH_OUT || empty($filters['type']))
                <h4>Kas Keluar</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered scroll" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>    
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('payable/fields.payment-number') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('general-ledger/fields.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; $totalExpense = 0;?>
                            @foreach($expense as $item)
                            <?php 
                            $date = !empty($item->created_date) ? new \DateTime($item->created_date) : null; 
                            $totalExpense += ($item->total_amount + $item->total_interest);
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y H:i') : '' }}</td>
                                <td>{{ $item->payment_number }}</td>
                                <td>{{ $item->note }}</td>
                                <td class="text-right">{{ number_format($item->total_amount + $item->total_interest) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><strong>Total</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalExpense) }}</strong></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total Kas Keluar <strong>Rp. {{ number_format($totalExpense) }}</strong> </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    // Change the selector if needed
    var $table = $('table.scroll'),
        $bodyCells = $table.find('tbody tr:first').children(),
        colWidth;

    // Adjust the width of thead cells when window resizes
    $(window).resize(function() {
        // Get the tbody columns width array
        colWidth = $bodyCells.map(function() {
            return $(this).width();
        }).get();
        
        // Set the width of thead columns
        $table.find('thead tr').children().each(function(i, v) {
            $(v).width(colWidth[i]);
        });    
    }).resize(); // Trigger resize handler
</script>
@endsection