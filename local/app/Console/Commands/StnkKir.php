<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Operational\Model\Master\MasterTruck;
use App\Modules\Operational\Http\Controllers\Master\MasterTruckController;
use App\Service\NotificationService;
use App\Role;

class StnkKir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:alert-stnk-kir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert waktu kadaluarsa STNK dan KIR truk';
    const INTERVAL_DAYS = 30;
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
        foreach($this->getAllTruck() as $truck) {
            // $this->checkStnk($truck);
            $this->checkKir($truck);
        }
    }

    protected function getAllTruck()
    {
        return MasterTruck::get();
    }

    protected function checkStnk(MasterTruck $truck)
    {
        if (empty($truck->stnk_date)) {
            return;
        }

        $stnkDate      = new \DateTime($truck->stnk_date);
        $stnkDueDate   = new \DateTime();
        $stnkDueDate->add(new \DateInterval('P'.self::INTERVAL_DAYS.'D'));

        if ($stnkDate > $stnkDueDate) {
            return;
        }
        $this->sendNotifAlertStnk($truck, $stnkDate);
    }

    protected function checkKir(MasterTruck $truck)
    {
        if (empty($truck->kir_date)) {
            return;
        }

        $kirDate      = new \DateTime($truck->kir_date);
        $kirDueDate   = new \DateTime();
        $kirDueDate->add(new \DateInterval('P'.self::INTERVAL_DAYS.'D'));

        if ($kirDate > $kirDueDate) {
            return;
        }

        $this->sendNotifAlertKir($truck, $kirDate);
    }

    protected function sendNotifAlertStnk(MasterTruck $truck, \DateTime $stnkDate)
    {
        $title   = $stnkDate > new \DateTime() ? 'STNK Will Expire' : 'STNK Expired';
        $message = 'Truck '.$truck->truck_code.'. Police Number: '.$truck->police_number.
                    '. Due Date: '.$stnkDate->format('d-M-Y');
        
        NotificationService::createSpesificBranchNotification(
            $title,
            $message,
            null,
            // MasterTruckController::URL.'/edit/'.$truck->truck_id,
            [Role::BRANCH_MANAGER, Role::OPERATIONAL_ADMIN, Role::ADMINISTRATOR],
            $truck->branch_id
        );
        $this->info($title.' '.$message);
    }

    protected function sendNotifAlertKir(MasterTruck $truck, \DateTime $kirDate)
    {
        $title   = $kirDate > new \DateTime() ? 'KIR Will Expire' : 'KIR Expired';
        $message = 'Truck '.$truck->truck_code.'. Police Number: '.$truck->police_number.
                    '. Due Date: '.$kirDate->format('d-M-Y');
        NotificationService::createSpesificBranchNotification(
            $title,
            $message,
            null,
            // MasterTruckController::URL.'/edit/'.$truck->truck_id,
            [Role::BRANCH_MANAGER, Role::OPERATIONAL_ADMIN, Role::ADMINISTRATOR],
            $truck->branch_id
        );
        $this->info($title.' '.$message);
    }
}
