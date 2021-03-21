<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Classes\UnitClassInterface;
use Battle\Effect\EffectCollection;

abstract class AbstractUnit implements UnitInterface
{
    /**
     * @var string - Имя юнита. Это может быть имя персонажа игрока, имя монстра или NPC
     */
    protected $name;

    /**
     * @var string - URL к картинке-аватару юнита
     */
    protected $avatar;

    /**
     * @var int - Урон
     */
    protected $damage;

    /**
     * @var float - Скорость атаки
     */
    protected $attackSpeed;

    /**
     * @var int - Текущее здоровье
     */
    protected $life;

    /**
     * @var int - Максимальное здоровье
     */
    protected $totalLife;

    /**
     * @var bool - Совершил ли юнит действие в текущем раунде
     */
    protected $action = false;

    /**
     * @var bool - Является ли юнит бойцом ближнего боя
     */
    protected $melee;

    /**
     * @var int - Концентрация
     */
    protected $concentration = 0;

    /**
     * @var EffectCollection
     */
    protected $effects;

    /**
     * @var UnitClassInterface
     */
    protected $class;

    public function __construct(
        string $name,
        string $avatar,
        int $damage,
        float $attackSpeed,
        int $life,
        bool $melee,
        UnitClassInterface $class
    )
    {
        $this->name = $name;
        $this->avatar = $avatar;
        $this->damage = $damage;
        $this->attackSpeed = $attackSpeed;
        $this->life = $this->totalLife = $life;
        $this->melee = $melee;
        $this->class = $class;
        $this->effects = new EffectCollection();
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

    public function isMelee(): bool
    {
        return $this->melee;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
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

    public function getConcentration(): int
    {
        return $this->concentration;
    }

    public function getClass(): UnitClassInterface
    {
        return $this->class;
    }

    public function getEffects(): EffectCollection
    {
        return $this->effects;
    }
}
