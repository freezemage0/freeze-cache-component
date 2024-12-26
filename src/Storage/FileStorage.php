<?php

declare(strict_types=1);

namespace Freeze\Component\Cache\Storage;

use DateTime;
use Exception;
use Freeze\Component\Cache\Contract\StorageInterface;
use Freeze\Component\Cache\Exception\RuntimeException;
use Freeze\Component\Cache\Item;
use Freeze\Component\Serializer\Contract\SerializerInterface;
use Freeze\Component\Serializer\NativeSerializer;

final class FileStorage implements StorageInterface
{
    public function __construct(
            private readonly string $cacheFilepath,
            private readonly SerializerInterface $serializer = new NativeSerializer()
    ) {
        $this->ensureFileExists();
    }

    /**
     * @param array<Item> $items
     * @return void
     */
    public function persist(array $items): void
    {
        $items = \array_map(
                static fn(Item $item): array => [
                        'expiresAt' => $item->getExpirationDate()?->getTimestamp(),
                        'key'       => $item->getKey(),
                        'value'     => $item->get(),
                ],
                $items
        );

        $serialized = $this->serializer->serialize($items);
        if (\file_put_contents($this->cacheFilepath, $serialized) === false) {
            throw new RuntimeException('Failed to save cache');
        }
    }

    /**
     * @return array<Item>
     */
    public function retrieve(): array
    {
        $items = \file_get_contents($this->cacheFilepath);
        if ($items === false || $items === '') {
            return [];
        }

        $items = $this->serializer->deserialize($items);

        return \array_filter(
                \array_map(
                        function (array $item): ?Item {
                            if (!isset($item['key'])) {
                                return null;
                            }

                            $result = new Item($item['key']);
                            if (isset($item['value'])) {
                                $result->set($item['value']);
                            }

                            if (isset($item['expiresAt'])) {
                                try {
                                    $expirationDate = (new DateTime())->setTimestamp((int)$item['expiresAt']);
                                } catch (Exception $e) {
                                    throw new RuntimeException(
                                            message: 'Failed to retrieve cache: invalid expiration timestamp',
                                            previous: $e
                                    );
                                }
                            } else {
                                $expirationDate = null;
                            }

                            $result->expiresAt($expirationDate);

                            return $result;
                        },
                        $items
                )
        );
    }

    public function clear(): void
    {
        if (\file_put_contents($this->cacheFilepath, '') === false) {
            throw new RuntimeException('Failed to clear cache');
        }
    }

    private function ensureFileExists(): void
    {
        $cacheDirectory = \dirname($this->cacheFilepath);
        if (!\is_dir($cacheDirectory)) {
            \mkdir($cacheDirectory, 0755, true);
        }

        if (!\is_file($this->cacheFilepath)) {
            \touch($this->cacheFilepath);
        }
    }
}
