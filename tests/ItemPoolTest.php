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
    private static string $temporaryFile;

    public function createCachePool(): ItemPool
    {
        return new ItemPool(ItemPoolTest::getTemporaryFile(), new NativeSerializer());
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        \unlink(ItemPoolTest::getTemporaryFile());
    }

    private static function getTemporaryFile(): string
    {
        return ItemPoolTest::$temporaryFile ??= \tempnam(\sys_get_temp_dir(), 'cache_');
    }
}
