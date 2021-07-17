<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Unit\UnitInterface;

interface ActionInterface
{
    public const NO_HANDLE_MESSAGE = '';

    /**
     * Название метода в классе Unit, который будет обрабатывать данное событие
     *
     * @return string
     */
    public function getHandleMethod(): string;

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
     * Было ли действие успешно выполнено
     *
     * Данный метод необходим для таких ситуаций, когда, например, юнит хочет выполнить лечение, но целей для лечения
     * нет - в этом случае, на основании isSuccessHandle() === false, берется базовая атака у юнита, и выполняется она,
     * вместо лечения
     *
     * TODO На удаление, после добавления механики проверки возможности использования до фактического использования
     * TODO события
     *
     * @return bool
     */
    public function isSuccessHandle(): bool;
}
