<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

class ResurrectionAction extends AbstractAction
{
    private const HANDLE_METHOD          = 'applyResurrectionAction';

    private const DEFAULT_NAME           = 'resurrected';
    private const DEFAULT_MESSAGE_METHOD = 'resurrected';
    // Анимация аналогична лечению
    public const EFFECT_ANIMATION_METHOD = 'effectHeal';

    /**
     * @var string
     */
    private $name;

    /**
     * % от максимального здоровья, который будет восстановлен воскрешаемому юниту
     *
     * TODO Добавить проверку, что power не может быть более 100
     *
     * @var int
     */
    private $power;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        int $power,
        ?string $name = null
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->name = $name ?? self::DEFAULT_NAME;
        $this->power = $power;
    }

    /**
     * Логика подразумевает, что воскрешение возможно только юнитов из своей команды
     *
     * TODO Добавить проверку на то, что выбранный юнит мертв
     *
     * @return bool
     * @throws ActionException
     */
    public function canByUsed(): bool
    {
        $this->targetUnit = $this->searchTargetUnit($this);
        return (bool)$this->targetUnit;
    }

    public function handle(): string
    {
        return $this->targetUnit->applyAction($this);
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
        return self::EFFECT_ANIMATION_METHOD;
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function getMessageMethod(): string
    {
        return self::DEFAULT_MESSAGE_METHOD;
    }
}
