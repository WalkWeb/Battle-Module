<?php

declare(strict_types=1);

namespace Tests\Battle\Result\FullLog;

use PHPUnit\Framework\TestCase;
use Battle\Result\FullLog\FullLog;

class FullLogTest extends TestCase
{
    public function testFullLogAddDefault(): void
    {
        $fullLog = new FullLog();
        $messages = ['message 1', 'message 2', 'message 3'];
        $fullLog->add($messages[0]);
        $fullLog->add($messages[1]);
        $fullLog->add($messages[2]);
        self::assertEquals($messages, $fullLog->getLog());
    }

    public function testFullLogAddText(): void
    {
        $fullLog = new FullLog();
        $messages = ['<p class="chat_message">text 1</p>', '<p class="chat_message">text 2</p>'];
        $fullLog->addText('text 1');
        $fullLog->addText('text 2');
        // Пустое сообщение не будет добавлено
        $fullLog->addText('');
        self::assertEquals($messages, $fullLog->getLog());
    }

    public function testFillLogAddLine(): void
    {
        $fullLog = new FullLog();
        $messages = ['<hr>', '<hr>'];
        $fullLog->addLine();
        $fullLog->addLine();
        self::assertEquals($messages, $fullLog->getLog());
    }
}
