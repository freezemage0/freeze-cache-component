<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

final class Item implements CacheItemInterface
{
    private CacheKey           $key;
    private mixed              $value          = null;
    private ?DateTimeInterface $expirationDate = null;

    private bool  $isReady   = false;
    private ?bool $isExpired = null;

    public function __construct(string $key)
    {
        $this->key = new CacheKey($key);
    }

    public function getKey(): string
    {
        return (string)$this->key;
    }

    public function get(): mixed
    {
        if (!$this->isHit()) {
            return null;
        }

        return $this->value;
    }

    public function isHit(): bool
    {
        if (!$this->isReady) {
            return false;
        }

        if ($this->expirationDate === null) {
            return true;
        }

        return !($this->isExpired = \time() > $this->expirationDate->getTimestamp());
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->isExpired = null;
        $this->isReady = true;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expirationDate = $expiration;
        if ($expiration !== null) {
            $this->isExpired = \time() > $expiration->getTimestamp();
        } else {
            $this->isExpired = null;
        }

        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): static
    {
        if ($time === null) {
            return $this->expiresAt(null);
        }

        $now = new DateTime();
        if (\is_int($time)) {
            $time = DateInterval::createFromDateString("+{$time} seconds");
        }

        return $this->expiresAt($now->add($time));
    }

    public function getExpirationDate(): ?DateTimeInterface
    {
        return $this->expirationDate;
    }
}
