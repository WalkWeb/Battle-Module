<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Classes\UnitClassInterface;
use Battle\Result\Chat\Message;
use Exception;

abstract class AbstractUnit implements UnitInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string - Имя юнита. Это может быть имя персонажа игрока, имя монстра или NPC
     */
    protected $name;

    /**
     * @var int - Уровень юнита
     */
    protected $level;

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
     * @var int - Ярость
     */
    protected $rage = 0;

    /**
     * @var UnitClassInterface
     */
    protected $class;

    /**
     * @var Message
     */
    protected $message;

    public function __construct(
        string $id,
        string $name,
        int $level,
        string $avatar,
        int $damage,
        float $attackSpeed,
        int $life,
        int $totalLife,
        bool $melee,
        UnitClassInterface $class,
        Message $message
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->avatar = $avatar;
        $this->damage = $damage;
        $this->attackSpeed = $attackSpeed;
        $this->life = $life;
        $this->totalLife = $totalLife;
        $this->melee = $melee;
        $this->class = $class;
        $this->message = $message;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLevel(): int
    {
        return $this->level;
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

    public function getRage(): int
    {
        return $this->rage;
    }

    public function getClass(): UnitClassInterface
    {
        return $this->class;
    }

    public function newRound(): void
    {
        $this->action = false;
        $this->addConcentration(self::ADD_CON_NEW_ROUND);
    }

    /**
     * Считает фактическое количество атак. Если скорость атаки 1.2, то с 80% вероятностью это будет 1 атака, а с 20%
     * вероятностью - 2 атаки
     *
     * @return int
     * @throws Exception
     */
    protected function calculateAttackSpeed(): int
    {
        $result = (int)floor($this->attackSpeed);
        $residue = $this->attackSpeed - $result;
        if (($residue > 0) && ($residue * 100 > random_int(0, 100))) {
            $result++;
        }

        return $result;
    }

    /**
     * @param int $concentration
     */
    protected function addConcentration(int $concentration): void
    {
        $this->concentration += $concentration;

        if ($this->concentration > self::MAX_CONS) {
            $this->concentration = self::MAX_CONS;
        }
    }

    public function upMaxConcentration(): void
    {
        $this->concentration = self::MAX_CONS;
    }
}
