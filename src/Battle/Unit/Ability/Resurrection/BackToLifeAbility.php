<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Resurrection;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;

class BackToLifeAbility extends AbstractAbility
{
    private const NAME       = 'Back to Life';
    private const ICON       = '/images/icons/ability/053.png';
    private const DISPOSABLE = false;

    /**
     * @var ActionCollection
     */
    private $actionCollection;

    public function __construct(UnitInterface $unit)
    {
        parent::__construct($unit, self::DISPOSABLE);
    }

    /**
     * Back to Life - это воскрешение мертвого юнита восстанавливая ему 30% от максимального здоровья
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ActionException
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if ($this->actionCollection === null) {
            $this->actionCollection = new ActionCollection();

            $this->actionCollection->add(new ResurrectionAction(
                $this->unit,
                $enemyCommand,
                $alliesCommand,
                ResurrectionAction::TARGET_DEAD_ALLIES,
                30,
                self::NAME,
                self::ICON
            ));
        }

        return $this->actionCollection;
    }

    /**
     * Способность активируется при полной ярости юнита
     *
     * @param UnitInterface $unit
     */
    public function update(UnitInterface $unit): void
    {
        $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
    }

    /**
     * Способность отмечает свое использование - переходит в неактивный статус и обнуляет ярость у юнита
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->usage = true;
        $this->unit->useRageAbility();
    }

    /**
     * Проверяет, есть ли цель для воскрешения
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     * @throws ActionException
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        foreach ($this->getAction($enemyCommand, $alliesCommand) as $action) {
            if (!$action->canByUsed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return self::ICON;
    }
}
