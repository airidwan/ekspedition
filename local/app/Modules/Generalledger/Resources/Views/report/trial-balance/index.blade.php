@extends('layouts.master')

@section('title', trans('general-ledger/menu.trial-balance'))
<?php  
use App\Modules\Generalledger\Model\Transaction\JournalLine;
?>
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.trial-balance') }}</h2>
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
                                <label for="accountCode" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="accountCode" name="accountCode" value="{{ !empty($filters['accountCode']) ? $filters['accountCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="accountDescription" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account-description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="accountDescription" name="accountDescription" value="{{ !empty($filters['accountDescription']) ? $filters['accountDescription'] : '' }}">
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
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                            $branchId  = '';
                            if (!empty(old('branchId'))) {
                                $branchId = old('branchId');
                            } elseif (!empty($filters['branchId'])) {
                                $branchId = $filters['branchId'];
                            }
                            ?>
                            <div class="form-group {{ $errors->has('branchId') ? 'has-error' : '' }}">
                                <label for="branchId" class="col-sm-4 control-label">Cabang </label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="branchId" id="branchId">
                                        <option value="" >All Branch</option>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ $branchId == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('branchId'))
                                        <span class="help-block">{{ $errors->first('branchId') }}</span>
                                    @endif
                                </div>
                            </div>
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
                                <a href="{{ URL($url.'/print-pdf') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i> {{ trans('shared/common.print-pdf') }}
                                </a>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <h3>ACTIVA</h3>
                <h3>Fluent Activa</h3>
                <h4>Kas</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalActiva   = 0;
                            $totalPasiva   = 0;

                            $totalDebetAssetKas   = 0;
                            $totalCreditAssetKas  = 0;
                            $totalBalanceAssetKas = 0;
                            $counter = 0;
                            ?>
                            @foreach($assetKas as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAssetKas       = $model->debet - $model->credit;
                            $totalBalanceAssetKas += $balanceAssetKas;
                            $totalDebetAssetKas   += $model->debet;
                            $totalCreditAssetKas  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAssetKas; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAssetKas) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Bank</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetAssetBank   = 0;
                            $totalCreditAssetBank  = 0;
                            $totalBalanceAssetBank = 0;
                            $counter = 0;
                            ?>
                            @foreach($assetBank as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAssetBank       = $model->debet - $model->credit;
                            $totalBalanceAssetBank += $balanceAssetBank;
                            $totalDebetAssetBank   += $model->debet;
                            $totalCreditAssetBank  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAssetBank; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAssetBank) }}</strong></td>
                                <?php $totalBalanceAssetBank = $totalDebetAssetBank-$totalCreditAssetBank; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Stock</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetAssetPersediaan   = 0;
                            $totalCreditAssetPersediaan  = 0;
                            $totalBalanceAssetPersediaan = 0;
                            $counter = 0;
                            ?>
                            @foreach($assetPersediaan as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAssetPersediaan       = $model->debet - $model->credit;
                            $totalBalanceAssetPersediaan += $balanceAssetPersediaan;
                            $totalDebetAssetPersediaan   += $model->debet;
                            $totalCreditAssetPersediaan  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAssetPersediaan; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAssetPersediaan) }}</strong></td>
                                <?php $totalBalanceAssetPersediaan = $totalDebetAssetPersediaan-$totalCreditAssetPersediaan; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Rent</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetAssetSewa   = 0;
                            $totalCreditAssetSewa  = 0;
                            $totalBalanceAssetSewa = 0;
                            $counter = 0;
                            ?>
                            @foreach($assetSewa as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAssetSewa       = $model->debet - $model->credit;
                            $totalBalanceAssetSewa += $balanceAssetSewa;
                            $totalDebetAssetSewa   += $model->debet;
                            $totalCreditAssetSewa  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAssetSewa; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAssetSewa) }}</strong></td>
                                <?php $totalBalanceAssetSewa = $totalDebetAssetSewa-$totalCreditAssetSewa; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Receivables</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetAssetPiutang   = 0;
                            $totalCreditAssetPiutang  = 0;
                            $totalBalanceAssetPiutang = 0;
                            $counter = 0;
                            ?>
                            @foreach($assetPiutang as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAssetPiutang       = $model->debet - $model->credit;
                            $totalBalanceAssetPiutang += $balanceAssetPiutang;
                            $totalDebetAssetPiutang   += $model->debet;
                            $totalCreditAssetPiutang  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAssetPiutang; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAssetPiutang) }}</strong></td>
                                <?php $totalBalanceAssetPiutang = $totalDebetAssetPiutang-$totalCreditAssetPiutang; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h3>Fixed Activa</h3>
                <h4>Asset</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetAsset   = 0;
                            $totalCreditAsset  = 0;
                            $totalBalanceAsset = 0;
                            $counter = 0;
                            ?>
                            @foreach($asset as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAsset       = $model->debet - $model->credit;
                            $totalBalanceAsset += $balanceAsset;
                            $totalDebetAsset   += $model->debet;
                            $totalCreditAsset  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAsset; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAsset) }}</strong></td>
                                <?php $totalBalanceAsset = $totalDebetAsset-$totalCreditAsset; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Depreciation</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetAssetPenyusutan   = 0;
                            $totalCreditAssetPenyusutan  = 0;
                            $totalBalanceAssetPenyusutan = 0;
                            $counter = 0;
                            ?>
                            @foreach($assetPenyusutan as $key => $model)
                            <?php
                            $period               = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceAssetPenyusutan       = $model->debet - $model->credit;
                            $totalBalanceAssetPenyusutan += $balanceAssetPenyusutan;
                            $totalDebetAssetPenyusutan   += $model->debet;
                            $totalCreditAssetPenyusutan  += $model->credit;
                            $counter = $key+1;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalActiva += $totalDebetAssetPenyusutan; ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalDebetAssetPenyusutan) }}</strong></td>
                                <?php $totalBalanceAssetPenyusutan = $totalDebetAssetPenyusutan-$totalCreditAssetPenyusutan; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <h3>Total Activa Rp. <strong>{{ number_format($totalActiva) }} </strong></h3>
                <div class="clearfix"></div>
                <hr>
                <h3>PASIVA</h3>
                <h4>Debt</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetLiability   = 0;
                            $totalCreditLiability  = 0;
                            $totalBalanceLiability = 0;
                            ?>
                            @foreach($liability as $key => $model)
                            <?php
                            $period                 = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceLiability       = $model->debet - $model->credit;
                            $totalBalanceLiability += $balanceLiability;
                            $totalDebetLiability   += $model->debet;
                            $totalCreditLiability  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                            </tr>
                            @endforeach
                            @if(!empty($liability))
                            <tr>
                                <td class="text-center">{{ $key+2 }}</td>
                                <td>RUGI/LABA</td>
                                <td></td>
                                <td class="text-right">{{ number_format($profitLoss) }}</td>
                            </tr>
                            @endif
                            <?php $totalPasiva += $totalCreditLiability + $profitLoss ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalCreditLiability + $profitLoss) }}</strong></td>
                                <?php $totalBalanceLiability = $totalDebetLiability-$totalCreditLiability; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <h4>Equitas</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-description') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.account-code') }}</th>
                                <th width="150px">{{ trans('general-ledger/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalDebetLiabilityEquitas   = 0;
                            $totalCreditLiabilityEquitas  = 0;
                            $totalBalanceLiabilityEquitas = 0;
                            ?>
                            @foreach($liabilityEquitas as $key => $model)
                            <?php
                            $period                 = !empty($model->period) ? new \DateTime($model->period) : null;
                            $balanceLiabilityEquitas       = $model->debet - $model->credit;
                            $totalBalanceLiabilityEquitas += $balanceLiabilityEquitas;
                            $totalDebetLiabilityEquitas   += $model->debet;
                            $totalCreditLiabilityEquitas  += $model->credit;
                            ?>
                            <tr>
                                <td class="text-center">{{ $key+1 }}</td>
                                <td>{{ $model->coa_description }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                            </tr>
                            @endforeach
                            <?php $totalPasiva += $totalCreditLiabilityEquitas ?>
                            <tr>
                                <td class="text-center"></td>
                                <td></td>
                                <td class="text-right"> <strong>{{ trans('shared/common.total') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalCreditLiabilityEquitas) }}</strong></td>
                                <?php $totalBalanceLiabilityEquitas = $totalDebetLiabilityEquitas-$totalCreditLiabilityEquitas; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <h3>Total Pasiva Rp. <strong>{{ number_format($totalPasiva) }} </strong></h3>
            </div>
        </div>
    </div>
</div>
@endsection
