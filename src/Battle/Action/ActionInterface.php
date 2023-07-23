<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectInterface;
use Battle\Unit\Offense\OffenseInterface;
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
    public const PARALYSIS    = 8;
    public const MANA_RESTORE = 9;

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
    // Применяет событие на последние живые цели в этом раунде
    public const TARGET_LAST_ALIVE_TARGETS    = 10;
    // Применяет событие  на раненого себя
    public const TARGET_WOUNDED_SELF          = 11;

    // TODO Подумать над отдельным таргетом для целей которые или ранены или имеют неполную ману

    public const ROLLBACK_METHOD_SUFFIX = 'Revert';

    // Пропуск создания сообщения в чате
    public const SKIP_MESSAGE_METHOD    = 'skip';

    // Пропуск создания анимации
    public const SKIP_ANIMATION_METHOD  = 'skip';

    // Максимальное уменьшение характеристики юнита - 90%
    public const MIN_MULTIPLIER         = 10;

    /**
     * Название метода в классе Unit, который будет обрабатывать данное событие
     *
     * @return string
     */
    public function getHandleMethod(): string;

    /**
     * Может ли событие быть использовано - проверяет наличие цели для применения
     *
     * Важно: метод не должен делать какие-то другие проверки: жив ли юнит, не ходил ли он в этом ходу и прочее. Этот
     * метод отвечает только за возможность применить указанный Action
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
     * Также выполнение события может породить другие события, например - рефлект урона, или какие-то дополнительные
     * эффекты от оружия. Они возвращаются в ActionCollection.
     *
     * TODO В конкретных реализациях много дублирующегося кода - можно улучшить
     */
    public function handle(): ActionCollection;

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
     * Возвращает цели к которым применяется действие
     *
     * @return UnitCollection
     * @throws ActionException
     */
    public function getTargetUnits(): UnitCollection;

    /**
     * Возвращает суммарную силу действия Action. Например, HealAction вернет силу лечения, а BuffAction - силу
     * изменения характеристики
     *
     * @return mixed
     * @throws ActionException
     */
    public function getPower(): int;

    /**
     * Если это DamageAction - то вернет параметры урона.
     *
     * Во всех остальных Action, при обращении к этому методу будет брошено исключение
     *
     * @return OffenseInterface
     * @throws ActionException
     */
    public function getOffense(): OffenseInterface;

    /**
     * Задает фактическую силу действия, по указанному юниту
     *
     * Этот параметр необходим для статистики и, например, для корректного расчета вампиризма - когда лечение
     * рассчитывает от фактически нанесенного урона, а не от самого показателя удара

     * Например, у юнита осталось 5 здоровья и он получает 50 удара. В этом случае фактический удар будет на 5 здоровья
     *
     * По умолчанию, в Action у которых нет никакой силы (например, призыв существ), возвращает 0
     *
     * @param UnitInterface $unit
     * @param int $factualPower
     * @return mixed
     */
    public function addFactualPower(UnitInterface $unit, int $factualPower): void;

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
     * Уклонился ли указанный юнит от данного события
     *
     * По умолчанию возвращает всегда false, true может быть только в DamageAction, если противник уклонился от удара
     *
     * @param UnitInterface $unit
     * @return bool
     */
    public function isEvaded(UnitInterface $unit): bool;

    /**
     * Указывает, что юнит уклонился от данного события
     *
     * По умолчанию вызов метода возвращает Exception, успешно обработан данный метод будет только в DamageAction
     *
     * @param UnitInterface $unit
     */
    public function dodged(UnitInterface $unit): void;

    /**
     * Показывает, можно ли заблокировать или увернуться от данного события (актуально только для DamageAction)
     *
     * Например, если это урон от эффекта, то его нельзя избежать. Соответственно для обычных ударов вернет true, для
     * урона от эффектов - false
     *
     * @return bool
     */
    public function isCanBeAvoided(): bool;

    /**
     * Будет ли удар критическим. Рассчитывается сразу при создании DamageAction в конструкторе
     *
     * Критический удар является общим для всех целей. Т.е. если юнит атакует 4-х противников, то критический удар либо
     * не будет нанесен ни по кому, либо будет нанесен по всем сразу.
     *
     * Работает только для DamageAction, в остальных Action будет исключение
     *
     * @return bool
     */
    public function isCriticalDamage(): bool;

    /**
     * Возвращает восстановленное здоровье от вампиризма. По умолчанию 0
     *
     * Работает только для DamageAction, в остальных Action будет исключение
     *
     * @return int
     * @throws ActionException
     */
    public function getRestoreLifeFromVampirism(): int;

    /**
     * Возвращает восстановленную ману от магического вампиризма. По умолчанию 0
     *
     * Работает только для DamageAction, в остальных Action будет исключение
     *
     * @return int
     */
    public function getRestoreManaFromMagicVampirism(): int;

    /**
     * Возвращает команду союзников (с точки зрения юнита совершающего событие)
     *
     * @return CommandInterface
     */
    public function getAlliesCommand(): CommandInterface;

    /**
     * Возвращает команду врагов (с точки зрения юнита совершающего событие)
     *
     * @return CommandInterface
     */
    public function getEnemyCommand(): CommandInterface;

    /**
     * Необходимо ли отслеживать цель (Unit->lastTargets)
     *
     * По умолчанию true, для событий от эффектов которые применяются каждый ход, например лечения/урона/паралича, нужно
     * вручную указывать false
     *
     * TODO Можно сделать автоматическое добавление "'target_tracking'  => false" событиям, которые создаются в стадии
     * TODO "on_next_round_actions"
     *
     * @return bool
     */
    public function isTargetTracking(): bool;
}
