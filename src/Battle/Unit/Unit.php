<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Chat\Message;
use Battle\Command\CommandInterface;
use Battle\Effect\Effect;
use Exception;
use Battle\Exception\ActionCollectionException;

class Unit extends AbstractUnit
{
    /**
     * Возвращает абстрактное действие (действия) от юнита в его ходе.
     *
     * В нашей логике юнит сам решает, какое действие ему совершать - это может быть как обычная атака, так и какая-то
     * способность, зависящая от класса.
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        if ($this->concentration >= self::MAX_CONS) {
            $this->concentration = 0;
            return $this->class->getAbility($this, $enemyCommand, $alliesCommand);
        }

        return $this->getDamageAction($enemyCommand, $alliesCommand);
    }

    /**
     * TODO Заменить видимость с public на private
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     * @throws Exception
     */
    public function getDamageAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $attacks = $this->calculateAttackSpeed();
        $array = [];

        for ($i = 0; $i < $attacks; $i++) {
            $array[] = new DamageAction($this, $enemyCommand, $alliesCommand);
        }

        return new ActionCollection($array);
    }

    /**
     * TODO Метод на удаление
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     */
    public function getHealAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        return new ActionCollection([new HealAction($this, $enemyCommand, $alliesCommand)]);
    }

    public function newRound(): void
    {
        $this->action = false;
        $this->concentration += self::NEW_ROUND_ADD_CONS;
    }

    /**
     * TODO Переделать public в private, эффекты должны добавляться только через applyAction()
     *
     * @param Effect $effect
     */
    public function addEffect(Effect $effect): void
    {
        $this->effects->add($effect);
    }

    /**
     * Считает фактическое количество атак. Если скорость атаки 1.2, то с 80% вероятностью это будет 1 атака, а с 20%
     * вероятностью - 2 атаки
     *
     * @return int
     * @throws Exception
     */
    private function calculateAttackSpeed(): int
    {
        $result = (int)floor($this->attackSpeed);
        $residue = $this->attackSpeed - $result;
        if (($residue > 0) && ($residue * 100 > random_int(0, 100))) {
            $result++;
        }

        return $result;
    }

    // ---------------------------------------------- HANDLE ACTION ----------------------------------------------------

    /**
     * Принимает и обрабатывает абстрактное действие от другого юнита.
     *
     * @param ActionInterface $action
     * @return string - Сообщение о произошедшем действии
     * @throws UnitException
     */
    public function applyAction(ActionInterface $action): string
    {
        // TODO Метод обрабатывающий Action брать из самого Action, тем самым избавляемся от if if if

        if ($action instanceof DamageAction) {
            return $this->applyDamageAction($action);
        }
        if ($action instanceof HealAction) {
            return $this->applyHealAction($action);
        }

        throw new UnitException(UnitException::UNDEFINED_ACTION);
    }

    private function applyDamageAction(DamageAction $action): string
    {
        $primordialLife = $this->life;

        $this->life -= $action->getPower();
        if ($this->life < 0) {
            $this->life = 0;
        }

        $action->setFactualPower($primordialLife - $this->life);

        return Message::damage($action);
    }

    private function applyHealAction(HealAction $action): string
    {
        $primordialLife = $this->life;

        $this->life += $action->getPower();
        if ($this->life > $this->totalLife) {
            $this->life = $this->totalLife;
        }

        $action->setFactualPower($this->life - $primordialLife);

        return Message::heal($action);
    }
}
