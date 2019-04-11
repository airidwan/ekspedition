<?php
use App\Modules\Generalledger\Model\Master\MasterBank;
?>

@extends('layouts.master')

@section('title', trans('general-ledger/menu.master-bank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.master-bank') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->bank_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#bankTab" data-toggle="tab">{{ trans('general-ledger/menu.master-bank') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#activationTab" data-toggle="tab">{{ trans('shared/common.activation') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="bankTab">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('bankName') ? 'has-error' : '' }}">
                                        <label for="bankName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.bank-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $model->bank_name }}">
                                            @if($errors->has('bankName'))
                                            <span class="help-block">{{ $errors->first('bankName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('accountName') ? 'has-error' : '' }}">
                                        <label for="accountName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="accountName" name="accountName" value="{{ count($errors) > 0 ? old('accountName') : $model->account_name }}">
                                            @if($errors->has('accountName'))
                                            <span class="help-block">{{ $errors->first('accountName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('accountNumber') ? 'has-error' : '' }}">
                                        <label for="accountNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="accountNumber" name="accountNumber" value="{{ count($errors) > 0 ? old('accountNumber') : $model->account_number }}">
                                            @if($errors->has('accountNumber'))
                                            <span class="help-block">{{ $errors->first('accountNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('npwp') ? 'has-error' : '' }}">
                                        <label for="npwp" class="col-sm-4 control-label">{{ trans('general-ledger/fields.npwp') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="npwp" name="npwp" value="{{ count($errors) > 0 ? old('npwp') : $model->npwp }}">
                                            @if($errors->has('npwp'))
                                            <span class="help-block">{{ $errors->first('npwp') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php $type = count($errors) > 0 ? old('type') : $model->type; ?>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select name="type" id="type" class="form-control">
                                                <option value="">Select Type</option>
                                                @foreach($optionsType as $option)
                                                <option value="{{ $option }}" {{ $option == $type ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php 
                                    $coaBank     = $model->coaBank; 
                                    $coaBankId   = !empty($coaBank) ? $coaBank->coa_id : '' ; 
                                    $coaBankCode = !empty($coaBank) ? $coaBank->coa_code : '' ; 
                                    $coaBankDesc = !empty($coaBank) ? $coaBank->description : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('coaBankId') ? 'has-error' : '' }}">
                                        <label for="coaBankId" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa-bank') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" class="form-control" id="coaBankId" name="coaBankId" value="{{ count($errors) > 0 ? old('coaBankId') : $coaBankId }}">
                                                <input type="text" class="form-control" id="coaBankCode" name="coaBankCode" value="{{ count($errors) > 0 ? old('coaBankCode') : $coaBankCode }}" readonly>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-bank"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('coaBankId'))
                                            <span class="help-block">{{ $errors->first('coaBankId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('coaBankDesc') ? 'has-error' : '' }}">
                                        <label for="coaBankDesc" class="col-sm-4 control-label">{{ trans('shared/common.description') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="coaBankDesc" name="coaBankDesc" value="{{ count($errors) > 0 ? old('coaBankDesc') : $coaBankDesc }}" readonly> 
                                            @if($errors->has('coaBankDesc'))
                                            <span class="help-block">{{ $errors->first('coaBankDesc') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="address" name="address" rows="4">{{ count($errors) > 0 ? old('address') : $model->bank_address }}</textarea>
                                            @if($errors->has('address'))
                                            <span class="help-block">{{ $errors->first('address') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} </label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="4">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                                <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="activationTab">
                                <div class="table-responsive">
                                    <div class="col-sm-12 portlets">
                                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('shared/common.branch') }}</th>
                                                    <th><input name="all-branch" id="all-branch" type="checkbox" ></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $detailBranchId = [];
                                                if (count($errors) > 0) {
                                                    $detailBranchId = old('branchDetail',[]);
                                                }else{
                                                    $branchDetail   = $model->bankBranch()->get();
                                                    foreach ($branchDetail as $dtlBranch) {
                                                        $detailBranchId[] = $dtlBranch->branch_id;
                                                    }
                                                }
                                                ?>
                                                @foreach($optionsBranch as $branch)
                                                <tr>
                                                    <td>{{ $branch->branch_name }} </td>
                                                    <td class="text-center">
                                                        <input name="branchDetail[]" value="{{ $branch->branch_id }}" type="checkbox" class="rows-check" {{ in_array($branch->branch_id, $detailBranchId) ? 'checked' : '' }}  >
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-lov-clearing" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/menu.master-coa') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-clearing" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionCoa as $clearing)
                        <tr style="cursor: pointer;" data-clearing="{{ json_encode($clearing) }}">
                            <td>{{ $clearing->coa_code }}</td>
                            <td>{{ $clearing->description }}</td>
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
<div id="modal-lov-bank" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/menu.master-coa') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-bank" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionCoa as $bank)
                        <tr style="cursor: pointer;" data-bank="{{ json_encode($bank) }}">
                            <td>{{ $bank->coa_code }}</td>
                            <td>{{ $bank->description }}</td>
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
@parent()
<script type="text/javascript">
    var cashIn  = '{{ MasterBank::CASH_IN }}';
    var cashOut = '{{ MasterBank::CASH_OUT }}';
    var bank    = '{{ MasterBank::BANK }}';

    $(document).on('ready', function(){
        $('#all-branch').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });
        
        $("#datatables-lov-clearing").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-clearing tbody').on('click', 'tr', function () {
            var clearing = $(this).data('clearing');

            $('#coaClearingId').val(clearing.coa_id);
            $('#coaClearingCode').val(clearing.coa_code);
            $('#coaClearingDesc').val(clearing.description);

            $('#modal-lov-clearing').modal("hide");
        });

        $("#datatables-lov-bank").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-bank tbody').on('click', 'tr', function () {
            var bank = $(this).data('bank');

            $('#coaBankId').val(bank.coa_id);
            $('#coaBankCode').val(bank.coa_code);
            $('#coaBankDesc').val(bank.description);

            $('#modal-lov-bank').modal("hide");
        });
    });
</script>
@endsection