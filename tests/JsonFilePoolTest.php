<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache\Test;

use Cache\IntegrationTests\CachePoolTest;
use Freeze\Component\FileCache\ItemPool;
use Freeze\Component\FileCache\Storage\FileStorage;
use Freeze\Component\Serializer\JsonSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

use const JSON_INVALID_UTF8_IGNORE;

final class JsonFilePoolTest extends CachePoolTest
{
    protected $skippedTests = [
            'testGetItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testHasItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
            'testDeleteItemInvalidKeys' => 'Provided invalid keys cannot happen in psr/cache 3.0',
    ];

    private static string $temporaryFile;

    public function createCachePool(): CacheItemPoolInterface
    {
        return new ItemPool(new FileStorage(
                JsonFilePoolTest::getTemporaryFile(),
                new JsonSerializer(JSON_INVALID_UTF8_IGNORE)
        ));
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        \unlink(JsonFilePoolTest::getTemporaryFile());
    }

    private static function getTemporaryFile(): string
    {
        return JsonFilePoolTest::$temporaryFile ??= \tempnam(\sys_get_temp_dir(), 'cache_');
    }
}
