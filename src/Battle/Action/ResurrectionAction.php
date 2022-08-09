<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitException;
use Battle\Unit\UnitInterface;

class ResurrectionAction extends AbstractAction
{
    private const MIM_POWER = 1;
    private const MAX_POWER = 100;

    private const HANDLE_METHOD          = 'applyResurrectionAction';

    private const DEFAULT_NAME           = 'resurrected';
    private const DEFAULT_MESSAGE_METHOD = 'resurrected';
    private const ANIMATION_METHOD       = 'resurrected';

    /**
     * @var string
     */
    private string $name;

    /**
     * % от максимального здоровья, который будет восстановлен воскрешаемому юниту
     *
     * @var int
     */
    private int $power;

    /**
     * @var string
     */
    private string $messageMethod;

    /**
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param int $typeTarget
     * @param int $power
     * @param string|null $name
     * @param string $icon
     * @param string|null $messageMethod
     * @throws ActionException
     */
    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        int $power,
        ?string $name = null,
        string $icon = '',
        ?string $messageMethod = null
    )
    {
        $typeTarget = $this->validateTypeTarget($typeTarget);
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);
        $this->name = $name ?? self::DEFAULT_NAME;
        $this->power = $this->validatePower($power);
        $this->messageMethod = $messageMethod ?? self::DEFAULT_MESSAGE_METHOD;
    }

    /**
     * Логика подразумевает, что воскрешение возможно только юнитов из своей команды
     *
     * @return bool
     * @throws ActionException
     * @throws UnitException
     */
    public function canByUsed(): bool
    {
        $this->targetUnits = $this->searchTargetUnits($this);
        return count($this->targetUnits) > 0;
    }

    public function handle(): void
    {
        foreach ($this->targetUnits as $targetUnit) {
            $targetUnit->applyAction($this);
        }
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    /**
     * Воскрешение восстанавливает часть здоровья, по этому силу лечения считать нужно
     *
     * @param UnitInterface $unit
     * @param int $factualPower
     */
    public function addFactualPower(UnitInterface $unit, int $factualPower): void
    {
        $this->factualPower = $factualPower;
        $this->factualPowerByUnit[$unit->getId()] = $factualPower;
    }

    /**
     * @param UnitInterface $unit
     * @return int
     * @throws ActionException
     */
    public function getFactualPowerByUnit(UnitInterface $unit): int
    {
        if (!array_key_exists($unit->getId(), $this->factualPowerByUnit)) {
            throw new ActionException(ActionException::NO_POWER_BY_UNIT . ': ' . $unit->getId());
        }

        return $this->factualPowerByUnit[$unit->getId()];
    }

    public function getAnimationMethod(): string
    {
        return self::ANIMATION_METHOD;
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function getMessageMethod(): string
    {
        return $this->messageMethod;
    }

    /**
     * Проверяет силу воскрешения на допустимые значения
     *
     * @param int $power
     * @return int
     * @throws ActionException
     */
    private function validatePower(int $power): int
    {
        if ($power < self::MIM_POWER || $power > self::MAX_POWER) {
            throw new ActionException(
                ActionException::INVALID_RESURRECTED_POWER . ': min: ' . self::MIM_POWER . ', max: ' . self::MAX_POWER
            );
        }

        return $power;
    }

    /**
     * Проверяет тип выбора цели. Текущие способности подразумевают воскрешение мертвых только из своей команды, или
     * самого себя.
     *
     * Соответственно, любые другие типы выбора цели, кроме TARGET_DEAD_ALLIES и TARGET_SELF будут некорректны
     *
     * @param int $typeTarget
     * @return int
     * @throws ActionException
     */
    private function validateTypeTarget(int $typeTarget): int
    {
        if ($typeTarget !== self::TARGET_DEAD_ALLIES && $typeTarget !== self::TARGET_SELF) {
            throw new ActionException(ActionException::INVALID_RESURRECTED_TARGET);
        }

        return $typeTarget;
    }
}
