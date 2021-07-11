<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

interface AbilityInterface
{
    /**
     * Название способности
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
     * Возвращает коллекцию действия данной способности
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection;

    /**
     * Сообщает об изменившихся параметрах юнита. В этом методе способность определяет, должна ли она активироваться
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void;

    /**
     * Возвращает владельца способности
     *
     * @return UnitInterface
     */
    public function getUnit(): UnitInterface;

    /**
     * Указывает способности, что она была успешно применена (и должна перейти в статус ready = false)
     */
    public function setApply(): void;
}
