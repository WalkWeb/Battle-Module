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
use Battle\Unit\Defense\Defense;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\Offense\Offense;
use Battle\Unit\Race\RaceInterface;
use Exception;
use Throwable;

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
     * @var Offense
     */
    protected $offense;

    /**
     * @var Defense
     */
    protected $defense;

    /**
     * @param string $id
     * @param string $name
     * @param int $level
     * @param string $avatar
     * @param int $life
     * @param int $totalLife
     * @param bool $melee
     * @param int $command
     * @param Offense $offense
     * @param Defense $defense
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
        int $life,
        int $totalLife,
        bool $melee,
        int $command,
        Offense $offense,
        Defense $defense,
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
        $this->life = $life;
        $this->totalLife = $totalLife;
        $this->melee = $melee;
        $this->command = $command;
        $this->offense = $offense;
        $this->defense = $defense;
        $this->race = $race;
        $this->container = $container;
        $this->class = $class;
        $this->abilities = new AbilityCollection($this->container->isTestMode());
        $this->effects = $effects ?? new EffectCollection($this);
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

    public function getConcentration(): int
    {
        return $this->concentration;
    }

    public function getCommand(): int
    {
        return $this->command;
    }

    public function getOffense(): Offense
    {
        return $this->offense;
    }

    public function getDefense(): Defense
    {
        return $this->defense;
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
     * @throws Exception
     */
    public function newRound(): void
    {
        $this->action = false;
        $this->addConcentration(self::ADD_CON_NEW_ROUND);
        $this->addRage(self::ADD_RAGE_NEW_ROUND);
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

    /**
     * @return ActionCollection
     */
    public function getBeforeActions(): ActionCollection
    {
        return $this->getOnNewRoundActions();
    }

    /**
     * @return ActionCollection
     * @throws ActionException
     */
    public function getAfterActions(): ActionCollection
    {
        return $this->effects->nextRound();
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
        $attackSpeed = $this->offense->getAttackSpeed();

        $result = (int)floor($attackSpeed);
        $residue = $attackSpeed - $result;
        if (($residue > 0) && ($residue * 100 > random_int(0, 100))) {
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
        $this->concentration += $concentration;

        if ($this->concentration > self::MAX_CONCENTRATION) {
            $this->concentration = self::MAX_CONCENTRATION;
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
     * Создает врожденные расовые способности
     *
     * В отличие от классовых способностей, расовые создаются с нуля, потому что на момент создания расы юнита еще нет
     *
     * Возможно в будущем способности у рас и классов будут приведены к единому формату
     *
     * @throws UnitException
     */
    private function addRaceAbilities(): void
    {
        foreach ($this->race->getAbilities() as $abilityClass) {
            try {
                $this->abilities->add(new $abilityClass($this));
            } catch (Throwable $e) {
                throw new UnitException(UnitException::INCORRECT_RACE_ABILITY . ': ' . $abilityClass);
            }
        }
    }
}
