<?php

declare(strict_types=1);

namespace Tests\Battle\Unit\Classes\DataProvider;

use Battle\Unit\Classes\DataProvider\ClassDataProviderInterface;
use Battle\Unit\Classes\DataProvider\ExampleClassDataProvider;
use Battle\Unit\Classes\UnitClassException;
use Exception;
use Tests\AbstractUnitTest;

/**
 * Так как классов много, хранить тесты на их все в одном файле неразумно, по этому тесты под конкретные классы делаются
 * в отдельных классах в соответствии с расой/названием класса.
 *
 * В этом файле проверяется только базовый функционал поставщика данных
 *
 * @package Tests\Battle\Unit\Classes\DataProvider
 */
class ExampleClassDataProviderTest extends AbstractUnitTest
{
    /**
     * @throws Exception
     */
    public function testExampleClassDataProviderGetSuccess(): void
    {
        $classId = 1;
        $classData = $this->getDataProvider()->get($classId);
        $class = $this->container->getUnitClassFactory()->create($classData);

        // Детальная проверка созданного класса делается в отдельных тестах по каждому классу
        // В текущем же случае нам достаточно того, что класс успешно создался. И делаем одну простую проверку
        self::assertEquals($classId, $class->getId());
    }

    /**
     * @throws Exception
     */
    public function testExampleClassDataProviderUnknownId(): void
    {
        $classId = 9999;
        $this->expectException(UnitClassException::class);
        $this->expectExceptionMessage(UnitClassException::UNDEFINED_CLASS_ID . ': ' . $classId);
        $this->getDataProvider()->get(9999);
    }

    /**
     * @return ClassDataProviderInterface
     */
    private function getDataProvider(): ClassDataProviderInterface
    {
        return new ExampleClassDataProvider($this->container);
    }
}
