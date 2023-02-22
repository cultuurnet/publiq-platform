<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\Repositories\InsightlyMappingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateProjectWithCoupon implements ShouldQueue
{
    use Queueable;
    use CreatesProject;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly CouponRepository $couponRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationActivatedWithCoupon $integrationActivatedWithCoupon): void
    {
        $integrationId = $integrationActivatedWithCoupon->id;

        $insightlyProjectId = $this->createProject($integrationId, true);

        $this->insightlyClient->projects()->updateWithCoupon(
            $insightlyProjectId,
            $this->couponRepository->getByIntegrationId($integrationId)->code
        );

        $this->logger->info(
            'Project created for integration with coupon',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationId->toString(),
            ]
        );
    }

    public function failed(
        IntegrationActivatedWithCoupon $integrationActivatedWithCoupon,
        Throwable $exception
    ): void {
        $this->logger->error(
            'Failed create project for integration with coupon',
            [
                'domain' => 'insightly',
                'contact_id' => $integrationActivatedWithCoupon->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
