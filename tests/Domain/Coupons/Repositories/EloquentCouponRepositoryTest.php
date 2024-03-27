<?php

declare(strict_types=1);

namespace Tests\Domain\Coupons\Repositories;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Coupons\Repositories\EloquentCouponRepository;
use Illuminate\Database\QueryException;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentCouponRepositoryTest extends TestCase
{
    private EloquentCouponRepository $couponRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->couponRepository = new EloquentCouponRepository();
    }

    public function test_it_can_save_a_coupon(): void
    {
        $coupon = new Coupon(
            Uuid::uuid4(),
            false,
            null,
            '12345678901'
        );

        $this->couponRepository->save($coupon);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'is_distributed' => false,
            'integration_id' => null,
            'code' => $coupon->code,
        ]);
    }

    public function test_it_cannot_save_coupons_with_the_same_code(): void
    {
        $uniqueCode = '12345678901';
        $coupon = new Coupon(
            Uuid::uuid4(),
            false,
            null,
            $uniqueCode,
        );

        $this->couponRepository->save($coupon);

        $duplicateCoupon = new Coupon(
            Uuid::uuid4(),
            false,
            null,
            $uniqueCode,
        );

        $this->expectException(QueryException::class);

        $this->couponRepository->save($duplicateCoupon);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'is_distributed' => false,
            'integration_id' => null,
            'code' => $uniqueCode,
        ]);

        $this->assertDatabaseMissing('coupons', [
            'id' => $duplicateCoupon->id,
            'is_distributed' => false,
            'integration_id' => null,
            'code' => $uniqueCode,
        ]);
    }

    public function test_it_can_get_a_coupon_from_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $unrelatedIntegrationId = Uuid::uuid4();

        $coupon = new Coupon(
            Uuid::uuid4(),
            true,
            $integrationId,
            '12345678901',
        );
        $this->couponRepository->save($coupon);

        $unrelatedCoupon = new Coupon(
            Uuid::uuid4(),
            true,
            $unrelatedIntegrationId,
            '10987654321',
        );
        $this->couponRepository->save($unrelatedCoupon);

        $foundCoupon = $this->couponRepository->getByIntegrationId($integrationId);
        $this->assertEquals($coupon, $foundCoupon);
    }

    public function test_it_can_get_a_coupon_by_code(): void
    {
        $coupon = new Coupon(
            Uuid::uuid4(),
            false,
            null,
            '12345678901'
        );

        CouponModel::query()->insert([
            'id' => $coupon->id,
            'is_distributed' => $coupon->isDistributed,
            'integration_id' => $coupon->integrationId,
            'code' => $coupon->code,
        ]);

        $foundCoupon = $this->couponRepository->getByCode($coupon->code);

        $this->assertEquals($coupon, $foundCoupon);
    }
}
