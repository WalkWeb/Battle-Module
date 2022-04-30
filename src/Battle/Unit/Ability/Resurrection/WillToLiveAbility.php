<?php

declare(strict_types=1);

namespace Battle\Unit\Ability\Resurrection;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Action\ResurrectionAction;
use Battle\Command\CommandInterface;
use Battle\Unit\Ability\AbstractAbility;
use Battle\Unit\UnitInterface;
use Exception;

class WillToLiveAbility extends AbstractAbility
{
    private const NAME            = 'Will to live';
    private const ICON            = '/images/icons/ability/429.png';
    private const MESSAGE_METHOD  = 'selfRaceResurrected';
    private const DISPOSABLE      = true;
    private const ACTIVATE_CHANCE = 25;

    /**
     * @var ActionCollection
     */
    private $actionCollection;

    public function __construct(UnitInterface $unit)
    {
        parent::__construct($unit, self::DISPOSABLE);
    }

    /**
     * Will to live – врожденная способность расы людей, позволяет с 25% шансом при смерти воскреснуть с 50% здоровья
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
                ResurrectionAction::TARGET_SELF,
                50,
                self::NAME,
                self::ICON,
                self::MESSAGE_METHOD
            ));
        }

        return $this->actionCollection;
    }

    /**
     * Способность активируется при смерти юнита с 25% вероятностью
     *
     * В тестовом режиме способность активируется со 100% шансом
     *
     * @param UnitInterface $unit
     * @param bool $testMode
     * @throws Exception
     */
    public function update(UnitInterface $unit, bool $testMode = false): void
    {
        if ($testMode) {
            $this->ready = !$this->usage && !$this->unit->isAlive();
        } else {
            $this->ready = !$this->usage && !$this->unit->isAlive() && random_int(0, 100) <= self::ACTIVATE_CHANCE;
        }
    }

    /**
     * Способность отмечает свое использование – переходит в неактивный статус
     */
    public function usage(): void
    {
        $this->ready = false;
        $this->usage = true;
    }

    /**
     * Проверяет, может ли способность быть применена. Достаточно проверить, что способность не применялась ранее -
     * если не применялась - значит может.
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        return !$this->usage;
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
