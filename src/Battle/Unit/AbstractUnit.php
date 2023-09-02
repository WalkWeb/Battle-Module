<?php

declare(strict_types=1);

namespace Battle\Unit;

use Battle\Action\ActionCollection;
use Battle\Action\ActionException;
use Battle\Command\CommandInterface;
use Battle\Unit\Classes\UnitClassInterface;
use Battle\Container\ContainerInterface;
use Battle\Unit\Ability\AbilityCollection;
use Battle\Unit\Ability\AbilityInterface;
use Battle\Unit\Defense\DefenseInterface;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\Race\RaceInterface;
use Exception;

abstract class AbstractUnit implements UnitInterface
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string - Имя юнита. Это может быть имя персонажа игрока, имя монстра или NPC
     */
    protected string $name;

    /**
     * @var int - Уровень юнита
     */
    protected int $level;

    /**
     * @var string - URL к картинке-аватару юнита
     */
    protected string $avatar;

    /**
     * @var int - Текущее здоровье
     */
    protected int $life;

    /**
     * @var int - Максимальное здоровье
     */
    protected int $totalLife;

    /**
     * @var int - Текущая мана
     */
    protected int $mana;

    /**
     * @var int - Максимальное здоровье
     */
    protected int $totalMana;

    /**
     * @var bool - Совершил ли юнит действие в текущем раунде
     */
    protected bool $action = false;

    /**
     * @var bool - Является ли юнит бойцом ближнего боя
     */
    protected bool $melee;

    /**
     * @var int - Номер команды: 1 - левая команда, 2 - правая команда
     */
    protected int $command;

    /**
     * @var int - Концентрация
     */
    protected int $concentration = 0;

    /**
     * @var int - Ярость
     */
    protected int $rage = 0;

    /**
     * @var int - Множитель получаемой концентрации. Указывается в процентах (20 => +20%, -30 => -30%)
     */
    protected int $addConcentrationMultiplier;

    /**
     * @var int - Множитель хитрости. Указывается в процентах (20 => +20%, -30 => -30%)
     */
    protected int $cunningMultiplier;

    /**
     * @var int - Множитель получаемой ярости. Указывается в процентах (20 => +20%, -30 => -30%)
     */
    protected int $addRageMultiplier;

    /**
     * @var UnitClassInterface|null
     */
    protected ?UnitClassInterface $class = null;

    /**
     * @var RaceInterface
     */
    protected RaceInterface $race;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var AbilityCollection
     */
    protected AbilityCollection $abilities;

    /**
     * @var EffectCollection
     */
    protected EffectCollection $effects;

    /**
     * @var OffenseInterface
     */
    protected OffenseInterface $offense;

    /**
     * @var DefenseInterface
     */
    protected DefenseInterface $defense;

    /**
     * @var UnitCollection
     */
    protected UnitCollection $lastTargets;

    /**
     * @param string $id
     * @param string $name
     * @param int $level
     * @param string $avatar
     * @param int $life
     * @param int $totalLife
     * @param int $mana
     * @param int $totalMana
     * @param bool $melee
     * @param int $command
     * @param int $addConcentrationMultiplier
     * @param int $cunningMultiplier
     * @param int $addRageMultiplier
     * @param OffenseInterface $offense
     * @param DefenseInterface $defense
     * @param RaceInterface $race
     * @param ContainerInterface $container
     * @param UnitClassInterface|null $class
     * @param EffectCollection|null $effects
     * @param UnitCollection|null $lastTargets
     * @throws UnitException
     */
    public function __construct(
        string $id,
        string $name,
        int $level,
        string $avatar,
        int $life,
        int $totalLife,
        int $mana,
        int $totalMana,
        bool $melee,
        int $command,
        int $addConcentrationMultiplier,
        int $cunningMultiplier,
        int $addRageMultiplier,
        OffenseInterface $offense,
        DefenseInterface $defense,
        RaceInterface $race,
        ContainerInterface $container,
        ?UnitClassInterface $class = null,
        ?EffectCollection $effects = null,
        ?UnitCollection $lastTargets = null
    )
    {
        $this->validateCommand($command);
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->avatar = $avatar;
        $this->life = $life;
        $this->totalLife = $totalLife;
        $this->mana = $mana;
        $this->totalMana = $totalMana;
        $this->melee = $melee;
        $this->command = $command;
        $this->addConcentrationMultiplier = $addConcentrationMultiplier;
        $this->cunningMultiplier = $cunningMultiplier;
        $this->addRageMultiplier = $addRageMultiplier;
        $this->offense = $offense;
        $this->defense = $defense;
        $this->race = $race;
        $this->container = $container;
        $this->class = $class;
        $this->abilities = new AbilityCollection($this->container->isTestMode());
        $this->effects = $effects ?? new EffectCollection($this);
        $this->lastTargets = $lastTargets ?? new UnitCollection();
        $this->addClassAbilities();
        $this->addRaceAbilities();
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

    public function getLife(): int
    {
        return $this->life;
    }

    public function getTotalLife(): int
    {
        return $this->totalLife;
    }

    public function getMana(): int
    {
        return $this->mana;
    }

    public function getTotalMana(): int
    {
        return $this->totalMana;
    }

    public function getConcentration(): int
    {
        return $this->concentration;
    }

    public function getCunning(): int
    {
        return (int)(self::BASE_CUNNING * ((100 + $this->cunningMultiplier) / 100));
    }

    public function getCommand(): int
    {
        return $this->command;
    }

    public function getOffense(): OffenseInterface
    {
        return $this->offense;
    }

    public function getDefense(): DefenseInterface
    {
        return $this->defense;
    }

    public function getRage(): int
    {
        return $this->rage;
    }

    public function getAddConcentrationMultiplier(): int
    {
        return $this->addConcentrationMultiplier;
    }

    /**
     * @param int $addConcentrationMultiplier
     * @throws UnitException
     */
    public function setAddConcentrationMultiplier(int $addConcentrationMultiplier): void
    {
        if ($addConcentrationMultiplier < self::MIN_RESOURCE_MULTIPLIER) {
            throw new UnitException(
                UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
            );
        }

        if ($addConcentrationMultiplier > self::MAX_RESOURCE_MULTIPLIER) {
            throw new UnitException(
                UnitException::INCORRECT_ADD_CONC_MULTIPLIER_VALUE . UnitInterface::MIN_RESOURCE_MULTIPLIER . ' - ' . UnitInterface::MAX_RESOURCE_MULTIPLIER
            );
        }

        $this->addConcentrationMultiplier = $addConcentrationMultiplier;
    }

    public function getCunningMultiplier(): int
    {
        return $this->cunningMultiplier;
    }

    public function getAddRageMultiplier(): int
    {
        return $this->addRageMultiplier;
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

    /**
     * @throws Exception
     */
    public function newRound(): void
    {
        $this->action = false;
        $this->addConcentration(self::ADD_CON_NEW_ROUND);
        $this->addRage(self::ADD_RAGE_NEW_ROUND);
        $this->abilities->newRound($this);
        $this->clearLastTarget();
    }

    /**
     * @throws Exception
     */
    public function useConcentrationAbility(): void
    {
        $this->concentration = 0;
        $this->abilities->update($this);
    }

    /**
     * @throws Exception
     */
    public function useRageAbility(): void
    {
        $this->rage = 0;
        $this->abilities->update($this);
    }

    public function getEffects(): EffectCollection
    {
        return $this->effects;
    }

    public function getBeforeActions(): ActionCollection
    {
        return $this->effects->newRound();
    }

    /**
     * @return ActionCollection
     * @throws ActionException
     */
    public function getAfterActions(): ActionCollection
    {
        return $this->effects->nextRound();
    }

    public function isParalysis(): bool
    {
        return $this->effects->existParalysis();
    }

    public function getLastTargets(): UnitCollection
    {
        return $this->lastTargets;
    }

    /**
     * @param UnitInterface $target
     * @throws UnitException
     */
    public function addLastTarget(UnitInterface $target): void
    {
        $this->lastTargets->addIfMissing($target);
    }

    public function clearLastTarget(): void
    {
        $this->lastTargets = new UnitCollection();
    }

    /**
     * Считает фактическое количество атак/заклинаний которые совершит юнит.
     *
     * Если скорость атаки 1.2, то с 80% вероятностью это будет 1 атака, а с 20% вероятностью - 2 атаки
     *
     * @return int
     * @throws Exception
     */
    protected function calculateHits(): int
    {
        if ($this->offense->getDamageType() === OffenseInterface::TYPE_ATTACK) {
            $speed = $this->offense->getAttackSpeed();
        } else {
            $speed = $this->offense->getCastSpeed();
        }

        $result = (int)floor($speed);
        $residue = $speed - $result;
        if (($residue > 0) && ($residue * 10000 > random_int(0, 10000))) {
            $result++;
        }

        return $result;
    }

    /**
     * @param int $concentration
     * @throws Exception
     */
    protected function addConcentration(int $concentration): void
    {
        $this->concentration += (int)($concentration * ((100 + $this->addConcentrationMultiplier) / 100));

        if ($this->concentration > self::MAX_CONCENTRATION) {
            $this->concentration = self::MAX_CONCENTRATION;
        }

        $this->abilities->update($this);
    }

    /**
     * @param int $rage
     * @throws Exception
     */
    protected function addRage(int $rage): void
    {
        $this->rage += (int)($rage * ((100 + $this->addRageMultiplier) / 100));

        if ($this->rage > self::MAX_RAGE) {
            $this->rage = self::MAX_RAGE;
        }

        $this->abilities->update($this);
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

    /**
     * Добавляет классовые способности
     */
    private function addClassAbilities(): void
    {
        if ($this->class) {
            foreach ($this->class->getAbilities($this) as $ability) {
                $this->abilities->add($ability);
            }
        }
    }

    /**
     * Добавляет расовые способности
     */
    private function addRaceAbilities(): void
    {
        foreach ($this->race->getAbilities($this) as $ability) {
            $this->abilities->add($ability);
        }
    }
}
