<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionInterface;
use Battle\Action\DamageAction;
use Battle\Action\HealAction;
use Battle\Chat\Message;
use Battle\Classes\UnitClassInterface;
use Battle\Command;
use Battle\Effect\Effect;
use Battle\Effect\EffectCollection;
use Battle\Exception\UserException;
use Battle\Tools;
use Exception;
use Battle\Exception\ActionCollectionException;

class Unit
{
    public const NEW_ROUND_ADD_CONS = 500;
    public const MAX_CONS = 1000;

    /**
     * @var string - Имя юнита. Это может быть имя персонажа игрока, имя монстра или NPC
     */
    private $name;

    /**
     * @var int - Урон
     */
    private $damage;

    /**
     * @var float - Скорость атаки
     */
    private $attackSpeed;

    /**
     * @var int - Текущее здоровье
     */
    private $life;

    /**
     * @var int - Максимальное здоровье
     */
    private $totalLife;

    /**
     * @var bool - Совершил ли юнит действие в текущем раунде
     */
    private $action = false;

    /**
     * @var bool - Является ли юнит бойцом ближнего боя
     */
    private $melee;

    /**
     * @var int - Концентрация
     */
    private $concentration = 0;

    /**
     * @var EffectCollection
     */
    private $effects;

    /**
     * @var UnitClassInterface
     */
    private $class;

    public function __construct(
        string $name,
        int $damage,
        float $attackSpeed,
        int $life,
        bool $melee,
        UnitClassInterface $class
    )
    {
        $this->name = $name;
        $this->damage = $damage;
        $this->attackSpeed = $attackSpeed;
        $this->life = $this->totalLife = $life;
        $this->melee = $melee;
        $this->class = $class;
        $this->effects = new EffectCollection();
    }

    /**
     * @param Command $enemyCommand
     * @param Command $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     * @throws Exception
     */
    public function getAction(Command $enemyCommand, Command $alliesCommand): ActionCollection
    {
        if ($this->concentration >= self::MAX_CONS) {
            $this->concentration = 0;
            return $this->class->getAbility($this, $enemyCommand, $alliesCommand);
        }

        return $this->getDamageAction($enemyCommand);
    }

    /**
     * @param ActionInterface $action
     * @return string - Сообщение о произошедшем действии
     * @throws UserException
     */
    public function applyAction(ActionInterface $action): string
    {
        if ($action instanceof DamageAction) {
            return $this->applyDamage($action);
        }
        if ($action instanceof HealAction) {
            return $this->applyHeal($action);
        }

        throw new UserException(UserException::UNDEFINED_ACTION);
    }

    public function isAction(): bool
    {
        return $this->action;
    }

    public function isAlive(): bool
    {
        return $this->life > 0;
    }

    public function madeAction(): void
    {
        $this->action = true;
    }

    public function newRound(): void
    {
        $this->action = false;
        $this->concentration += self::NEW_ROUND_ADD_CONS;
    }

    public function isMelee(): bool
    {
        return $this->melee;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function getAttackSpeed(): float
    {
        return $this->attackSpeed;
    }

    public function getLife(): int
    {
        return $this->life;
    }

    public function getTotalLife(): int
    {
        return $this->totalLife;
    }

    /**
     * @param Command $defendingCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     */
    public function getDamageAction(Command $defendingCommand): ActionCollection
    {
        $attacks = $this->calculateAttackSpeed();
        $array = [];

        for ($i = 0; $i < $attacks; $i++) {
            $array[] = new DamageAction($this, $defendingCommand);
        }

        return new ActionCollection($array);
    }

    /**
     * @param Command $alliesCommand
     * @return ActionCollection
     * @throws ActionCollectionException
     */
    public function getHealAction(Command $alliesCommand): ActionCollection
    {
        return new ActionCollection([new HealAction($this, $alliesCommand)]);
    }

    public function getConcentration(): int
    {
        return $this->concentration;
    }

    public function getClass(): UnitClassInterface
    {
        return $this->class;
    }

    public function addEffect(Effect $effect): void
    {
        $this->effects->add($effect);
    }

    /**
     * @return Effect[]
     */
    public function getEffects(): array
    {
        return $this->effects->getEffects();
    }

    private function applyDamage(DamageAction $action): string
    {
        $primordialLife = $this->life;

        $this->life -= $action->getPower();
        if ($this->life < 0) {
            $this->life = 0;
        }

        $action->setFactualPower($primordialLife - $this->life);

        return Message::damage($action);
    }

    private function calculateAttackSpeed(): int
    {
        $result = (int)floor($this->attackSpeed);
        $residue = $this->attackSpeed - $result;
        if (($residue > 0) && ($residue * 100 > Tools::rand(0, 100))) {
            $result++;
        }

        return $result;
    }

    private function applyHeal(HealAction $action): string
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
