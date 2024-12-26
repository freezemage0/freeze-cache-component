<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache\Test;

use Cache\IntegrationTests\CachePoolTest;
use Freeze\Component\FileCache\ItemPool;
use Freeze\Component\Serializer\NativeSerializer;

final class ItemPoolTest extends CachePoolTest
{
    protected $skippedTests = [
            'testGetItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testHasItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testDeleteItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
    ];
    private const CACHE_PATH = __DIR__ . '/../.cache';

    public function createCachePool(): ItemPool
    {
        return new ItemPool(
                self::CACHE_PATH,
                new NativeSerializer()
        );
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        \unlink(ItemPoolTest::CACHE_PATH);
    }
}
