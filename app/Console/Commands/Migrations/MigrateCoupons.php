<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Console\Commands\ReadCsvFile;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateCoupons extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:coupons';

    protected $description = 'Migrate the coupons provided in the coupons.csv CSV file (database/project-aanvraag/coupons.csv)';

    public function handle(CouponRepository $couponRepository): int
    {
        Model::unsetEventDispatcher();

        CauserResolver::setCauser(UserModel::createSystemUser());

        $couponsAsArray = $this->readCsvFile('database/project-aanvraag/coupons.csv');

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

            $coupon = new Coupon(
                Uuid::uuid4(),
                (bool) $isDistributed,
                null,
                $code
            );
            $couponRepository->save($coupon);
        }

        return 0;
    }
}
