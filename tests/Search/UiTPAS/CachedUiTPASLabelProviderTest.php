<?php

declare(strict_types=1);

namespace Tests\Search\UiTPAS;

use App\Search\UiTPAS\CachedUiTPASLabelProvider;
use App\Search\UiTPAS\UiTPASLabelProvider;
use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Cache\Repository as Cache;

final class CachedUiTPASLabelProviderTest extends TestCase
{
    public function test_it_gets_labels_from_cache_or_falls_back_to_provider(): void
    {
        $expectedLabels = ['labels:foo', 'labels:bar'];

        $provider = $this->createMock(UiTPASLabelProvider::class);
        $provider->expects($this->once())
            ->method('getLabels')
            ->willReturn($expectedLabels);

        $cache = $this->createMock(Cache::class);
        $cache->expects($this->once())
            ->method('remember')
            ->with(
                $this->equalTo('uitpas_labels'),
                $this->callback(function ($ttl) {
                    return $ttl instanceof \DateTimeInterface;
                }),
                $this->callback(function ($callback) use ($expectedLabels) {
                    return $callback() === $expectedLabels;
                })
            )
            ->willReturn($expectedLabels);

        $cachedProvider = new CachedUiTPASLabelProvider($provider, $cache);

        $result = $cachedProvider->getLabels();

        $this->assertSame($expectedLabels, $result);
    }
}
