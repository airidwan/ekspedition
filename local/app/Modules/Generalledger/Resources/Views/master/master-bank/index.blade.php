@extends('layouts.master')

@section('title', trans('general-ledger/menu.master-bank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.master-bank') }}</h2>
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
                            <div class="form-group">
                                <label for="accountNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="accountNumber" name="accountNumber" value="{{ !empty($filters['accountNumber']) ? $filters['accountNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="npwp" class="col-sm-4 control-label">{{ trans('general-ledger/fields.npwp') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="npwp" name="npwp" value="{{ !empty($filters['npwp']) ? $filters['npwp'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="coaCode" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="coaCode" name="coaCode" value="{{ !empty($filters['coaCode']) ? $filters['coaCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="coaDescription" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa-description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="coaDescription" name="coaDescription" value="{{ !empty($filters['coaDescription']) ? $filters['coaDescription'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = !empty($filters['status']) || !Session::has('filters') ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
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
                                <th>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('general-ledger/fields.account-name') }}</th>
                                <th>{{ trans('general-ledger/fields.account-number') }}</th>
                                <th>{{ trans('general-ledger/fields.npwp') }}</th>
                                <th>{{ trans('general-ledger/fields.coa-bank') }}</th>
                                <th>{{ trans('general-ledger/fields.coa-description') }}</th>
                                <th>{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('shared/common.deskripsi') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->bank_name }}</td>
                                <td>{{ $model->type }}</td>
                                <td>{{ $model->account_name }}</td>
                                <td>{{ $model->account_number }}</td>
                                <td>{{ $model->npwp }}</td>
                                <td>{{ $model->coa_bank_code }}</td>
                                <td>{{ $model->coa_bank_description }}</td>
                                <td>{{ $model->bank_address }}</td>
                                <td>{{ $model->description }}</td>
                                <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->bank_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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
        
    });
</script>
@endsection
