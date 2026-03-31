<?php

declare(strict_types=1);

namespace Tests;

use App\Json;

final class JsonTest extends TestCase
{
    public function test_encode_preserving_empty_objects_converts_empty_arrays_to_objects(): void
    {
        $data = [
            'name' => 'test',
            'links' => [],
        ];

        $this->assertSame('{"name":"test","links":{}}', Json::encodePreservingEmptyObjects($data));
    }

    public function test_encode_preserving_empty_objects_handles_nested_empty_arrays(): void
    {
        $data = [
            'organizer' => [
                'id' => '123',
                'cardSystems' => [
                    ['id' => 1, 'name' => 'sys', 'links' => []],
                ],
            ],
        ];

        $result = Json::encodePreservingEmptyObjects($data);

        $this->assertStringContainsString('"links":{}', $result);
    }

    public function test_encode_preserving_empty_objects_leaves_non_empty_arrays_intact(): void
    {
        $data = [
            'items' => ['a', 'b'],
            'empty' => [],
        ];

        $result = Json::encodePreservingEmptyObjects($data);

        $this->assertSame('{"items":["a","b"],"empty":{}}', $result);
    }

    public function test_full_json_round_trip_preserves_empty_objects(): void
    {
        $originalJson = '{"organizer":{"id":"123","cardSystems":[{"id":1,"links":{}}]},"permissionDetails":[]}';

        $decoded = Json::decodeAssociatively($originalJson);
        $reEncoded = Json::encodePreservingEmptyObjects($decoded);

        $this->assertStringContainsString('"links":{}', $reEncoded);
        $this->assertStringNotContainsString('"links":[]', $reEncoded);
    }
}
