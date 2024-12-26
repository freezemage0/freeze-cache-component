<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache\Test;

use Cache\IntegrationTests\CachePoolTest;
use Freeze\Component\FileCache\ItemPool;
use Freeze\Component\FileCache\Storage\FileStorage;
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

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        \unlink(NativeFilePoolTest::getTemporaryFile());
    }

    private static function getTemporaryFile(): string
    {
        return NativeFilePoolTest::$temporaryFile ??= \tempnam(\sys_get_temp_dir(), 'cache_');
    }
}
