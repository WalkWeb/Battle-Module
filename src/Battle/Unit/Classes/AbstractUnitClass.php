<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Result\Chat\Message\MessageInterface;

abstract class AbstractUnitClass implements UnitClassInterface
{
    /**
     * @var MessageInterface
     */
    protected $message;

    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }
}
