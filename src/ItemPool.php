<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache;

use Freeze\Component\FileCache\Contract\StorageInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class ItemPool implements CacheItemPoolInterface
{
    /** @var array<Item> */
    private array $items;

    public function __construct(
        private readonly StorageInterface $cacheStorage
    ) {
    }

    public function getItem(string $key, bool $validateKey = true): CacheItemInterface
    {
        if ($validateKey) {
            CacheKey::validate($key);
        }

        $this->load();

        return $this->items[$key] ??= new Item($key);
    }

    public function getItems(array $keys = []): iterable
    {
        $this->load();

        $items = [];
        foreach ($keys as $key) {
            CacheKey::validate($key);

            $items[$key] = $this->getItem($key, false);
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        $this->load();

        $item = $this->getItem($key, false);

        return $item->isHit();
    }

    public function clear(): bool
    {
        unset($this->items);

        $this->cacheStorage->clear();

        return true;
    }

    public function deleteItem(string $key): bool
    {
        $this->load();

        unset($this->items[$key]);

        return $this->persist();
    }

    public function deleteItems(array $keys): bool
    {
        $this->load();

        foreach ($keys as $key) {
            CacheKey::validate($key);

            unset($this->items[$key]);
        }

        return $this->persist();
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->load();

        $this->items[$item->getKey()] = $item;
        return $this->persist();
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (!($item instanceof DeferredItem)) {
            // First time item is put into deferred queue
            $this->items[$item->getKey()] = new DeferredItem($item);
        } else {
            // Item is already deferred, update stored value.
            $item->getOrigin()->set($item->getValue());
        }

        return true;
    }

    public function commit(): bool
    {
        $this->load();

        foreach ($this->items as $key => $item) {
            if (!($item instanceof DeferredItem)) {
                continue;
            }

            $this->items[$key] = $item->getOrigin();
        }

        return $this->persist();
    }

    private function load(): void
    {
        if (!isset($this->items)) {
            $this->items = [];

            foreach ($this->cacheStorage->retrieve() as $item) {
                $this->items[$item->getKey()] = $item;
            }
        }
    }

    private function persist(): bool
    {
        $this->cacheStorage->persist($this->items);

        return true;
    }

    public function __destruct()
    {
        $this->commit();
    }
}
