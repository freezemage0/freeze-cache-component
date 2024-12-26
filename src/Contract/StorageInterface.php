<?php

declare(strict_types=1);

namespace Freeze\Component\FileCache\Contract;

use Freeze\Component\FileCache\Item;

interface StorageInterface
{
    /**
     * @param array<Item> $items
     * @return void
     */
    public function persist(array $items): void;

    /**
     * @return array<Item>
     */
    public function retrieve(): array;

    public function clear(): void;
}
