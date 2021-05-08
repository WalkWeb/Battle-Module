<?php

declare(strict_types=1);

namespace Tests\Battle\Factory\Mock;

use Battle\Action\ActionInterface;
use Battle\Action\Damage\DamageAction;
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
