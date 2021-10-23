<?php

declare(strict_types=1);

namespace Battle\Unit\Classes;

use Battle\Result\Chat\ChatInterface;

abstract class AbstractUnitClass implements UnitClassInterface
{
    /**
     * @var ChatInterface
     */
    protected $chat;

    public function __construct(ChatInterface $message)
    {
        $this->chat = $message;
    }
}
