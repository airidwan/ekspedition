@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receivables'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.receivables') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <form  role="form" id="registerForm" class="form-horizontal" method="post" action="">
                    {{ csrf_field() }}
                    <div id="horizontal-form">
                        <div class="col-sm-8 portlets">
                            <div class="col-sm-12 portlets" style="padding-bottom:20px;">
                                <div class="form-group">
                                    <div class="col-sm-8">
                                        <label class="radio-inline iradio">
                                            <input type="radio" name="statusPernikahan" id="statusPernikahan" value="Menikah" checked> {{ trans('general-ledger/fields.invoice-receivable') }}
                                        </label>
                                        <label class="radio-inline iradio">
                                            <input type="radio" name="statusPernikahan" id="statusPernikahan" value="Belum Menikah"> {{ trans('general-ledger/fields.other-receivable') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 portlets">
                                <div class="form-group">
                                    <label for="customer" class="col-sm-5 control-label">{{ trans('general-ledger/fields.customer-name') }}</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control" id="customer" name="customer" value="{{ !empty($filters['customer']) ? $filters['customer'] : '' }}">
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('invoiceFrom') ? 'has-error' : '' }}">
                                    <label for="invoiceFrom" class="col-sm-5 control-label">{{ trans('general-ledger/fields.invoice-from') }}</label>
                                    <div class="col-sm-7">
                                        <div class="input-group">
                                            <input type="text" id="invoiceFrom" name="invoiceFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('invoiceTo') ? 'has-error' : '' }}">
                                    <label for="invoiceTo" class="col-sm-5 control-label">{{ trans('general-ledger/fields.invoice-to') }}</label>
                                    <div class="col-sm-7">
                                        <div class="input-group">
                                            <input type="text" id="invoiceTo" name="invoiceTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 portlets">
                                <div class="form-group">
                                    <label for="invoiceNumber" class="col-sm-5 control-label">{{ trans('general-ledger/fields.invoice-number') }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-control" name="invoiceNumber" id="invoiceNumber">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="invoiceNumber" class="col-sm-5 control-label">{{ trans('general-ledger/fields.status') }}</label>
                                    <div class="col-sm-7">
                                        <select class="form-control" name="invoiceNumber" id="invoiceNumber">
                                            <option value="">Paid</option>
                                            <option value="">Not Paid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <fieldset class="scheduler-border" style="padding-bottom:0px;"> 
                                <div style="padding-top:10px;" class="form-group {{ $errors->has('totalReceivables') ? 'has-error' : '' }}">
                                    <label for="totalReceivables" class="col-sm-6 control-label">{{ trans('general-ledger/fields.total-receivables') }}</label>
                                    <div class="col-sm-6">
                                        <input disabled type="text" class="form-control currency text-right" id="totalReceivables" name="totalReceivables" value="100.000.000">
                                        @if($errors->has('totalReceivables'))
                                        <span class="help-block">{{ $errors->first('totalReceivables') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('totalPayment') ? 'has-error' : '' }}">
                                    <label for="totalPayment" class="col-sm-6 control-label">{{ trans('general-ledger/fields.total-payment') }}</label>
                                    <div class="col-sm-6">
                                        <input disabled type="text" class="form-control currency text-right" id="totalPayment" name="totalPayment" value="100.000">
                                        @if($errors->has('totalPayment'))
                                        <span class="help-block">{{ $errors->first('totalPayment') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('totalResidual') ? 'has-error' : '' }}">
                                    <label for="totalResidual" class="col-sm-6 control-label">{{ trans('general-ledger/fields.total-residual') }}</label>
                                    <div class="col-sm-6">
                                        <input disabled type="text" class="form-control currency text-right" id="totalResidual" name="totalResidual" value="100.000">
                                        @if($errors->has('totalResidual'))
                                        <span class="help-block">{{ $errors->first('totalResidual') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('totalReduction') ? 'has-error' : '' }}">
                                    <label for="totalReduction" class="col-sm-6 control-label">{{ trans('general-ledger/fields.total-reduction') }}</label>
                                    <div class="col-sm-6">
                                        <input disabled type="text" class="form-control currency text-right" id="totalReduction" name="totalReduction" value="100.000">
                                        @if($errors->has('totalReduction'))
                                        <span class="help-block">{{ $errors->first('totalReduction') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('totalAddition') ? 'has-error' : '' }}">
                                    <label for="totalAddition" class="col-sm-6 control-label">{{ trans('general-ledger/fields.total-addition') }}</label>
                                    <div class="col-sm-6">
                                        <input disabled type="text" class="form-control currency text-right" id="totalAddition" name="totalAddition" value="100.000">
                                        @if($errors->has('totalAddition'))
                                        <span class="help-block">{{ $errors->first('totalAddition') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="data-table-toolbar">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="toolbar-btn-action">
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                    <a href="{{ URL('operasional/transaction/receivables/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="check" class="icheck"></th>
                                    <th>{{ trans('shared/common.kode') }}</th>
                                    <th>{{ trans('general-ledger/fields.customer-name') }}</th>
                                    <th>{{ trans('general-ledger/fields.invoice-number') }}</th>
                                    <th>{{ trans('general-ledger/fields.invoice-date') }}</th>
                                    <th>{{ trans('general-ledger/fields.total-receivables') }}</th>
                                    <th>{{ trans('general-ledger/fields.total-payment') }}</th>
                                    <th>{{ trans('general-ledger/fields.reduction') }}</th>
                                    <th>{{ trans('general-ledger/fields.addition') }}</th>
                                    <th>{{ trans('general-ledger/fields.status') }}</th>
                                    <th>{{ trans('general-ledger/fields.receivables-residual') }}</th>
                                    <th>{{ trans('general-ledger/fields.due-date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="check" class="icheck">
                                    </td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <input type="text" id="dueDate" name="invoiceFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="check" class="icheck">
                                    </td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <input type="text" id="dueDate" name="invoiceFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="check" class="icheck">
                                    </td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <input type="text" id="dueDate" name="invoiceFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="data-table-toolbar">
                            <!-- pagination -->
                        </div>
                        <div class="data-table-toolbar">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="toolbar-btn-action">
                                        <a href="#" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> {{ trans('general-ledger/fields.create-invoice') }}</a>
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('general-ledger/fields.save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
