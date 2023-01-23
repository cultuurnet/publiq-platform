<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Coupons\Models\CouponModel;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateCoupons extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:coupons';

    protected $description = 'Migrate the coupons provided in the coupons.csv CSV file';

    public function handle(): int
    {
        CauserResolver::setCauser(UserModel::createSystemUser());

        // Read the coupons from CSV file
        $couponsAsArray = $this->readCsvFile('coupons.csv');

        $couponsCount = count($couponsAsArray);
        if ($couponsCount <= 0) {
            $this->warn('No coupons to import');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $couponsCount . ' coupons?')) {
            return 0;
        }

        foreach ($couponsAsArray as $couponAsArray) {
            if (!is_array($couponAsArray)) {
                continue;
            }

            [$code, $isDistributed] = $couponAsArray;

            if (strlen($code) !== 11) {
                $this->warn('The code ' . $code . ' was not imported because length is not 11.');
                continue;
            }

            $this->info('Importing code ' . $code);

            $couponModel = new CouponModel([
                'id' => Uuid::uuid4(),
                'is_distributed' => $isDistributed,
                'integration_id' => null,
                'code' => $code,
            ]);
            $couponModel->save();
        }

        return 0;
    }
}
