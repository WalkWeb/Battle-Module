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
    private $name;

    /**
     * % от максимального здоровья, который будет восстановлен воскрешаемому юниту
     *
     * @var int
     */
    private $power;

    /**
     * @param UnitInterface $actionUnit
     * @param CommandInterface $enemyCommand
     * @param CommandInterface $alliesCommand
     * @param int $typeTarget
     * @param int $power
     * @param string|null $name
     * @param string $icon
     * @throws ActionException
     */
    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        int $power,
        ?string $name = null,
        string $icon = ''
    )
    {
        $typeTarget = $this->validateTypeTarget($typeTarget);
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget, $icon);
        $this->name = $name ?? self::DEFAULT_NAME;
        $this->power = $this->validatePower($power);
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

    public function handle(): string
    {
        // TODO Переделать формирование сообщения так, чтобы обрабатывались ситуации со множеством целей
        $message = '';

        foreach ($this->targetUnits as $targetUnit) {
            $message .= $targetUnit->applyAction($this);
        }

        return $message;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    public function setFactualPower(int $factualPower): void
    {
        $this->factualPower = $factualPower;
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
        return self::DEFAULT_MESSAGE_METHOD;
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
     * Проверяет тип выбора цели. Текущая логика подразумевает воскрешение мертвых только из своей команды.
     * Соответственно, любые другие типы выбора цели, кроме TARGET_DEAD_ALLIES будут некорректны
     *
     * @param int $typeTarget
     * @return int
     * @throws ActionException
     */
    private function validateTypeTarget(int $typeTarget): int
    {
        if ($typeTarget !== self::TARGET_DEAD_ALLIES) {
            throw new ActionException(ActionException::INVALID_RESURRECTED_TARGET);
        }

        return $typeTarget;
    }
}
