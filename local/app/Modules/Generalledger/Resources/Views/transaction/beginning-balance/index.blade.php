@extends('layouts.master')

@section('title', trans('general-ledger/menu.beginning-balance'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.beginning-balance') }}</h2>
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
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.bank-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="bankName" name="bankName" value="{{ !empty($filters['bankName']) ? $filters['bankName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="accountName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="accountName" name="accountName" value="{{ !empty($filters['accountName']) ? $filters['accountName'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
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
                                <a href="{{ URL($url.'/print-pdf') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
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
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('general-ledger/fields.bank-name') }}</th>
                                <th>{{ trans('general-ledger/fields.account-name') }}</th>
                                <th>{{ trans('general-ledger/fields.beginning-balance') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <?php $date = !empty($model->balance_date) ? new \DateTime($model->balance_date) : null; ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->bank_name }}</td>
                                <td>{{ $model->account_name }}</td>
                                <td class="text-right">{{ number_format($model->beginning_balance) }}</td>
                                <td>{{ !empty($date) ? $date->format('d-m-Y') : '' }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->beginning_balance_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
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

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        var segmentName = function () {
            if ($('#segmentName').val() == 'Account')  {
                $('#identifier').prop('disabled', false);
            }
            else {
                $('#identifier').prop('disabled', 'disabled');
            }
        };
        $(segmentName);
        $("#segmentName").change(segmentName);
    });
</script>
@endsection
