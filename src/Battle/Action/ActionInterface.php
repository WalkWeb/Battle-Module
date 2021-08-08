<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Unit\UnitInterface;

interface ActionInterface
{
    /**
     * Название метода в классе Unit, который будет обрабатывать данное событие
     *
     * @return string
     */
    public function getHandleMethod(): string;

    /**
     * Может ли событие быть использовано - проверяет наличие цели для применения
     *
     * @return bool
     */
    public function canByUsed(): bool;

    /**
     * Применение события
     *
     * @return string
     */
    public function handle(): string;

    /**
     * Название события, используется для создания сообщений в чате
     *
     * @return string
     */
    public function getNameAction(): string;

    /**
     * Возвращает юнита совершающего действие
     *
     * @return UnitInterface
     */
    public function getActionUnit(): UnitInterface;

    /**
     * Возвращает юнита к которому применяется действие
     *
     * @return UnitInterface
     * @throws ActionException
     */
    public function getTargetUnit(): UnitInterface;

    /**
     * Возвращает силу действия (например, силу удара или силу лечения)
     *
     * @return mixed
     */
    public function getPower(): int;

    /**
     * Задает фактическую силу действия
     *
     * Например, у юнита осталось 5 здоровья и он получает 50 удара. В этом случае фактический удар будет на 5 здоровья
     *
     * Этот параметр необходим, например, для корректного расчета вампиризма
     *
     * @param int $factualPower
     * @return mixed
     */
    public function setFactualPower(int $factualPower): void;

    /**
     * Возвращает фактическую силу действия
     *
     * @return int
     */
    public function getFactualPower(): int;

    /**
     * Возвращает юнита, который будет призван
     *
     * Актуально для SummonAction, при вызове этого метода у других Action будет брошено исключение
     *
     * @return UnitInterface
     */
    public function getSummonUnit(): UnitInterface;

    /**
     * Возвращает название метода, который будет обрабатывать изменение указанной характеристики
     *
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @return string
     */
    public function getModifyMethod(): string;

    /**
     * Значение, на которое характеристика была изменена. Используется для отката изменения
     *
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @param int $revertValue
     */
    public function setRevertValue(int $revertValue): void;

    /**
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @return int
     */
    public function getRevertValue(): int;
}
