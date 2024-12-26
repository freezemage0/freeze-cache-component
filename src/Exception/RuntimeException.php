<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache\Exception;

use Psr\Cache\CacheException;

final class RuntimeException extends \RuntimeException implements CacheException
{
}
