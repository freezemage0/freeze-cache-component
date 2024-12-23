<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache;

use Psr\Cache\InvalidArgumentException;

final class InvalidCacheKeyException extends \InvalidArgumentException implements InvalidArgumentException
{

}
