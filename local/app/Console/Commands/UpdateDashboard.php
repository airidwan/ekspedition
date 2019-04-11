<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Service\CurrentRoleService;
use App\Service\CurrentBranchService;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Role;
use App\User;
use App\Dashboard;

class UpdateDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:update-data-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update data dashboard';
    protected $now;
    protected $months;
    protected $branchs;
    protected $branch_id;
    protected $branch_code;
    protected $branch_code_numeric;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->now = new \DateTime();
        $this->months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->branchs = $this->getAllBranchs();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = new \DateTime();
        $this->info('==========================');
        $this->info('Start: '.$now->format('d-m-Y H:i:s').PHP_EOL);

        $this->proses();

        $now = new \DateTime();
        $this->info(PHP_EOL.'End: '.$now->format('d-m-Y H:i:s').PHP_EOL);
        $this->info('==========================');
    }

    protected function proses()
    {
        foreach ($this->branchs as $branch) {

            // $this->info('==========================');
            $this->info($branch->branch_code);
            $this->branch_id           = $branch->branch_id;
            $this->branch_code         = $branch->branch_code;
            $this->branch_code_numeric = $branch->branch_code_numeric;


            $model = Dashboard::where('month', $this->now->format('m'))
                                ->where('year', $this->now->format('Y'))
                                ->where('branch_id', $this->branch_id)
                                ->first();

            if($model === null){
                $model = new Dashboard();
            }

            // $this->info('Total resi');
            $model->total_resi                      = $this->getTotalResi();
            // $this->info('Total manifest');
            $model->total_manifest                  = $this->getTotalManifest();
            // $this->info('Total do');
            $model->total_do                        = $this->getTotalDO();
            // $this->info('Total received');
            $model->total_resi_received             = $this->getTotalResiReceived()
            ;
            // $this->info('Data resi per month');
            $model->data_resi_per_month             = json_encode($this->getDataGraphResiPerMonth());
            // $this->info('Data do per month');
            $model->data_do_per_month               = json_encode($this->getDataGraphDOPerMonth());

            // $this->info('Data resi vs received');
            $model->data_resi_vs_received           = json_encode($this->getDataGraphResiVsReceived());
            // $this->info('Data resi this month');
            $model->data_resi_this_month            = json_encode($this->getDataGraphResiThisMonth());
            // $this->info('Data resi received this month');
            $model->data_resi_received_this_month   = json_encode($this->getDataGraphResiReceivedThisMonth());

            $model->month       = $this->now->format('m');
            $model->year        = $this->now->format('Y');
            $model->branch_id   = $this->branch_id;
            // $this->info('==========================');

            $model->save();
        }
    }

    protected function getAllBranchs()
    {
        $query = \DB::table('op.mst_branch')
                    ->where('active', '=', 'Y')
                    ->orderBy('mst_branch.branch_code', 'asc');

        return $query->get();
    }

    protected function getDataGraphResiPerMonth()
    {
        $data = [];
        foreach ($this->months as $key => $month) {
            $intMonth          = $key + 1;
            $dataItem          = [];
            $dataItem['month'] = $month;

            if($this->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO){
                foreach ($this->branchs as $branch) {
                    $dataItem[$branch->branch_id] = $this->getTotalResi($branch->branch_id, $intMonth);
                }
            }else{
                $dataItem[$this->branch_id] = $this->getTotalResi($this->branch_id, $intMonth);
            }

            $data[] = $dataItem;
        }

        $yKeys = [];
        $labels = [];
        if($this->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO){
            foreach ($this->branchs as $branch) {
                $yKeys[] = $branch->branch_id;
            }

            foreach ($this->branchs as $branch) {
                $labels[] = $branch->branch_code;
            }
        }else{
                $yKeys[] = $this->branch_id;
                $labels[] = $this->branch_code;
        }

        return [
            'data'   => $data,
            'xKey'   => 'month',
            'yKeys'  => $yKeys,
            'labels' => $labels,
        ];
    }

    protected function getDataGraphDOPerMonth()
    {
        $data = [];
        foreach ($this->months as $key => $month) {
            $intMonth          = $key + 1;
            $dataItem          = [];
            $dataItem['month'] = $month;

            if($this->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO){
                foreach ($this->branchs as $branch) {
                    $dataItem[$branch->branch_id] = $this->getTotalDO($branch->branch_id, $intMonth);
                }
            }else{
                $dataItem[$this->branch_id] = $this->getTotalDO($this->branch_id, $intMonth);

            }

            $data[] = $dataItem;
        }

        $yKeys = [];
        $labels = [];

        if($this->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO){
            foreach ($this->branchs as $branch) {
                $yKeys[] = $branch->branch_id;
            }
            foreach ($this->branchs as $branch) {
                $labels[] = $branch->branch_code;
            }
        }else{
            $yKeys[]  = $this->branch_id;
            $labels[] = $this->branch_code;
        }

        return [
            'data'   => $data,
            'xKey'   => 'month',
            'yKeys'  => $yKeys,
            'labels' => $labels,
        ];
    }

    protected function getDataGraphResiVsReceived()
    {
        $data = [];
        foreach ($this->months as $key => $month) {
            $intMonth             = $key + 1;
            $dataItem             = [];
            $dataItem['month']    = $month;
            $dataItem['resi']     = $this->getTotalResi(null, $intMonth);
            $dataItem['received'] = $this->getTotalResiReceived(null, $intMonth);

            $data[] = $dataItem;
        }

        $yKeys  = ['resi', 'received'];
        $labels = ['Total Resi', 'Total Resi Received'];

        return [
            'data'   => $data,
            'xKey'   => 'month',
            'yKeys'  => $yKeys,
            'labels' => $labels,
        ];
    }

    protected function getDataGraphResiThisMonth()
    {
        $data = [];

        if($this->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO){
            foreach ($this->branchs as $branch) {
                $dataItem['label'] = $branch->branch_code;
                $dataItem['value'] = $this->getTotalResi($branch->branch_id);

                $data[] = $dataItem;
            }
        }else{
            $dataItem['label'] = $this->branch_code;
            $dataItem['value'] = $this->getTotalResi($this->branch_id);

            $data[] = $dataItem;
        }


        return [
            'data'   => $data,
        ];
    }

    protected function getDataGraphResiReceivedThisMonth()
    {
        $data = [];
        
        if($this->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO){
            foreach ($this->branchs as $branch) {
                $dataItem['label'] = $branch->branch_code;
                $dataItem['value'] = $this->getTotalResiReceived($branch->branch_id);

                $data[] = $dataItem;
            }
        }else{
                $dataItem['label'] = $this->branch_code;
                $dataItem['value'] = $this->getTotalResiReceived($this->branch_id);

                $data[] = $dataItem;
        }

        return [
            'data'   => $data,
        ];
    }

    protected function getTotalResi($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_resi_header')
                        ->where('created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereIn('status', [TransactionResiHeader::APPROVED]);

        if ($this->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('branch_id', '=', $this->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getTotalManifest($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_manifest_header')
                        ->where('created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereIn('status', [ManifestHeader::OTR, ManifestHeader::ARRIVED, ManifestHeader::CLOSED, ManifestHeader::CLOSED_WARNING]);

        if ($this->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('branch_id', '=', $this->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getTotalDO($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_delivery_order_header')
                        ->where('created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereIn('status', [DeliveryOrderHeader::ON_THE_ROAD, DeliveryOrderHeader::CLOSED]);

        if ($this->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('branch_id', '=', $this->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('branch_id', '=', $branchId);
        }

        return $query->count();
    }

    protected function getTotalResiReceived($branchId = null, $month = null)
    {
        $date  = !empty($month) ? new \DateTime($this->now->format('Y').'-'.$month.'-1') : new \DateTime($this->now->format('Y-m-1'));
        $query = \DB::table('op.trans_resi_header')
                        ->select('trans_resi_header.resi_header_id')
                        ->join('op.v_received_ant_taken_resi', 'v_received_ant_taken_resi.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->where('trans_resi_header.created_date', '>=', $date->format('Y-m-1 00:00:00'))
                        ->where('trans_resi_header.created_date', '<=', $date->format('Y-m-t 23:59:59'))
                        ->whereRaw('v_received_ant_taken_resi.total_coly - v_received_ant_taken_resi.coly_received - v_received_ant_taken_resi.coly_taken <= 0')
                        ->distinct();

        if ($this->branch_code_numeric != MasterBranch::KODE_NUMERIC_HO) {
            $query->where('trans_resi_header.branch_id', '=', $this->branch_id);
        } elseif (!empty($branchId)) {
            $query->where('trans_resi_header.branch_id', '=', $branchId);
        }

        return $query->count();
    }
}
