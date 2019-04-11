@extends('layouts.print')

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['accountCode']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.account-code') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['accountCode'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['accountDescription']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.account-description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['accountDescription'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['periodMonth']) && !empty($filters['periodYear']))
                    <tr>
                        <td width="18%">{{ trans('general-ledger/fields.period') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['periodMonth'].' - '.$filters['periodYear'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['branchId']))
                    <?php $branch = \DB::table('op.mst_branch')->where('branch_id',$filters['branchId'])->first(); ?>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.branch') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $branch->branch_name }}</td>
                    </tr>
                @else
                    <tr>
                        <td width="18%">{{ trans('operational/fields.branch') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">All Branch</td>
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
<h3>ACTIVA</h3>
<h3>Fluent Activa</h3>
<h4>Cash</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $totalActiva   = 0;
         $totalPasiva   = 0;

         $no = 1;
         $totalDebetAssetCash   = 0; 
         $totalCreditAssetCash  = 0; 
         $totalBalanceAssetCash = 0;
         ?>
         @foreach($assetKas as $model)
         <?php  
         $balanceAssetCash       = $model->debet - $model->credit;
         $totalDebetAssetCash   += $model->debet;
         $totalCreditAssetCash  += $model->credit;
         $totalBalanceAssetCash += $balanceAssetCash;
         ?>
         
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAssetCash;?>
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAssetCash) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Bank</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetAssetBank   = 0; 
         $totalCreditAssetBank  = 0; 
         $totalBalanceAssetBank = 0;
         ?>
         @foreach($assetBank as $model)
         <?php  
         $balanceAssetBank       = $model->debet - $model->credit;
         $totalDebetAssetBank   += $model->debet;
         $totalCreditAssetBank  += $model->credit;
         $totalBalanceAssetBank += $balanceAssetBank;
         ?>
         
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAssetBank;?>

        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAssetBank) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Stock</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetAssetStock   = 0; 
         $totalCreditAssetStock  = 0; 
         $totalBalanceAssetStock = 0;
         ?>
         @foreach($assetPersediaan as $model)
         <?php  
         $balanceAssetStock       = $model->debet - $model->credit;
         $totalDebetAssetStock   += $model->debet;
         $totalCreditAssetStock  += $model->credit;
         $totalBalanceAssetStock += $balanceAssetStock;
         ?>
         
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAssetStock;?>

        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAssetStock) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Rent</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetAssetRent   = 0; 
         $totalCreditAssetRent  = 0; 
         $totalBalanceAssetRent = 0;
         ?>
         @foreach($assetSewa as $model)
         <?php  
         $balanceAssetRent       = $model->debet - $model->credit;
         $totalDebetAssetRent   += $model->debet;
         $totalCreditAssetRent  += $model->credit;
         $totalBalanceAssetRent += $balanceAssetRent;
         ?>
         
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAssetRent;?>

        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAssetRent) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Receivables</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetAssetReceivables   = 0; 
         $totalCreditAssetReceivables  = 0; 
         $totalBalanceAssetReceivables = 0;
         ?>
         @foreach($assetPiutang as $model)
         <?php  
         $balanceAssetReceivables       = $model->debet - $model->credit;
         $totalDebetAssetReceivables   += $model->debet;
         $totalCreditAssetReceivables  += $model->credit;
         $totalBalanceAssetReceivables += $balanceAssetReceivables;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAssetReceivables;?>

        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAssetReceivables) }}</strong></td>
        </tr>
    </tbody>
</table>
<h3>Fixed Activa</h3>
<h4>Asset</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetAsset   = 0; 
         $totalCreditAsset  = 0; 
         $totalBalanceAsset = 0;
         ?>
         @foreach($asset as $model)
         <?php  
         $balanceAsset       = $model->debet - $model->credit;
         $totalDebetAsset   += $model->debet;
         $totalCreditAsset  += $model->credit;
         $totalBalanceAsset += $balanceAsset;
         ?>
         
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAsset;?>
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAsset) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Depreciation</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetAssetDepreciation   = 0; 
         $totalCreditAssetDepreciation  = 0; 
         $totalBalanceAssetDepreciation = 0;
         ?>
         @foreach($assetPenyusutan as $model)
         <?php  
         $balanceAssetDepreciation       = $model->debet - $model->credit;
         $totalDebetAssetDepreciation   += $model->debet;
         $totalCreditAssetDepreciation  += $model->credit;
         $totalBalanceAssetDepreciation += $balanceAssetDepreciation;
         ?>
         
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->debet) }}</td>
        </tr>
        @endforeach
        <?php $totalActiva += $totalDebetAssetDepreciation;?>
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" ><strong>{{ trans('general-ledger/fields.total') }}</strong></td>
            <td width="25%" align="right"><strong>{{ number_format($totalDebetAssetDepreciation) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Total Activa <strong>{{ number_format($totalActiva) }}</strong></h4>
<hr>
<br>
<h3>PASIVA</h3>
<h4>Debt</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetLiability   = 0; 
         $totalCreditLiability  = 0; 
         $totalBalanceLiability = 0; 
         ?>
         @foreach($liability as $model)
         <?php  
         $balanceLiability       = $model->debet - $model->credit;
         $totalDebetLiability   += $model->debet;
         $totalCreditLiability  += $model->credit;
         $totalBalanceLiability += $balanceLiability;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->credit) }}</td>
        </tr>
        @endforeach
        <?php $totalPasiva += $totalCreditLiability + $profitLoss;?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >LABA/RUGI</td>
            <td width="30%" ></td>
            <td width="25%" align="right">{{ number_format($profitLoss) }}</td>
        </tr>
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" >{{ trans('general-ledger/fields.total') }}</td>
            <?php $totalBalanceLiability = $totalDebetLiability-$totalCreditLiability; ?>
            <td width="25%" align="right"><strong>{{ number_format($totalCreditLiability + $profitLoss) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Equitas</h4>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="40%">{{ trans('general-ledger/fields.account-description') }}</th>
            <th width="30%">{{ trans('general-ledger/fields.account-code') }}</th>
            <th width="25%">{{ trans('general-ledger/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php 
         $no = 1;
         $totalDebetLiabilityEquitas   = 0; 
         $totalCreditLiabilityEquitas  = 0; 
         $totalBalanceLiabilityEquitas = 0; 
         ?>
         @foreach($liabilityEquitas as $model)
         <?php  
         $balanceLiabilityEquitas       = $model->debet - $model->credit;
         $totalDebetLiabilityEquitas   += $model->debet;
         $totalCreditLiabilityEquitas  += $model->credit;
         $totalBalanceLiabilityEquitas += $balanceLiabilityEquitas;
         ?>
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="40%" >{{ $model->coa_description }}</td>
            <td width="30%" >{{ $model->coa_code }}</td>
            <td width="25%" align="right">{{ number_format($model->credit) }}</td>
        </tr>
        @endforeach
        <?php $totalPasiva += $totalCreditLiabilityEquitas;?>
        <tr>
            <td width="5%"  align="center"></td>
            <td width="40%" ></td>
            <td width="30%" >{{ trans('general-ledger/fields.total') }}</td>
            <td width="25%" align="right"><strong>{{ number_format($totalCreditLiabilityEquitas) }}</strong></td>
        </tr>
    </tbody>
</table>
<h4>Total Pasiva <strong>{{ number_format($totalPasiva) }}</strong></h4>

@endsection