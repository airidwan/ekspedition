<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\HistoryTransaction;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Service\Transaction\HistoryTransactionService;

class HistoryManifestService
{
    public static function saveHistory($manifestId, $transactionName, $description = null)
    {
        $manifest = ManifestHeader::find($manifestId);
        if ($manifest === null) {
            return;
        }

        $data = self::getDataManifest($manifest);

        HistoryTransactionService::saveHistory(HistoryTransaction::MANIFEST, $manifestId, $manifest->manifest_number, $transactionName, $description, $data);
    }

    protected static function getDataManifest(ManifestHeader $manifest)
    {
        $route = $manifest->route;
        $truck = $manifest->truck;
        $branch = $manifest->branch;
        $driver = $manifest->driver;
        $driverAssistant = $manifest->driverAssistant;

        $manifestStd = new \StdClass();
        $manifestStd->manifest_number = $manifest->manifest_number;
        $manifestStd->route_code = $route !== null ? $route->route_code : '';
        $manifestStd->truck_code = $truck !== null ? $truck->truck_code : '';
        $manifestStd->police_number = $truck !== null ? $truck->police_number : '';
        $manifestStd->truck_owner = $truck !== null ? $truck->owner_name : '';
        $manifestStd->driver_code = $driver !== null ? $driver->driver_code : '';
        $manifestStd->driver_name = $driver !== null ? $driver->driver_name : '';
        $manifestStd->driver_assistant_code = $driverAssistant !== null ? $driverAssistant->driver_code : '';
        $manifestStd->driver_assistant_name = $driverAssistant !== null ? $driverAssistant->driver_name : '';
        $manifestStd->total_coly = $manifest->totalColy();
        $manifestStd->money_trip = number_format($manifest->money_trip);
        $manifestStd->status = $manifest->status;
        $manifestStd->branch_code = $branch !== null ? $branch->branch_code : '';

        $manifestStd->lines = self::getLineManifest($manifest);

        return $manifestStd;
    }

    protected static function getLineManifest(ManifestHeader $manifest)
    {
        $lines = [];
        foreach ($manifest->lines as $line) {
            $resi = $line->resi;

            $lineStd = new \StdClass();
            $lineStd->resi_number = $resi !== null ? $resi->resi_number : '';
            $lineStd->coly_sent = number_format($line->coly_sent);

            $lines[] = $lineStd;
        }

        return $lines;
    }
}
