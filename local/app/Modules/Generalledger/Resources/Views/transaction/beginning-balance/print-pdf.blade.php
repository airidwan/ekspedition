@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['segmentName']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.segment-name') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['segmentName'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['description']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['description'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['identifier']))
                    <?php 
                        $identifier = '';
                        if ($filters['identifier'] == '1') {
                            $identifier = 'Asset';
                        } else if ($filters['identifier'] == '2'){
                            $identifier = 'Liability';
                        } else if ($filters['identifier'] == '3'){
                            $identifier = 'Equitas';
                        } else if ($filters['identifier'] == '4'){
                            $identifier = 'Revenue';
                        } else if ($filters['identifier'] == '5'){
                            $identifier = 'Ekspense';
                        }
                    ?>
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.identifier') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $identifier }}</td>
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
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="20%">{{ trans('general-ledger/fields.segment-name') }}</th>
            <th width="20%">{{ trans('general-ledger/fields.coa-code') }}</th>
            <th width="40%">{{ trans('shared/common.description') }}</th>
            <th width="10%">{{ trans('general-ledger/fields.identifier') }}</th>
            <th width="5%">{{ trans('shared/common.active') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($models as $model)
         <?php 
            $identifier = '';
            if ($model->identifier == '1') {
                $identifier = 'Asset';
            } else if ($model->identifier == '2'){
                $identifier = 'Liability';
            } else if ($model->identifier == '3'){
                $identifier = 'Equitas';
            } else if ($model->identifier == '4'){
                $identifier = 'Revenue';
            } else if ($model->identifier == '5'){
                $identifier = 'Ekspense';
            }
        ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="20%" >{{ $model->segment_name }}</td>
            <td width="20%" >{{ $model->coa_code }}</td>
            <td width="40%" >{{ $model->description }}</td>
            <td width="10%" >{{ $identifier }}</td>
            <td width="5%" align="center">{{ $model->active == 'Y' ? 'v' : 'x' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
