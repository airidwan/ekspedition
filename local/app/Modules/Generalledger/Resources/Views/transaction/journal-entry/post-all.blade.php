@extends('layouts.master')

@section('title', trans('general-ledger/menu.journal-entry'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/fields.post-all') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/post-all') }}">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="journalNumber" class="col-sm-4 control-label">{{ trans('general-ledger/fields.journal-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="journalNumber" name="journalNumber" value="{{ !empty($filters['journalNumber']) ? $filters['journalNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="category" id="category">
                                        <option value="">ALL</option>
                                        @foreach($optionCategory as $category)
                                        <option value="{{ $category }}" {{ !empty($filters['category']) && $filters['category'] == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="period" class="col-sm-4 control-label">{{ trans('shared/common.period') }}</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="periodMonth" id="periodMonth">
                                        <option value="">ALL</option>
                                        @foreach($optionPeriodMonth as $value => $label)
                                        <option value="{{ $value }}" {{ !empty($filters['periodMonth']) && $filters['periodMonth'] == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <select class="form-control" name="periodYear" id="periodYear">
                                        <option value="">ALL</option>
                                        @foreach($optionPeriodYear as $option)
                                        <option value="{{ $option }}" {{ !empty($filters['periodYear']) && $filters['periodYear'] == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="period" class="col-sm-4 control-label">{{ trans('shared/common.limit') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="limit" id="limit">
                                        <option value="">ALL</option>
                                        @foreach($optionLimit as $limit)
                                        <option value="{{ $limit }}" {{ !empty($filters['limit']) && $filters['limit'] == $limit ? 'selected' : '' }}>
                                            {{ $limit }}
                                        </option>
                                        @endforeach
                                    </select>
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
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save-post-all') }}">
                    {{ csrf_field() }}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="">{{ trans('shared/common.num') }}</th>
                                    <th width="">{{ trans('general-ledger/fields.journal-number') }} <hr/> {{ trans('shared/common.category') }}</th>
                                    <th width="">{{ trans('shared/common.date') }} <hr/> {{ trans('shared/common.period') }}</th>
                                    <th width="">{{ trans('general-ledger/fields.coa-combination') }} </th>
                                    <th width="">{{ trans('general-ledger/fields.coa-description') }}</th>
                                    <th width="">{{ trans('general-ledger/fields.debet') }}</th>
                                    <th width="">{{ trans('general-ledger/fields.credit') }}</th>
                                    <th width="">{{ trans('shared/common.description') }}</th>
                                    <th><input name="all-line" id="all-line" type="checkbox" ></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;?>
                                @foreach($models as $model)
                                <?php
                                $createdDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                                $period      = !empty($model->period) ? new \DateTime($model->period) : null;
                                $header      = JournalHeader::find($model->journal_header_id);
                                $length      = $header->lines->count();
                                ?>
                                <tr>
                                    <td class="text-center" rowspan="{{ $length }}">{{ $no++ }}</td>
                                    <td rowspan="{{ $length }}">{{ $model->journal_number }} <hr/> {{ $model->category }}</td>
                                    <td class="text-center" rowspan="{{ $length }}">
                                        {{ $createdDate !== null ? $createdDate->format('d-m-Y') : '' }} <hr/> 
                                        {{ $period !== null ? $period->format('M-Y') : '' }}
                                    </td>
                                    @foreach($header->lines as $index => $line)
                                        @if($index != 0)
                                            <tr> 
                                        @endif
                                                <td>{{ $line->accountCombination->getCombinationCode() }}</td>
                                                <td>{{ $line->accountCombination->getCombinationDescription() }}</td>
                                                <td align="right">{{ number_format($line->debet) }}</td>
                                                <td align="right">{{ number_format($line->credit) }}</td>
                                                <td >{{ $line->description }}</td>
                                        @if($index == 0)
                                            <td class="text-center" rowspan="{{ $length }}">
                                                <input name="journalHeaderId[]" value="{{ $model->journal_header_id }}" type="checkbox" class="rows-check">
                                            </td>
                                        @endif
                                            </tr>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 data-table-toolbar text-right">
                        <div class="form-group">
                            @can('access', [$resource, 'postAll'])
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-upload"></i> {{ trans('general-ledger/fields.post-all') }}</button>
                            @endcan
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function() {
        $('#all-line').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });
    });
</script>
@endsection
