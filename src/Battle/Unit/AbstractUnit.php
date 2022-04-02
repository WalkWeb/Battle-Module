<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Command\CommandInterface;
use Battle\Container\ContainerException;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Race\RaceInterface;
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
     * @var int - Меткость
     */
    protected $accuracy;

    /**
     * @var int - Защита
     */
    protected $defense;

    /**
     * @var int - Шанс блока вражеских атак
     */
    protected $block;

    /**
     * @var int - Игнорирование блока цели
     */
    protected $blockIgnore;

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
     * @var int - Номер команды: 1 - левая команда, 2 - правая команда
     */
    protected $command;

    /**
     * @var int - Концентрация
     */
    protected $concentration = 0;

    /**
     * @var int - Ярость
     */
    protected $rage = 0;

    /**
     * @var UnitClassInterface|null
     */
    protected $class;

    /**
     * @var RaceInterface
     */
    protected $race;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AbilityCollection
     */
    protected $abilities;

    /**
     * @var EffectCollection
     */
    protected $effects;

    /**
     * @param string $id
     * @param string $name
     * @param int $level
     * @param string $avatar
     * @param int $damage
     * @param float $attackSpeed
     * @param int $accuracy
     * @param int $defense
     * @param int $block
     * @param int $blockIgnore
     * @param int $life
     * @param int $totalLife
     * @param bool $melee
     * @param int $command
     * @param RaceInterface $race
     * @param ContainerInterface $container
     * @param UnitClassInterface|null $class
     * @param EffectCollection|null $effects
     * @throws UnitException
     */
    public function __construct(
        string $id,
        string $name,
        int $level,
        string $avatar,
        int $damage,
        float $attackSpeed,
        int $accuracy,
        int $defense,
        int $block,
        int $blockIgnore,
        int $life,
        int $totalLife,
        bool $melee,
        int $command,
        RaceInterface $race,
        ContainerInterface $container,
        ?UnitClassInterface $class = null,
        ?EffectCollection $effects = null
    )
    {
        $this->validateCommand($command);
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->avatar = $avatar;
        $this->damage = $damage;
        $this->attackSpeed = $attackSpeed;
        $this->accuracy = $accuracy;
        $this->defense = $defense;
        $this->block = $block;
        $this->blockIgnore = $blockIgnore;
        $this->life = $life;
        $this->totalLife = $totalLife;
        $this->melee = $melee;
        $this->command = $command;
        $this->race = $race;
        $this->container = $container;
        $this->class = $class;
        $this->abilities = $class ? $class->getAbilities($this) : new AbilityCollection();
        $this->effects = $effects ?? new EffectCollection($this);
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

    public function getDPS(): float
    {
        return round($this->damage * $this->attackSpeed, 1);
    }

    public function getAttackSpeed(): float
    {
        return $this->attackSpeed;
    }

    public function getAccuracy(): int
    {
        return $this->accuracy;
    }

    public function getDefense(): int
    {
        return $this->defense;
    }

    public function getBlock(): int
    {
        return $this->block;
    }

    public function getBlockIgnore(): int
    {
        return $this->blockIgnore;
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

    public function getCommand(): int
    {
        return $this->command;
    }

    public function getRage(): int
    {
        return $this->rage;
    }

    public function getClass(): ?UnitClassInterface
    {
        return $this->class;
    }

    public function getRace(): RaceInterface
    {
        return $this->race;
    }

    public function getIcon(): string
    {
        return $this->class ? $this->class->getSmallIcon() : $this->race->getIcon();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getAbilities(): AbilityCollection
    {
        return $this->abilities;
    }

    public function getOnNewRoundActions(): ActionCollection
    {
        return $this->effects->newRound();
    }

    /**
     * @throws ActionException
     * @throws ContainerException
     */
    public function newRound(): void
    {
        $this->action = false;
        $this->addConcentration(self::ADD_CON_NEW_ROUND);
        $this->addRage(self::ADD_RAGE_NEW_ROUND);

        // События, которые должны примениться при отмене эффекта
        $effectActions = $this->effects->nextRound();

        foreach ($effectActions as $action) {
            if ($action->canByUsed()) {
                $action->handle();
                $this->container->getScenario()->addAnimation($action, $this->container->getStatistic());
            }
        }
    }

    public function useConcentrationAbility(): void
    {
        $this->concentration = 0;
        $this->abilities->update($this);
    }

    public function useRageAbility(): void
    {
        $this->rage = 0;
        $this->abilities->update($this);
    }

    public function getEffects(): EffectCollection
    {
        return $this->effects;
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

        $this->abilities->update($this);
    }

    /**
     * @param int $rage
     */
    protected function addRage(int $rage): void
    {
        $this->rage += $rage;

        if ($this->rage > self::MAX_RAGE) {
            $this->rage = self::MAX_RAGE;
        }
    }

    /**
     * Проверяет способность, готовой к использованию
     *
     * Способность применяется если:
     * 1. Есть способность доступная для использования
     * 2. Способность может быть использована (например, есть цель для лечения)
     *
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return AbilityInterface|null
     */
    protected function getAbility(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ?AbilityInterface
    {
        foreach ($this->abilities as $ability) {
            if ($ability->isReady() && $ability->canByUsed($enemyCommand, $alliesCommand)) {
                return $ability;
            }
        }

        return null;
    }

    /**
     * Проверяет корректность номера активной команды - может иметь значение только 1 или 2
     *
     * @param int $command
     * @throws UnitException
     */
    private function validateCommand(int $command): void
    {
        if ($command !== 1 && $command !== 2) {
            throw new UnitException(UnitException::INCORRECT_COMMAND);
        }
    }
}
