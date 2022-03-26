<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\UnitCollection;
use Battle\Unit\UnitInterface;

interface ActionInterface
{
    public const DAMAGE       = 1;
    public const HEAL         = 2;
    public const WAIT         = 3;
    public const SUMMON       = 4;
    public const BUFF         = 5;
    public const EFFECT       = 6;
    public const RESURRECTION = 7;

    // Применяет событие на себя
    public const TARGET_SELF                  = 1;
    // Применяет событие на случайного врага
    public const TARGET_RANDOM_ENEMY          = 2;
    // Применяет событие на самого раненого союзника
    public const TARGET_WOUNDED_ALLIES        = 3;
    // Применяет событие (эффект) на случайного противника, не имеющего данного эффекта
    public const TARGET_EFFECT_ENEMY          = 4;
    // Применяет событие (эффект) на случайного дружеского юнита, не имеющего данного эффекта
    public const TARGET_EFFECT_ALLIES         = 5;
    // Применяет эффект на самого раненого союзника не имеющего данного эффекта
    public const TARGET_WOUNDED_ALLIES_EFFECT = 6;
    // Применяет событие-воскрешение на случайного мертвого союзного юнита
    public const TARGET_DEAD_ALLIES           = 7;
    // Применяет событие на всех живых противников (для массовой атаки по всем врагам)
    public const TARGET_ALL_ENEMY             = 8;
    // Применяет событие на всех раненых (но живых) союзников (для массового лечения по своим)
    public const TARGET_ALL_WOUNDED_ALLIES    = 9;

    public const ROLLBACK_METHOD_SUFFIX = 'Revert';

    // Пропуск создания сообщения в чате
    public const SKIP_MESSAGE_METHOD    = 'skip';

    /**
     * Название метода в классе Unit, который будет обрабатывать данное событие
     *
     * @return string
     */
    public function getHandleMethod(): string;

    /**
     * Может ли событие быть использовано - проверяет наличие цели для применения
     *
     * Важно: в некоторых Action в canByUsed() определяется цель для применения Action, и если вызвать сразу handle(),
     * не вызвав перед этим canByUsed() будет исключение. С одной стороны, это неочевидная, а следовательно плохая
     * логика, с другой стороны, со временем, приходит понимание, что такой подход допустим: потому что проверка на
     * canByUsed() должна быть обязательной перед каждым вызовом Action->handle(), соответственно допустимо, что Action
     * упадет из-за отсутствия цели для применения, если проверка на canByUsed() не была сделана.
     *
     * @return bool
     */
    public function canByUsed(): bool;

    /**
     * Применение события. При этом само событие определит, на какого юнита оно должно примениться.
     *
     * Корректный вызов:
     *
     * if ($action->canByUsed()) {
     *     $action->handle();
     * }
     *
     * TODO В конкретных реализациях много дублирующегося кода - можно улучшить
     */
    public function handle(): void;

    /**
     * Название события, используется для создания сообщений в чате
     *
     * @return string
     */
    public function getNameAction(): string;

    /**
     * Возвращает юнита создавшего данный Action
     *
     * Необходимо для корректного расчета статистики от эффектов. Например, чтобы лечение от эффекта засчитывалось не
     * тому юниту, на которого эффект наложен, а тому, кто этот эффект создал.
     *
     * В Action которые не относятся к эффектам, CreatorUnit будет равен ActionUnit
     *
     * @return UnitInterface
     */
    public function getCreatorUnit(): UnitInterface;

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
     * @return UnitCollection
     * @throws ActionException
     */
    public function getTargetUnits(): UnitCollection;

    /**
     * Возвращает силу действия (например, силу удара или силу лечения)
     *
     * В Action не имеющих силу действия (например, призыв существ) вернет 0
     *
     * @return mixed
     */
    public function getPower(): int;

