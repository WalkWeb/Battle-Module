<?php

declare(strict_types=1);

namespace Tests\Battle\Result\Chat;

use Battle\Result\Chat\Chat;
use PHPUnit\Framework\TestCase;

class ChatTest extends TestCase
{
    public function testChatAddMessage(): void
    {
        $message1 = 'message #1';
        $message2 = 'message #2';

        $chat = new Chat();
        $chat->add($message1);
        $chat->add($message2);

        $expectedResult = [
            '<p class="' . Chat::HIDDEN_CLASS . '">' . $message1 . '</p>',
            '<p class="' . Chat::HIDDEN_CLASS . '">' . $message2 . '</p>',
        ];

        $i = 0;

        foreach ($chat->getMessages() as $message) {
            self::assertEquals($expectedResult[$i], $message);
            $i++;
        }
    }
}
