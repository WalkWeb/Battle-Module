<?php

declare(strict_types=1);

namespace Tests\Battle\Traits;

use Battle\Traits\IdTrait;
use Exception;
use Tests\AbstractUnitTest;

class IdTraitTest extends AbstractUnitTest
{
    use IdTrait;

    /**
     * Тест на генерацию случайной строки
     *
     * @dataProvider successDataProvider
     * @param int $length
     * @param int $expectedLength
     * @throws Exception
     */
    public function testIdTraitLimitLength(int $length, int $expectedLength): void
    {
        $string = self::generateId($length);

        self::assertEquals($expectedLength, mb_strlen($string));
    }

    /**
     * @return array
     */
    public function successDataProvider(): array
    {
        return [
            [
                5,
                5,
            ],
            // Если указана длина больше 100 - она будет уменьшена до длины 100
            [
                150,
                100,
            ],
        ];
    }
}
