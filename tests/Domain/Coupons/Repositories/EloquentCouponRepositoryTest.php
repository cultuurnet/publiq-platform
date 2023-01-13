<?php

declare(strict_types=1);

namespace Tests\Domain\Coupons\Repositories;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\EloquentCouponRepository;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentCouponRepositoryTest extends TestCase
{
    use RefreshDatabase;

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
            null,
            '12345678901'
        );

        $this->couponRepository->save($coupon);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'integration_id' => null,
            'code' => $coupon->code,
            'is_used' => false,
        ]);
    }

    public function test_it_cannot_save_coupons_with_the_same_code(): void
    {
        $uniqueCode = '12345678901';
        $coupon = new Coupon(
            Uuid::uuid4(),
            null,
            $uniqueCode
        );

        $this->couponRepository->save($coupon);

        $duplicateCoupon = new Coupon(
            Uuid::uuid4(),
            null,
            $uniqueCode
        );

        $this->expectException(QueryException::class);

        $this->couponRepository->save($duplicateCoupon);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'integration_id' => null,
            'code' => $uniqueCode,
            'is_used' => false,
        ]);

        $this->assertDatabaseMissing('coupons', [
            'id' => $duplicateCoupon->id,
            'integration_id' => null,
            'code' => $uniqueCode,
            'is_used' => false,
        ]);
    }

    public function test_it_can_get_a_coupon_from_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $unrelatedIntegrationId = Uuid::uuid4();

        $coupon = new Coupon(
            Uuid::uuid4(),
            $integrationId,
            '12345678901'
        );
        $this->couponRepository->save($coupon);

        $unrelatedCoupon = new Coupon(
            Uuid::uuid4(),
            $unrelatedIntegrationId,
            '10987654321'
        );
        $this->couponRepository->save($unrelatedCoupon);

        $foundCoupon = $this->couponRepository->getByIntegrationId($integrationId);
        $this->assertEquals($coupon, $foundCoupon);
    }
}
