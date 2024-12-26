<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache;

use Freeze\Component\FileCache\Exception\InvalidCacheKeyException;
use Stringable;

final class CacheKey implements Stringable
{
    public function __construct(
            private readonly string $key
    ) {
        CacheKey::validate($this->key);
    }

    /**
     * @throws InvalidCacheKeyException
     */
    public static function validate($key): void
    {
        if (!\is_string($key)) {
            throw new InvalidCacheKeyException('Cache key must be a string');
        }

        if ($key === '') {
            throw new InvalidCacheKeyException('Cache key cannot be empty');
        }

        if (\preg_match('/[^A-Za-z0-9._-]/', $key, $matches)) {
            throw new InvalidCacheKeyException('Invalid cache key character: ' . $matches[0]);
        }

        $reservedCharacters = \preg_quote('{}@\\/:');
        if (\preg_match("~[{$reservedCharacters}]~", $key, $matches)) {
            throw new InvalidCacheKeyException('Cannot use reserved characters {}()/\@: in cache key');
        }
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
