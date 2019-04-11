@extends('layouts.master')

@section('title', trans('general-ledger/menu.beginning-balance'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.beginning-balance') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->beginning_balance_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="balanceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <?php $balanceDate = new \DateTime($model->balance_date); ?>
                                        <input type="text" id="balanceDate" name="balanceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $balanceDate->format('d-m-Y') }}" >
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $modelBank = $model->bank;
                                $bankName  = !empty($modelBank) ? $modelBank->bank_name : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('bankName') ? 'has-error' : '' }}">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.bank') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="bankId" name="bankId" value="{{ count($errors) > 0 ? old('bankId') : $model->bank_id }}">
                                    <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $bankName }}" readonly>
                                    <span class="btn input-group-addon" id="modalBank" data-toggle="modal" data-target="#modal-bank"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('bankName'))
                                    <span class="help-block">{{ $errors->first('bankName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('beginningBalance') ? 'has-error' : '' }}">
                                <label for="beginningBalance" class="col-sm-4 control-label">{{ trans('general-ledger/fields.beginning-balance') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="beginningBalance" name="beginningBalance" value="{{ count($errors) > 0 ? str_replace(',', '', old('beginningBalance')) : $model->beginning_balance }}">
                                    @if($errors->has('beginningBalance'))
                                    <span class="help-block">{{ $errors->first('beginningBalance') }}</span>
                                    @endif
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
<div id="modal-bank" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Bank List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-bank" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.bank-name') }}</th>
                            <th>{{ trans('general-ledger/fields.account-name') }}</th>
                            <th>{{ trans('general-ledger/fields.account-number') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionBank as $bank)
                        <tr style="cursor: pointer;" data-bank="{{ json_encode($bank) }}">
                            <td>{{ $bank->bank_name }}</td>
                            <td>{{ $bank->account_name }}</td>
                            <td>{{ $bank->account_number }}</td>
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
    $(document).on('ready', function(){
        $("#datatables-bank").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-bank tbody').on('click', 'tr', function () {
            var bank = $(this).data('bank');

            $('#bankId').val(bank.bank_id);
            $('#bankName').val(bank.bank_name);
            
            $('#modal-bank').modal('hide');
        });
    });
</script>
@endsection
