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

// TODO Добавить механику единственного применения за все время боя

class WillToLiveAbility extends AbstractAbility
{
    private const NAME = 'Will to live';
    private const ICON = '/images/icons/ability/429.png';

    // TODO Сделать специальное сообщение в чате для этой способности вида "$unit умер, но благодаря врожденной способности $name вернулся к жизни"

    /**
     * @var ActionCollection
     */
    private $actionCollection;

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
                self::ICON
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
            $this->ready = !$this->unit->isAlive();
        } else {
            $this->ready = !$this->unit->isAlive() && random_int(0, 100) <= 25;
        }
    }

    /**
     * Способность отмечает свое использование – переходит в неактивный статус
     */
    public function usage(): void
    {
        $this->ready = false;
    }

    /**
     * Проверяет, может ли способность быть применена. Учитывая, что способность активируется при смерти – эта проверка
     * и не нужна. Но на всякий случай делаем.
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        return !$this->unit->isAlive();
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
