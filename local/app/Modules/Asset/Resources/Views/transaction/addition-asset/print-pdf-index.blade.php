<?php 
use App\Modules\Asset\Model\Master\AssetCategory;
use App\Modules\Asset\Model\Transaction\AdditionAsset;

?>

@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['assetNumber']))
                    <tr>
                        <td width="18%">{{ trans('asset/fields.asset-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['assetNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['itemCode']))
                    <tr>
                        <td width="18%">{{ trans('inventory/fields.item-code') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['itemCode'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['itemDescription']))
                    <tr>
                        <td width="18%">{{ trans('inventory/fields.item-description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['itemDescription'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['policeNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.police-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['policeNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['employee']))
                    <tr>
                        <td width="18%">{{ trans('asset/fields.employee') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['employee'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['category']))
                <?php 
                    $modelCategory = AssetCategory::find($filters['category']);
                ?>
                    <tr>
                        <td width="18%">{{ trans('shared/common.category') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ !empty($modelCategory) ? $modelCategory->category_name : '' }}</td>
                    </tr>
                @endif
                @if (!empty($filters['type']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.type') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['type'] }}</td>
                    </tr>
                @endif
                @if(!empty($filters['status']))
                <?php 
                    $modelStatus = \DB::table('ast.asset_status')->where('asset_status_id', '=', $filters['status'])->first();
                ?>
                    <tr>
                        <td width="18%">{{ trans('shared/common.status') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ !empty($modelStatus) ? $modelStatus->status : '' }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateFrom']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateTo']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateTo'] }}</td>
                    </tr>
                @endif
            </table>
            <br/>
        </td>
        <td width="25%" cellpadding="0" cellspacing="0">
            <table>
                <?php $date = new \DateTime(); ?>
                <tr>
                    <td width="25%">{{ trans('shared/common.date') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ $date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.user') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Auth::user()->full_name }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.branch') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Session::get('currentBranch')->branch_name }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="15%" rowspan="2">{{ trans('asset/fields.asset-number') }}</th>
            <th width="15%">{{ trans('shared/common.type') }}</th>
            <th width="20%">{{ trans('inventory/fields.item-code') }}</th>
            <th width="15%" rowspan="2">{{ trans('operational/fields.police-number') }}</th>
            <th width="15%" rowspan="2">{{ trans('asset/fields.employee') }}</th>
            <th width="15%">{{ trans('shared/common.date') }}</th>
        </tr>
        <tr>
            <th>{{ trans('shared/common.category') }}</th>
            <th>{{ trans('inventory/fields.item-description') }}</th>
            <th>{{ trans('shared/common.status') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($models as $model)
        <?php
             $assetDate = !empty($model->asset_date) ? new \DateTime($model->asset_date) : null;
         ?>
        <tr>
            <td width="5%"  align="center" rowspan="2">{{ $no++ }}</td>
            <td width="15%" rowspan="2">{{ $model->asset_number }}</td>
            <td width="15%" >{{ $model->type }}</td>
            <td width="20%" >{{ $model->item_code }}</td>
            <td width="15%" rowspan="2">{{ $model->police_number }}</td>
            <td width="15%" rowspan="2">{{ $model->employee_name }}</td>
            <td width="15%" >{{ !empty($assetDate) ? $assetDate->format('d-M-Y') : '' }}</td>
        </tr>
        <tr>
            <td>{{ $model->category_name }}</td>
            <td>{{ $model->item_description }}</td>
            <td>{{ $model->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
