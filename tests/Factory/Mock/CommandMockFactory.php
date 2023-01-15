<?php

declare(strict_types=1);

namespace Tests\Factory\Mock;

use Battle\Command\Command;
use PHPUnit\Framework\TestCase;

class CommandMockFactory extends TestCase
{
    /**
     * Создает мок сломанной команды, которая на вызов метода isAlive() вернет true, а на getDefinedUnit(), т.е. на
     * запрос юнита для атаки вернет null (т.е. живых нет)
     *
     * @return Command
     */
    public function createAliveAndNoDefinedUnit(): Command
    {
        $stub = $this->createMock(Command::class);

        $stub->method('isAlive')
            ->willReturn(true);

        $stub->method('getUnitForAttacks')
            ->willReturn(null);

        return $stub;
    }
}
