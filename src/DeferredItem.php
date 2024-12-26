<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache;

use Psr\Cache\CacheItemInterface;

final class DeferredItem implements CacheItemInterface
{
    private mixed $value;

    public function __construct(
            private readonly CacheItemInterface $item
    ) {
    }

    public function getKey(): string
    {
        return $this->item->getKey();
    }

    public function get(): mixed
    {
        return $this->item->get();
    }

    public function isHit(): bool
    {
        return $this->item->isHit();
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->item->expiresAt($expiration);
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        $this->item->expiresAfter($time);
        return $this;
    }

    public function getOrigin(): CacheItemInterface
    {
        return $this->item;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
