<?php

declare(strict_types=1);

namespace Tests\Factory\Mock;

use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use PHPUnit\Framework\TestCase;

class ActionMockFactory extends TestCase
{
    private const METHOD = 'getHandleMethod';

    public function createDamageActionMock(string $handleMethod): ActionInterface
    {
        $stub = $this->createMock(DamageAction::class);

        $stub->method(self::METHOD)
            ->willReturn($handleMethod);

        return $stub;
    }
}
