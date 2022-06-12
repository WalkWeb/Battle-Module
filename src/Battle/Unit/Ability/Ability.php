<?php

declare(strict_types=1);

namespace Battle\Unit\Ability;

use Battle\Action\ActionCollection;
use Battle\Action\ActionFactory;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;
use Exception;

/**
 * TODO В будущем не будет отдельных классов на каждую способность, а будет один универсальный класс способности,
 * TODO который будет создаваться на основе массива с параметрами
 *
 * TODO Но т.к. это трудоемкий переход - он будет делаться постепенно, и на это время будут существовать сразу оба
 * TODO варианта с отдельными классами и одним универсальным
 *
 * @package Battle\Unit\Ability
 */
class Ability extends AbstractAbility
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var array
     */
    private $actionsData;

    /**
     * TODO В будущем, когда конкретные классы способностей будут удалены - этот параметр нужно вынести в AbstractAbility
     *
     * @var int
     */
    private $typeActivate;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @param UnitInterface $unit
     * @param bool $disposable
     * @param string $name
     * @param string $icon
     * @param array $actionsData
     * @param int $typeActivate
     * @param int $chanceActivate
     * @param ActionFactory|null $actionFactory
     * @throws Exception
     */
    public function __construct(
        UnitInterface $unit,
        bool $disposable,
        string $name,
        string $icon,
        array $actionsData,
        int $typeActivate,
        int $chanceActivate = 100,
        ?ActionFactory $actionFactory = null
    )
    {
        parent::__construct($unit, $disposable, $chanceActivate);
        $this->name = $name;
        $this->icon = $icon;
        $this->typeActivate = $typeActivate;
        $this->actionFactory = $actionFactory ?? new ActionFactory();
        $this->actionsData = $this->validateActionsData($actionsData);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return bool
     * @throws Exception
     */
    public function canByUsed(CommandInterface $enemyCommand, CommandInterface $alliesCommand): bool
    {
        if ($this->disposable && $this->usage) {
            return false;
        }

        foreach ($this->getAction($enemyCommand, $alliesCommand) as $action) {
            if (!$action->canByUsed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @return ActionCollection
     * @throws Exception
     */
    public function getAction(CommandInterface $enemyCommand, CommandInterface $alliesCommand): ActionCollection
    {
        $actions = new ActionCollection();
        foreach ($this->actionsData as &$actionData) {
            $this->addParameters($actionData, $enemyCommand, $alliesCommand);
            $actions->add($this->actionFactory->create($actionData));
        }

        return $actions;
    }

    /**
     * @param UnitInterface $unit
     * @param bool $testMode
     * @throws Exception
     */
    public function update(UnitInterface $unit, bool $testMode = false): void
    {
        if ($this->disposable && $this->usage) {
            $this->ready = false;
            return;
        }

        switch ($this->typeActivate) {
            case self::ACTIVATE_CONCENTRATION:
                $this->ready = $unit->getConcentration() === UnitInterface::MAX_CONCENTRATION;
                break;
            case self::ACTIVATE_RAGE:
                $this->ready = $unit->getRage() === UnitInterface::MAX_RAGE;
                break;
            case self::ACTIVATE_LOW_LIFE:
                $this->ready = !$this->usage && $this->unit->getLife() < $this->unit->getTotalLife() * 0.3;
                break;
            case self::ACTIVATE_DEAD:
                if ($testMode) {
                    $this->ready = !$this->unit->isAlive();
                } else {
                    $this->ready = !$this->unit->isAlive() && random_int(0, 100) <= $this->chanceActivate;
                }
                break;
            // TODO Default: exception unknown type activate
        }
    }

    public function usage(): void
    {
        $this->ready = false;
        $this->usage = true;

        if ($this->typeActivate === self::ACTIVATE_CONCENTRATION) {
            $this->unit->useConcentrationAbility();
        }
        if ($this->typeActivate === self::ACTIVATE_RAGE) {
            $this->unit->useRageAbility();
        }
    }

    /**
     * @param array $actionsData
     * @return array
     * @throws Exception
     */
    private function validateActionsData(array $actionsData): array
    {
        foreach ($actionsData as $actionData) {
            // Проверяем, что передан массив из массивов
            // Дальнейшая валидация будет происходить в ActionFactory
            if (!is_array($actionData)) {
                throw new AbilityException(AbilityException::INVALID_ACTION_DATA);
            }
        }

        return $actionsData;
    }

    /**
     * Параметры способности изначально не связаны с юнитом (точнее есть только юнит, в момент создания способности, но
     * не на момент написания массива параметров способности) и командами - потому что изначально это просто массив с
     * параметрами.
     *
     * Но для создания Actions уже нужна информация по юниту, который делает ход, и о командах. По этому нужные
     * параметры добавляются в момент запроса Actions, когда юнит и команды уже существуют
     *
     * @param array $data
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @throws AbilityException
     */
    private function addParameters(array &$data, CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        $data['action_unit'] = $this->unit;
        $data['enemy_command'] = $enemyCommand;
        $data['allies_command'] = $alliesCommand;

        if (array_key_exists('effect', $data)) {
            $this->validateEffectData($data['effect']);
            foreach ($data['effect']['on_apply_actions'] as &$onApplyActionData) {
                $this->addStageParameters($onApplyActionData, $enemyCommand, $alliesCommand);
            }
            unset($onApplyActionData);
            foreach ($data['effect']['on_next_round_actions'] as &$onNextRoundActionData) {
                $this->addStageParameters($onNextRoundActionData, $enemyCommand, $alliesCommand);
            }
            unset($onNextRoundActionData);
            foreach ($data['effect']['on_disable_actions'] as &$onDisableActionData) {
                $this->addStageParameters($onDisableActionData, $enemyCommand, $alliesCommand);
            }
            unset($onDisableActionData);
        }
    }

    /**
     * @param array $data
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     */
    private function addStageParameters(array &$data, CommandInterface $enemyCommand, CommandInterface $alliesCommand): void
    {
        $data['action_unit'] = $this->unit;
        $data['enemy_command'] = $enemyCommand;
        $data['allies_command'] = $alliesCommand;
    }

    /**
     * Проверяет наличие необходимых параметров в массиве параметров для создания эффекта:
     * "on_apply_actions"
     * "on_next_round_actions"
     * "on_disable_actions"
     *
     * @param array $data
     * @throws AbilityException
     */
    private function validateEffectData(array $data): void
    {
        if (
            !array_key_exists('on_apply_actions', $data) ||
            !array_key_exists('on_next_round_actions', $data) ||
            !array_key_exists('on_disable_actions', $data)
        ) {
            throw new AbilityException(AbilityException::INVALID_EFFECT_DATA);
        }
    }
}
