<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;

interface ActionInterface
{
    public const DAMAGE = 1;
    public const HEAL   = 2;
    public const WAIT   = 3;
    public const SUMMON = 4;
    public const BUFF   = 5;
    public const EFFECT = 6;

    // Применяет событие на себя
    public const TARGET_SELF           = 1;
    // Применяет событие на случайного врага
    public const TARGET_RANDOM_ENEMY   = 2;
    // Применяет событие на самого раненого союзника
    public const TARGET_WOUNDED_ALLIES = 3;

    public const ROLLBACK_METHOD_SUFFIX = 'Revert';

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
     * Возвращает тип выбора цели для применения события
     *
     * @return int
     */
    public function getTypeTarget(): int;

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
     * @throws ActionException
     */
    public function getModifyMethod(): string;

    /**
     * Значение, на которое характеристика была изменена. Используется для отката изменения
     *
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @param int $revertValue
     * @throws ActionException
     */
    public function setRevertValue(int $revertValue): void;

    /**
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @return int
     * @throws ActionException
     */
    public function getRevertValue(): int;

    /**
     * BuffAction создает BuffAction для отката своих изменений характеристик юнита
     *
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @return ActionInterface
     * @throws ActionException
     */
    public function getRevertAction(): ActionInterface;

    /**
     * Возвращает коллекцию применяемых к юниту эффектов
     *
     * Только для EffectAction, при вызове у других Action будет брошено исключение
     *
     * @return EffectCollection
     * @throws ActionException
     */
    public function getEffects(): EffectCollection;

    /**
     * Возвращает названия метода (в классе Scenario) для создания анимации данного Action
     *
     * Такая механика нужна для того, чтобы одни и те же Action, например HealAction по-разному анимировать, в
     * зависимости от того, чем они были сделаны - юнитом или эффектом
     *
     * @return string
     */
    public function getAnimationMethod(): string;

    /**
     * При создании Action, его создатель считается $actionUnit, но, если Action является частью эффекта, то при
     * наложении его на другого юнита, Action будет вызываться уже от его лица. Соответственно нужен метод для изменения
     * $actionUnit в таких ситуациях.
     *
     * @param UnitInterface $unit
     */
    public function changeActionUnit(UnitInterface $unit): void;

    // todo Add getRevertNameAction()
    // todo Add getAlliesCommand
    // todo Add getEnemyCommand
}
