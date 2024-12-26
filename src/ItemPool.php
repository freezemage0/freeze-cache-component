<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache;

use DateTime;
use Freeze\Component\Serializer\Contract\SerializerInterface;
use Freeze\Component\Serializer\NativeSerializer;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class ItemPool implements CacheItemPoolInterface
{
    /** @var array<Item> */
    private array $items;

    public function __construct(
            private readonly string $cachePath,
            private readonly SerializerInterface $serializer = new NativeSerializer()
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

        return \file_put_contents($this->cachePath, '') !== false;
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

            if (!\is_readable($this->cachePath)) {
                return;
            }

            $contents = \file_get_contents($this->cachePath);
            if ($contents === '' || $contents === false) {
                return;
            }

            $items = (array) $this->serializer->deserialize($contents);

            foreach ($items as $v) {
                $item = new Item($v['key']);

                if (isset($v['expiresAt'])) {
                    $expiresAt = (new DateTime())->setTimestamp($v['expiresAt']);
                } else {
                    $expiresAt = null;
                }
                $item->expiresAt($expiresAt);

                if (isset($v['value'])) {
                    $item->set($v['value']);
                }

                $this->items[$item->getKey()] = $item;
            }
        }
    }

    private function persist(): bool
    {
        $items = \array_map(
                static fn(Item $item): array => [
                        'expiresAt' => $item->getExpirationDate()?->getTimestamp() ?? null,
                        'value' => $item->get(),
                        'key' => $item->getKey(),
                ],
                $this->items
        );


        return \file_put_contents($this->cachePath, $this->serializer->serialize($items)) !== false;
    }

    public function __destruct()
    {
        $this->commit();
    }
}
