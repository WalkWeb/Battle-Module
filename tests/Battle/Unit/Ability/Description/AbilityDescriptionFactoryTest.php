<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Description;

use Battle\BattleException;
use Battle\Container\ContainerException;
use Battle\Unit\Ability\AbilityException;
use Battle\Unit\Ability\Description\AbilityDescriptionFactory;
use Exception;
use Tests\AbstractUnitTest;

class AbilityDescriptionFactoryTest extends AbstractUnitTest
{
    /**
     * @dataProvider successDataProvider
     * @param array $data
     * @param string $expectedDescription
     * @throws AbilityException
     * @throws BattleException
     * @throws ContainerException
     */
    public function testAbilityDescriptionFactoryCreateSuccess(array $data, string $expectedDescription): void
    {
        self::assertEquals($expectedDescription, (string)$this->getFactory()->create($data));
    }

    /**
     * @dataProvider failDataProvider
     * @param array $data
     * @param string $error
     * @throws AbilityException
     * @throws BattleException
     * @throws ContainerException
     */
    public function testAbilityDescriptionFactoryCreateFail(array $data, string $error): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);
        $this->getFactory()->create($data);
    }

    public function successDataProvider(): array
    {
        return [
            [
                [
                    'description' => 'text %d text %d',
                    'values' => [100, 200],
                ],
                'text 100 text 200',
            ],
            [
                [
                    'description' => 'abc %d',
                    'values' => [300],
                ],
                'abc 300',
            ],
        ];
    }

    public function failDataProvider(): array
    {
        return [
            // Отсутствует description
            [
                [
                    'values' => [300],
                ],
                AbilityException::INVALID_DESCRIPTION_DATA,
            ],
            // description некорректного типа
            [
                [
                    'description' => 123,
                    'values' => [300],
                ],
                AbilityException::INVALID_DESCRIPTION_DATA,
            ],
            // Отсутствует values
            [
                [
                    'description' => 'abc %d',
                ],
                AbilityException::INVALID_VALUES_DATA,
            ],
            // values некорректного типа
            [
                [
                    'description' => 'abc %d',
                    'values' => 300,
                ],
                AbilityException::INVALID_VALUES_DATA,
            ],
            // values содержит не int
            [
                [
                    'description' => 'abc %d',
                    'values' => [300, '100'],
                ],
                AbilityException::INVALID_VALUE_DATA,
            ],
        ];
    }

    private function getFactory(): AbilityDescriptionFactory
    {
        return new AbilityDescriptionFactory($this->getContainer());
    }
}
