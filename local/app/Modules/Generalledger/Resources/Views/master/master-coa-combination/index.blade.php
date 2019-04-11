@extends('layouts.master')

@section('title', trans('general-ledger/menu.master-coa-combination'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.master-coa-combination') }}</h2>
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
                        <div class="col-sm-8 portlets">
                            <div class="form-group">
                                <label for="label" class="col-sm-4 control-label"></label>
                                <label for="label" class="col-sm-4 control-label">{{ trans('general-ledger/fields.from') }}</label>
                                <label for="label" class="col-sm-4 control-label">{{ trans('general-ledger/fields.to') }}</label>
                            </div>
                            <div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">
                                <label for="company" class="col-sm-4 control-label">{{ trans('general-ledger/fields.company') }}</label>
                                <div class="col-sm-4">
                                    <?php $companyFrom = !empty($filters['companyFrom']) ? $filters['companyFrom'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="companyFrom" id="companyFrom">
                                        <option value="ALL"></option>
                                        @foreach($optionCompany as $company)
                                        <option value="{{ $company->coa_code }}" {{ !empty($filters['companyFrom']) && $filters['companyFrom'] == $company->coa_code ? 'selected' : '' }}>{{ $company->coa_code }} - {{ $company->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('companyFrom'))
                                    <span class="help-block">{{ $errors->first('companyFrom') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <?php $companyFrom = !empty($filters['companyTo']) ? $filters['companyTo'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="companyTo" id="companyTo">
                                        <option value="ALL"></option>
                                        @foreach($optionCompany as $company)
                                        <option value="{{ $company->coa_code }}" {{ !empty($filters['companyTo']) && $filters['companyTo'] == $company->coa_code ? 'selected' : '' }}>{{ $company->coa_code }} - {{ $company->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('companyTo'))
                                    <span class="help-block">{{ $errors->first('companyTo') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('costCenter') ? 'has-error' : '' }}">
                                <label for="costCenter" class="col-sm-4 control-label">{{ trans('general-ledger/fields.cost-center') }}</label>
                                <div class="col-sm-4">
                                    <?php $costCenterFrom = !empty($filters['costCenterFrom']) ? $filters['costCenterFrom'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="costCenterFrom" id="costCenterFrom">
                                        <option value="ALL"></option>
                                        @foreach($optionCostCenter as $costCenter)
                                        <option value="{{ $costCenter->coa_code }}" {{ !empty($filters['costCenterFrom']) && $filters['costCenterFrom'] == $costCenter->coa_code ? 'selected' : '' }}>{{ $costCenter->coa_code }} - {{ $costCenter->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('costCenterFrom'))
                                    <span class="help-block">{{ $errors->first('costCenterFrom') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <?php $costCenterTo = !empty($filters['costCenterTo']) ? $filters['costCenterTo'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="costCenterTo" id="costCenterTo">
                                        <option value="ALL"></option>
                                        @foreach($optionCostCenter as $costCenter)
                                        <option value="{{ $costCenter->coa_code }}" {{ !empty($filters['costCenterTo']) && $filters['costCenterTo'] == $costCenter->coa_code ? 'selected' : '' }}>{{ $costCenter->coa_code }} - {{ $costCenter->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('costCenterTo'))
                                    <span class="help-block">{{ $errors->first('costCenterTo') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('account') ? 'has-error' : '' }}">
                                <label for="account" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }}</label>
                                <div class="col-sm-4">
                                    <?php $accountFrom = !empty($filters['accountFrom']) ? $filters['accountFrom'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="accountFrom" id="accountFrom">
                                        <option value="ALL"></option>
                                        @foreach($optionAccount as $account)
                                        <option value="{{ $account->coa_code }}" {{ !empty($filters['accountFrom']) && $filters['accountFrom'] == $account->coa_code ? 'selected' : '' }}>{{ $account->coa_code }} - {{ $account->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('accountFrom'))
                                    <span class="help-block">{{ $errors->first('accountFrom') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <?php $accountTo = !empty($filters['accountTo']) ? $filters['accountTo'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="accountTo" id="accountTo">
                                        <option value="ALL"></option>
                                        @foreach($optionAccount as $account)
                                        <option value="{{ $account->coa_code }}" {{ !empty($filters['accountTo']) && $filters['accountTo'] == $account->coa_code ? 'selected' : '' }}>{{ $account->coa_code }} - {{ $account->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('accountTo'))
                                    <span class="help-block">{{ $errors->first('accountTo') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('subAccount') ? 'has-error' : '' }}">
                                <label for="subAccount" class="col-sm-4 control-label">{{ trans('general-ledger/fields.sub-account') }}</label>
                                <div class="col-sm-4">
                                    <?php $subAccountFrom = !empty($filters['subAccountFrom']) ? $filters['subAccountFrom'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="subAccountFrom" id="subAccountFrom">
                                        <option value="ALL"></option>
                                        @foreach($optionSubAccount as $subAccount)
                                        <option value="{{ $subAccount->coa_code }}" {{ !empty($filters['subAccountFrom']) && $filters['subAccountFrom'] == $subAccount->coa_code ? 'selected' : '' }}>{{ $subAccount->coa_code }} - {{ $subAccount->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('subAccountFrom'))
                                    <span class="help-block">{{ $errors->first('subAccountFrom') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <?php $subAccountTo = !empty($filters['subAccountTo']) ? $filters['subAccountTo'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="subAccountTo" id="subAccountTo">
                                        <option value="ALL"></option>
                                        @foreach($optionSubAccount as $subAccount)
                                        <option value="{{ $subAccount->coa_code }}" {{ !empty($filters['subAccountTo']) && $filters['subAccountTo'] == $subAccount->coa_code ? 'selected' : '' }}>{{ $subAccount->coa_code }} - {{ $subAccount->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('subAccountTo'))
                                    <span class="help-block">{{ $errors->first('subAccountTo') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('future') ? 'has-error' : '' }}">
                                <label for="future" class="col-sm-4 control-label">{{ trans('general-ledger/fields.future') }}</label>
                                <div class="col-sm-4">
                                    <?php $futureFrom = !empty($filters['futureFrom']) ? $filters['futureFrom'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="futureFrom" id="futureFrom">
                                        <option value="ALL"></option>
                                        @foreach($optionFuture as $future)
                                        <option value="{{ $future->coa_code }}" {{ !empty($filters['futureFrom']) && $filters['futureFrom'] == $future->coa_code ? 'selected' : '' }}>{{ $future->coa_code }} - {{ $future->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('futureFrom'))
                                    <span class="help-block">{{ $errors->first('futureFrom') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <?php $futureTo = !empty($filters['futureTo']) ? $filters['futureTo'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="futureTo" id="futureTo">
                                        <option value="ALL"></option>
                                        @foreach($optionFuture as $future)
                                        <option value="{{ $future->coa_code }}" {{ !empty($filters['futureTo']) && $filters['futureTo'] == $future->coa_code ? 'selected' : '' }}>{{ $future->coa_code }} - {{ $future->description }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('futureTo'))
                                    <span class="help-block">{{ $errors->first('futureTo') }}</span>
                                    @endif
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
                                <th>{{ trans('general-ledger/fields.coa-combination') }}</th>
                                <th>{{ trans('shared/common.deskripsi') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->combination_code }}</td>
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
                                    <a href="{{ URL($url . '/edit/' . $model->account_combination_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @can('access', [$resource, 'delete'])
                                    <!-- <a data-id="{{ $model->account_combination_id }}" data-label="{{ $model->combination_code }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
                                        <i class="fa fa-remove"></i>
                                    </a> -->
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
        $('#companyFrom').select2();
        $('#companyTo').select2();
        $('#costCenterFrom').select2();
        $('#costCenterTo').select2();
        $('#accountFrom').select2();
        $('#accountTo').select2();
        $('#subAccountFrom').select2();
        $('#subAccountTo').select2();
        $('#futureFrom').select2();
        $('#futureTo').select2();
    });
</script>
@endsection
