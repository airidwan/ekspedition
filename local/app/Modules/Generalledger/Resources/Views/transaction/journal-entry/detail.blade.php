@extends('layouts.master')

@section('title', trans('general-ledger/menu.journal-entry'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.journal-entry') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->journal_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-8 portlets">
                                    <div class="form-group">
                                        <label for="journalNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.journal-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="journalNumber" name="journalNumber" value="{{ $model->journal_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php $createdDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group">
                                        <label for="createdDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="createdDate" name="createdDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $createdDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php $category = $model->category ?>
                                    <div class="form-group">
                                        <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="category" id="category" disabled>
                                                <option value="">Select Category</option>
                                                @foreach($optionCategory as $option)
                                                    <option value="{{ $option }}" {{ $option == $category ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="period" class="col-sm-4 control-label">{{ trans('shared/common.period') }}</label>
                                        <?php
                                        if (count($errors) > 0) {
                                            $periodMonth = old('periodMonth');
                                            $periodYear  = old('periodYear');
                                        } else {
                                            $period      = new \DateTime($model->journal_date);
                                            $periodMonth = intval($period->format('m'));
                                            $periodYear  = intval($period->format('Y'));
                                        }
                                        ?>
                                        <div class="col-sm-4">
                                            <select class="form-control" name="periodMonth" id="periodMonth" disabled>
                                                @foreach($optionPeriodMonth as $value => $label)
                                                    <option value="{{ $value }}" {{ $value == $periodMonth ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-control" name="periodYear" id="periodYear" disabled>
                                                @foreach($optionPeriodYear as $option)
                                                    <option value="{{ $option }}" {{ $option == $periodYear ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('general-ledger/fields.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea id="description" name="description" class="form-control" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('general-ledger/fields.coa-combination') }}</th>
                                                    <th width="150px">{{ trans('general-ledger/fields.debet') }}</th>
                                                    <th width="150px">{{ trans('general-ledger/fields.credit') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines()->orderBy('debet', 'desc')->orderBy('credit', 'desc')->get() as $line)
                                                <tr>
                                                    <td >
                                                        {{ !empty($line->accountCombination) ? $line->accountCombination->getCombinationCode() : '' }}<hr/>
                                                        {{ !empty($line->accountCombination) ? $line->accountCombination->getCombinationDescription() : '' }}
                                                    </td>
                                                    <td class="text-right"> {{ number_format($line->debet) }} </td>
                                                    <td class="text-right"> {{ number_format($line->credit) }} </td>
                                                    <td> {{ $line->description }} </td>
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
