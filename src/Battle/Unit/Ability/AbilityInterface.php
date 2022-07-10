<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;

interface AbilityInterface
{
    // Варианты активации способности
    public const ACTIVATE_CONCENTRATION = 1;
    public const ACTIVATE_RAGE          = 2;
    public const ACTIVATE_LOW_LIFE      = 3;
    public const ACTIVATE_DEAD          = 4;

    /**
     * Название способности
     *
     * Данный метод нужен будет не для самой механики боя (там используется name Action), а, например, для отображения
     * описания способности при наведении на её иконку
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Иконка способности
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Готова ли способность для использования
     *
     * @return bool
     */
    public function isReady(): bool;

    /**
     * Является ли способность одноразовой - т.е. может быть использована только один раз за бой
     *
     * @return bool
     */
    public function isDisposable(): bool;

    /**
     * Может ли способность быть применена. Например, способность лечения может быть готова для использования, но нет
     * цели для лечения - в этом случае способность применять пока не нужно
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool;
    
    /**
     * Возвращает коллекцию действия данной способности
     *
     * TODO Переименовать в getActions()
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;

    /**
     * Сообщает об изменившихся параметрах юнита. В этом методе способность определяет, должна ли она активироваться
     *
     * Некоторые способности активируются с определенным шансом. Чтобы покрыть их тестами используется дополнительный
     * параметр $testMode, который убирает фактор случайности
     *
     * @param UnitInterface $unit
     * @param bool $testMode
     * @throws Exception
     */
    public function update(UnitInterface $unit, bool $testMode = false): void;

    /**
     * Возвращает владельца способности
     *
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface;

    /**
     * Указывает способности, что она была успешно применена и должна сделать соответствующие действия для себя:
     *
     * 1. Перейти в статус ready = false
     * 2. При необходимости изменить характеристику юнита, если способность была связана с заполненностью концентрации
     *    или ярости
     */
    public function usage(): void;

    /**
     * Была ли способность использована хотя бы один раз
     *
     * @return bool
     */
    public function isUsage(): bool;

    /**
     * Тип активации способности. На данный момент этот метод используется в одном месте - для реализации способностей,
     * которые могут применяться после смерти юнита. И такие способности определяются по
     *
     * TypeActivate === self::ACTIVATE_DEAD
     *
     * @return int
     */
    public function getTypeActivate(): int;

    /**
     * Возвращает шанс активации способности. На данный момент применяется только в способностях которые могут
     * активироваться после смерти юнита
     *
     * @return int
     */
    public function getChanceActivate(): int;
}
