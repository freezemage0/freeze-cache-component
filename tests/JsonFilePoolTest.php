<?php

declare(strict_types=1);

namespace Freeze\Component\Cache\Test;

use Cache\IntegrationTests\CachePoolTest;
use Freeze\Component\Cache\ItemPool;
use Freeze\Component\Cache\Storage\FileStorage;
use Freeze\Component\Serializer\JsonSerializer;
use Psr\Cache\CacheItemPoolInterface;

use const JSON_INVALID_UTF8_IGNORE;

final class JsonFilePoolTest extends CachePoolTest
{
    protected $skippedTests = [
            'testGetItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testHasItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testDeleteItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
    ];

    private static ?string $temporaryFile = null;

    public function createCachePool(): CacheItemPoolInterface
    {
        return new ItemPool(new FileStorage(
                JsonFilePoolTest::getTemporaryFile(),
                new JsonSerializer(JSON_INVALID_UTF8_IGNORE)
        ));
    }

    private static function getTemporaryFile(): string
    {
        return JsonFilePoolTest::$temporaryFile ??= \tempnam(\sys_get_temp_dir(), 'cache_');
    }
}
