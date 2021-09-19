<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat\Message;

use Battle\Action\DamageAction;
use Battle\Result\Chat\Message\Message;
use Battle\Result\Chat\Message\MessageException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Battle\Factory\BaseFactory;

// TODO Реализовать полноценные тесты на Message в одном классе. Сейчас они сделаны частями в разных тестах.

class MessageTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testMessageUndefinedMethod(): void
    {
        [$unit, $command, $enemyCommand] = BaseFactory::create(1, 2);

        $message = new Message();

        $action = new DamageAction(
            $unit,
            $enemyCommand,
            $command,
            DamageAction::TARGET_RANDOM_ENEMY,
            30,
            'test attack',
            null,
            'undefinedMessageMethod'
        );

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage(MessageException::UNDEFINED_MESSAGE_METHOD);
        $message->createMessage($action);
    }
}
