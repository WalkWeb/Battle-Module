<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Ability\Description;

use Battle\Container\ContainerException;
use Battle\Unit\Ability\Description\AbilityDescription;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

class AbilityDescriptionTest extends AbstractTestCase
{
    /**
     * @throws ContainerException
     */
    #[DataProvider('successAbilityDescriptionDataProvider')]
    public function testAbilityDescriptionSuccess(string $description, array $values, string $expectedDescription): void
    {
        $abilityDescription = new AbilityDescription(
            $description,
            $values,
            $this->getContainer()->getTranslation()
        );

        self::assertEquals($expectedDescription, (string)$abilityDescription);
    }

    public static function successAbilityDescriptionDataProvider(): array
    {
        return [
            [
                'text %d text %d',
                [100, 200],
                'text 100 text 200',
            ],
            [
                'abc %d',
                [300],
                'abc 300',
            ],
        ];
    }
}
