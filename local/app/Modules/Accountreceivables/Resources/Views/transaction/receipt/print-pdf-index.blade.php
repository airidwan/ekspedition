@extends('layouts.print')

<?php
    use App\Modules\Accountreceivables\Model\Transaction\Receipt;
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['receiptNumber']))
                    <tr>
                        <td width="18%">{{ trans('accountreceivables/fields.receipt-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['receiptNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['invoiceNumber']))
                    <tr>
                        <td width="18%">{{ trans('accountreceivables/fields.invoice-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['invoiceNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['resiNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.resi-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['resiNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['createdBy']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.created-by') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['createdBy'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['type']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.type') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['type'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['receiptMethod']))
                    <tr>
                        <td width="18%">{{ trans('accountreceivables/fields.receipt-method') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['receiptMethod'] }}</td>
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
            <th width="3%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="11%">{{ trans('accountreceivables/fields.receipt-number') }}</th>
            <th width="7%">{{ trans('shared/common.type') }}</th>
            <th width="11%">{{ trans('accountreceivables/fields.invoice-number') }}</th>
            <th width="11%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="18%">{{ trans('operational/fields.customer') }}</th>
            <th width="18%">{{ trans('operational/fields.customer') }}</th>
            <th width="14%" >{{ trans('accountreceivables/fields.receipt-method') }}</th>
            <th width="7%" rowspan="2">{{ trans('accountreceivables/fields.amount') }}</th>
        </tr>
        <tr>
            <th >{{ trans('shared/common.date') }}</th>
            <th >{{ trans('shared/common.created-by') }}</th>
            <th >{{ trans('accountreceivables/fields.invoice-type') }}</th>
            <th >{{ trans('operational/fields.route') }}</th>
            <th >{{ trans('operational/fields.sender') }}</th>
            <th >{{ trans('operational/fields.receiver') }}</th>
            <th >{{ trans('accountreceivables/fields.cash-or-bank') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $totalAmount   = 0; 
        ?>
        @foreach($models as $model)
        <?php
            $date       = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
            $model      = Receipt::find($model->receipt_id);

        ?>
        <tr>
            <td width="3%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="11%" >{{ $model->receipt_number }}</td>
            <td width="7%">{{ $model->type }}</td>
            <td width="11%" >{{ !empty($model->invoice) ? $model->invoice->invoice_number : '' }}</td>
            <td width="11%" >{{ !empty($model->invoice->resi) ? $model->invoice->resi->resi_number : '' }}</td>
            <td width="18%" >{{ !empty($model->invoice->resi->customer) ? $model->invoice->resi->customer->customer_name : '' }}</td>
            <td width="18%" >{{ !empty($model->invoice->resi->customerReceiver) ? $model->invoice->resi->customerReceiver->customer_name : '' }}</td>
            <td width="14%" >{{ $model->receipt_method }}</td>
            <td width="7%" align="right" rowspan="2">{{ number_format($model->amount) }}</td>
        </tr>
        <tr>
            <td>{{ !empty($date) ? $date->format('d-m-Y') : '' }}</td>
            <td>{{ !empty($model->createdBy) ? $model->createdBy->full_name : '' }}</td>
            <td>{{ !empty($model->invoice) ? $model->invoice->type : '' }}</td>
            <td>{{ !empty($model->invoice->resi->route) ? $model->invoice->resi->route->route_code : '' }}</td>
            <td>{{ !empty($model->invoice->resi) ? $model->invoice->resi->sender_name : '' }}</td>
            <td>{{ !empty($model->invoice->resi) ? $model->invoice->resi->receiver_name : '' }}</td>
            <td>{{ !empty($model->bank) ? $model->bank->bank_name : '' }}</td>
        </tr>
        <?php 
            $totalAmount   += $model->amount;
         ?>
        @endforeach
        <tr>
            <td width="93%" align="center"><strong>{{ trans('shared/common.total') }}</strong></td>
            <td width="7%" align="right"><strong>{{ number_format($totalAmount) }}</strong></td>
        </tr>
    </tbody>
</table>
<?php
use App\Modules\Operational\Model\Master\MasterCity;
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$date = new \DateTime;

$branchManager = DB::table('adm.user_role_branch')
                    ->select('users.full_name')
                    ->join('adm.user_role', 'user_role.user_role_id', '=', 'user_role_branch.user_role_id')
                    ->join('adm.users', 'users.id', '=', 'user_role.user_id')
                    ->where('user_role_branch.branch_id', \Session::get('currentBranch')->branch_id)
                    ->where('user_role.role_id', App\Role::BRANCH_MANAGER)
                    ->first();
?>
<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="50%" align="center"></td>
            <td width="50%" align="center">{{ $city->city_name }}, {{ $date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td width="25%" align="center">{{ trans('shared/common.cashier') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.finance-admin') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.finance') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.branch-manager') }}</td>
        </tr>
        <tr>
            <td width="100%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="25%" align="center">(.............................................)</td>
            <td width="25%" align="center">(.............................................)</td>
            <td width="25%" align="center">(.............................................)</td>
            <td width="25%" align="center">(.............................................)</td>
        </tr>
    </tbody>
</table>
@endsection
