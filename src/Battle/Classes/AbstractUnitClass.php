<?php

declare(strict_types=1);

namespace Battle\Classes;

use Battle\Result\Chat\Message\Message;

abstract class AbstractUnitClass implements UnitClassInterface
{
    /**
     * @var Message
     */
    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
