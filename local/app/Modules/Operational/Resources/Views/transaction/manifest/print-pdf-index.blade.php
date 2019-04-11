@extends('layouts.print')
<?php use App\Modules\Operational\Model\Transaction\ManifestHeader; ?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['manifestNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.manifest-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['manifestNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['driver']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.driver') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['driver'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['driverAssistant']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.driver-assistant') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['driverAssistant'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['nopolTruck']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.nopol-truck') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['nopolTruck'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['truckCategory']))
                    <?php $truck = \DB::table('adm.mst_lookup_values')->select('meaning')->where('lookup_code', '=', $filters['truckCategory'])->first(); ?>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.truck-category') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $truck->meaning }}</td>
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
                @if (!empty($filters['status']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.status') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['status'] }}</td>
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
            <th width="13%">{{ trans('operational/fields.manifest-number') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.route') }}</th>
            <th width="12%">{{ trans('operational/fields.kota-asal') }}</th>
            <th width="12%">{{ trans('operational/fields.police-number') }}</th>
            <th width="15%">{{ trans('operational/fields.driver') }}</th>
            <th width="23%" rowspan="2">{{ trans('shared/common.description') }}</th>
            <th width="10%" rowspan="2">{{ trans('shared/common.status') }}</th>
        </tr>
        <tr>
            <th >{{ trans('shared/common.date') }}</th>
            <th >{{ trans('operational/fields.kota-tujuan') }}</th>
            <th >{{ trans('operational/fields.truck-owner') }}</th>
            <th >{{ trans('operational/fields.assistant') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; ?>
        @foreach($models as $model)
        <?php
            $model      = ManifestHeader::find($model->manifest_header_id);
            $date       = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
        ?>
        <tr>
            <td width="5%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="13%" >{{ $model->manifest_number }}</td>
            <td width="10%" rowspan="2">{{ $model->route->route_code }}</td>
            <td width="12%" >{{ $model->route->cityStart->city_name }}</td>
            <td width="12%" >{{ $model->truck->police_number }}</td>
            <td width="15%" >{{ $model->driver->driver_name }}</td>
            <td width="23%" rowspan="2">{{ $model->description }}</td>
            <td width="10%" rowspan="2">{{ $model->status }}</td>
        </tr>
        <tr>
            <td>{{ $date->format('d-m-Y') }}</td>
            <td>{{ $model->route->cityEnd->city_name }}</td>
            <td>{{ $model->truck->owner_name }}</td>
            <td>{{ !empty($model->driverAssistant) ? $model->driverAssistant->driver_name : '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