    /**
     * Задает фактическую силу действия, суммарно по всем юнитам
     *
     * Например, у юнита осталось 5 здоровья и он получает 50 удара. В этом случае фактический удар будет на 5 здоровья
     *
     * Этот параметр необходим, например, для корректного расчета вампиризма
     *
     * @param string $unitId
     * @param int $factualPower
     * @return mixed
     */
    public function addFactualPower(string $unitId, int $factualPower): void;

    /**
     * Сбрасывает factualPower у Action
     *
     * Необходим для эффектов, чтобы на каждом раунде отображалась сила эффекта именно в этом раунде, а не суммарная за
     * все раунды
     */
    public function clearFactualPower(): void;

    /**
     * Возвращает фактическую силу действия
     *
     * @return int
     */
    public function getFactualPower(): int;

    /**
     * Возвращает силу действия по конкретному юниту
     *
     * @param UnitInterface $unit
     * @return int
     * @throws ActionException
     */
    public function getFactualPowerByUnit(UnitInterface $unit): int;

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
     * @param int|float $revertValue
     * @throws ActionException
     */
    public function setRevertValue($revertValue): void;

    /**
     * Используется только в BuffAction, при вызове у других Action будет брошено исключение
     *
     * @return int|float
     * @throws ActionException
     */
    public function getRevertValue();

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
     * Возвращает эффект события
     *
     * Только для EffectAction, при вызове у других Action будет брошено исключение
     *
     * Так как один и тот же EffectAction может применяться к нескольким юнитам, чтобы каждый из них имел свой
     * уникальный эффект - необходимо клонировать возвращаемый объект, т.е. делать:
     *
     * return clone $this->effect;
     *
     * @return EffectInterface
     * @throws ActionException
     */
    public function getEffect(): EffectInterface;

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
     * Возвращает названия метода (в классе Chat) для создания сообщения для чата данного Action
     *
     * Такая механика нужна для того, чтобы у одних и тех же Action создавать разные сообщения для чата, например когда
     * DamageAction - это урон от юнита по другому юниту, то сообщение будет:
     * "Titan атаковал Zombie на 32 урона"
     *
     * А если DamageAction - это урон от эффекта по юниту, то сообщение будет:
     * "Titan получил урон на 10 здоровья, от эффекта Отравление"
     *
     * @return string
     */
    public function getMessageMethod(): string;

    /**
     * При создании Action, его создатель считается $actionUnit, но, если Action является частью эффекта, то при
     * наложении его на другого юнита, Action будет вызываться уже от его лица. Соответственно нужен метод для изменения
     * $actionUnit в таких ситуациях.
     *
     * @param UnitInterface $unit
     */
    public function changeActionUnit(UnitInterface $unit): void;

    /**
     * Иконка события. Если это простая базовая атака - будет возвращена пустая строка. Во всех остальных случаях
     * используется способность, которая имеет свою иконку.
     *
     * Используется для отображения иконок способностей в чате
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Было ли событие заблокировано указанным юнитом
     *
     * По умолчанию возвращает всегда false, true может быть только в DamageAction, если противник заблокировал урон
     *
     * @param UnitInterface $unit
     * @return bool
     */
    public function isBlocked(UnitInterface $unit): bool;

    /**
     * Указывает, что событие было заблокировано данным юнитом
     *
     * По умолчанию вызов метода возвращает Exception, успешно обработан данный метод будет только в DamageAction
     *
     * @param UnitInterface $unit
     */
    public function blocked(UnitInterface $unit): void;

    /**
     * Возвращает blockIgnore Action, актуально только для DamageAction, при вызове у прочих Action будет получен
     * Exception
     *
     * По-умолчанию используется blockIgnore атакующего юнита, но если это урон от эффекта - игнорирование будет 100,
     * независимо от blockIgnore атакующего юнита, т.к. урон от эффекта не может быть заблокирован
     *
     * @return int
     * @throws ActionException
     */
    public function getBlockIgnore(): int;
}
