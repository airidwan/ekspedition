@extends('layouts.master')

@section('title', trans('operational/menu.resi-to-receipt'))

<?php $now = new \DateTime(); ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.resi-to-receipt') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ URL($urlPrint) }}" target="_blank">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="periode" class="col-sm-4 control-label">{{ trans('shared/common.period') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="periode">
                                        @foreach($optionPeriode as $periode)
                                            <option value="{{ $periode }}" >{{ $periode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">{{ trans('shared/common.date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $now->format('1-m-Y') }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $now->format('d-m-Y') }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pickupNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pickupNumber" name="pickupNumber" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="manifestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="doNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="doNumber" name="doNumber" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lgeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.customer-taking-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="lgeNumber" name="lgeNumber" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lgeTransactNumber" class="col-sm-4 control-label">{{ trans('operational/fields.customer-taking-transact-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="lgeTransactNumber" name="lgeTransactNumber" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('operational/fields.receipt-or-return-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print') }}</button>
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
