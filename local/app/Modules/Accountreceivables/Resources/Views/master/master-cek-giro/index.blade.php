@extends('layouts.master')

@section('title', trans('accountreceivables/menu.master-cek-giro'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.master-cek-giro') }}</h2>
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
                                <?php $type = !empty($filters['type']) ? $filters['type'] : ''
                                ?>
                                <label for="type" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.type') }}</label>
                                <div class="col-sm-8">
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="type" id="type" value="ALL" {{ $type == 'ALL' ? 'checked' : ''  }} > ALL
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="type" id="type" value="C" {{ $type == 'C' ? 'checked' : ''  }} > {{ trans('accountreceivables/fields.cek') }}
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="type" id="type" value="G" {{ $type == 'G' ? 'checked' : ''  }}> {{ trans('accountreceivables/fields.giro') }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('number') ? 'has-error' : '' }}">
                                <label for="number" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.number') }}</label>
                                <div class="col-sm-4">
                                    <?php $number = !empty($filters['number']) ? $filters['number'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="number" id="number">
                                        <option value="ALL">ALL</option>
                                        @foreach($optionNumber as $data)
                                        <option value="{{ $data->cg_number }}" {{ !empty($filters['number']) && $filters['number'] == $data->cg_number ? 'selected' : '' }}>{{ $data->cg_number }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('number'))
                                    <span class="help-block">{{ $errors->first('number') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('customer') ? 'has-error' : '' }}">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                <div class="col-sm-4">
                                    <?php $customer = !empty($filters['customer']) ? $filters['customer'] : '' ;
                                    ?>
                                    <select class="form-control select2" name="customer" id="customer">
                                        <option value="ALL">ALL</option>
                                        @foreach($optionCustomer as $data)
                                        <option value="{{ $data->customer_id }}" {{ !empty($filters['customer']) && $filters['customer'] == $data->customer_id ? 'selected' : '' }}>{{ $data->customer_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('customer'))
                                    <span class="help-block">{{ $errors->first('customer') }}</span>
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
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="username" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.start-date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                          <input data-mask="99-99-9999" placeholder="dd-mm-yyyy" type="text" id="startDate" name="startDate" class="form-control datepicker-input" value="{{ !empty($filters['startDate']) ? $filters['startDate'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="username" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.due-date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input data-mask="99-99-9999" placeholder="dd-mm-yyyy" type="text" id="dueDate" name="dueDate" class="form-control datepicker-input" value="{{ !empty($filters['dueDate']) ? $filters['dueDate'] : '' }}">
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
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('accountreceivables/fields.type') }}</th>
                                <th>{{ trans('accountreceivables/fields.number') }}</th>
                                <th>{{ trans('accountreceivables/fields.customer') }}</th>
                                <th>{{ trans('accountreceivables/fields.bank-name') }}</th>
                                <th>{{ trans('accountreceivables/fields.start-date') }}</th>
                                <th>{{ trans('accountreceivables/fields.due-date') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <tr>
                                <?php
                                     $startDate = !empty($model->cg_date) ? new \DateTime($model->cg_date) : null;
                                     $dueDate = !empty($model->cg_due_date) ? new \DateTime($model->cg_due_date) : null;
                                 ?>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->cg_type == 'C' ? 'Cek' : 'Giro' }}</td>
                                <td>{{ $model->cg_number }}</td>
                                <td>{{ $model->customer_name }}</td>
                                <td>{{ $model->bank_name }}</td>
                                <td>{{ !empty($startDate) ? $startDate->format('d-M-Y') : '' }}</td>
                                <td>{{ !empty($dueDate) ? $dueDate->format('d-M-Y') : '' }}</td>
                                <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->cek_giro_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @can('access', [$resource, 'delete'])
                                    <!-- <a data-id="{{ $model->cek_giro_id }}" data-label="{{ $model->cek_giro_id }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
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

@section('modal')
@parent
<div class="md-modal md-3d-flip-horizontal" id="modal-delete">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.delete') }}</strong> {{ trans('accountreceivables/menu.master-cek-giro') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="delete-text">Are you sure want to delete ?</h4>
                    <form role="form" method="post" action="{{ URL($url . '/delete') }}" class="text-right">
                        {{ csrf_field() }}
                        <input type="hidden" id="delete-id" name="id" >
                        <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                        <button type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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
        $('#number').select2();
        $('#customer').select2();
        $('.delete-action').on('click', function() {
            $("#delete-id").val($(this).data('id'));
            $("#delete-text").html('{{ trans('shared/common.delete-confirmation', ['variable' => trans('accountreceivables/menu.master-cek-giro')]) }} ' + $(this).data('label') + '?');
        });
    });
</script>
@endsection
