@extends('layouts.master')

@section('title', trans('operational/menu.return-manifest'))

<?php 
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader; 
    use App\Modules\Operational\Model\Transaction\ManifestHeader; 
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.return-manifest') }}</h2>
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
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="returnManifestNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.return-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="returnManifestNumber" name="returnManifestNumber" value="{{ !empty($filters['returnManifestNumber']) ? $filters['returnManifestNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="manifestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" value="{{ !empty($filters['manifestNumber']) ? $filters['manifestNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label"></label>
                                <div class="col-sm-8">
                                    <?php $jenis = !empty($filters['jenis']) ? $filters['jenis'] : 'headers' ?>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio1" value="headers" {{ $jenis == 'headers' ? 'checked' : '' }}> Headers
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio2" value="lines" {{ $jenis == 'lines' ? 'checked' : '' }}> Lines
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="note" name="note" value="{{ !empty($filters['note']) ? $filters['note'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
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
                    @if (empty($filters['jenis']) || $filters['jenis'] == 'headers')
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.return-number') }}</th>
                                <th>{{ trans('inventory/fields.return-date') }}</th>
                                <th>{{ trans('shared/common.time') }}</th>
                                <th>{{ trans('operational/fields.manifest-number') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <?php
                                $returnDate = !empty($model->created_date) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->manifest_return_number }}</td>
                                <td>{{ !empty($returnDate) ? $returnDate->format('d-M-Y') : '' }}</td>
                                <td>{{ !empty($returnDate) ? $returnDate->format('h:i') : '' }}</td>
                                <td>{{ $model->manifest_number }}</td>
                                <td>{{ $model->description }}</td>
                                <td class="text-center">
                                @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->manifest_return_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                    <i class="fa fa-pencil"></i>
                                    </a>
                                @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.return-number') }}</th>
                                <th>{{ trans('inventory/fields.return-date') }}</th>
                                <th>{{ trans('operational/fields.manifest-number') }}</th>
                                <th>{{ trans('operational/fields.resi-number') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('operational/fields.total-coly') }}</th>
                                <th>{{ trans('operational/fields.coly-sent') }}</th>
                                <th>{{ trans('operational/fields.coly-return') }}</th>
                                <th>{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <?php
                                $returnDate = !empty($model->created_date) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->created_date) : null;
                                $resi = TransactionResiHeader::find($model->resi_header_id);
                                $model->total_coly = $resi->totalColy();
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->manifest_return_number }}</td>
                                <td>{{ !empty($returnDate) ? $returnDate->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->manifest_number }}</td>
                                <td>{{ $model->resi_number }}</td>
                                <td>{{ $model->note }}</td>
                                <td class="text-center">{{ $model->total_coly}}</td>
                                <td class="text-center">{{ $model->coly_sent }}</td>
                                <td class="text-center">{{ $model->coly_return }}</td>
                                <td class="text-center">
                                @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->manifest_return_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                    <i class="fa fa-pencil"></i>
                                    </a>
                                @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
