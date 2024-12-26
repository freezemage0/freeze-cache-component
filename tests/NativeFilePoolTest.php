<?php

declare(strict_types=1);

namespace Freeze\Component\Cache\Test;

use Cache\IntegrationTests\CachePoolTest;
use Freeze\Component\Cache\ItemPool;
use Freeze\Component\Cache\Storage\FileStorage;
use Freeze\Component\Serializer\NativeSerializer;

final class NativeFilePoolTest extends CachePoolTest
{
    protected $skippedTests = [
            'testGetItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testHasItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testDeleteItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
    ];
    private static string $temporaryFile;

    public function createCachePool(): ItemPool
    {
        return new ItemPool(new FileStorage(
                NativeFilePoolTest::getTemporaryFile(),
                new NativeSerializer()
        ));
    }

    private static function getTemporaryFile(): string
    {
        return NativeFilePoolTest::$temporaryFile ??= \tempnam(\sys_get_temp_dir(), 'cache_');
    }
}
