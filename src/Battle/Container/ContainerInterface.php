<?php

declare(strict_types=1);

namespace Battle\Container;

interface ContainerInterface
{
    /**
     * @param string $id
     * @return object
     */
    public function get(string $id): object;

    /**
     * @param string $id
     * @return bool
     */
    public function exist(string $id): bool;
}
