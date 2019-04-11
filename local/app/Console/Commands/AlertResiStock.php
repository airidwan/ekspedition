<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Operational\Model\Transaction\ResiStock;
use App\Modules\Operational\Model\Master\MasterAlertResiStock;
use App\Service\NotificationService;
use App\Role;

class AlertResiStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:alert-resi-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert saat resi stock ngendon melebihi batas nya';

    protected $now;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->now = new \DateTime();
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
    }

    protected function proses()
    {
        foreach($this->getAllResiStock() as $resiStock) {
            $alertResiStock = $this->getAlertResiStock($resiStock);
            if ($alertResiStock === null || $alertResiStock->minimum_days <= 0) {
                continue;
            }

            $tanggalBatas = new \DateTime($resiStock->created_date);
            $minimumDays  = $alertResiStock->minimum_days;

            $tanggalBatas->add(new \DateInterval('P'.$minimumDays.'D'));

            if ($tanggalBatas > $this->now) {
                continue;
            }

            $this->sendNotifAlertResiStock($resiStock, $tanggalBatas);
            $this->info('Alert Resi Stock: '.$resiStock->resi->resi_number.'. Batas: '.$tanggalBatas->format('d-m-Y').'. Branch: '.$resiStock->branch->branch_code);
        }
    }

    protected function getAllResiStock()
    {
        return ResiStock::orderBy('branch_id')->orderBy('resi_header_id')->get();
    }

    protected function getAlertResiStock(ResiStock $resiStock)
    {
        $route = $resiStock->resi !== null ? $resiStock->resi->route : null;
        if ($route === null) {
            return;
        }

        return MasterAlertResiStock::where('branch_id', '=', $resiStock->branch_id)->where('city_end_id', '=', $route->city_end_id)->first();
    }

    protected function isResiStockMelebihiBatas(ResiStock $resiStock, MasterAlertResiStock $alertResiStock)
    {
        $createdDate = new \DateTime($resiStock->created_date);
        $minimumDays = $alertResiStock->minimum_days;

        $createdDate->add(new \DateInterval('P'.$minimumDays.'D'));

        return $this->now > $createdDate;
    }

    protected function sendNotifAlertResiStock(ResiStock $resiStock, \DateTime $tanggalBatas)
    {
        $route = $resiStock->resi->route;
        $destination = $route->cityEnd !== null ? $route->cityEnd->city_name : '';
        $message = 'Resi '.$resiStock->resi->resi_number.'. Destination: '.$destination.
                    '. Coly: '.number_format($resiStock->coly).'. Limit Date: '.$tanggalBatas->format('d-M-Y');
        
        NotificationService::createSpesificBranchNotification(
            'Resi Stock Not Come Out',
            $message,
            null,
            [Role::BRANCH_MANAGER, Role::OPERATIONAL_ADMIN],
            $resiStock->branch_id
        );
    }
}
