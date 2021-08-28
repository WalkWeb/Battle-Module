<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;

class EffectAction extends AbstractAction
{
    private const HANDLE_METHOD = 'applyEffectAction';

    /**
     * @var string
     */
    private $name;

    /**
     * @var EffectCollection
     */
    private $effects;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget,
        string $name,
        EffectCollection $effects
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->name = $name;
        $this->effects = $effects;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * @return string
     * @throws ActionException
     */
    public function handle(): string
    {
        if (!$this->targetUnit) {
            throw new ActionException(ActionException::NO_TARGET_FOR_EFFECT);
        }

        return $this->targetUnit->applyAction($this);
    }

    public function getNameAction(): string
    {
        return $this->name;
    }

    public function getEffects(): EffectCollection
    {
        return $this->effects;
    }

    public function canByUsed(): bool
    {
        if (!$this->targetUnit) {
            return false;
        }

        foreach ($this->effects as $effect) {
            // Если хотя бы один из накладываемых эффектов еще есть на юните - событие считаем невозможным для применения
            if ($this->targetUnit->getEffects()->exist($effect)) {
                return false;
            }
        }

        return true;
    }

    public function setFactualPower(int $factualPower): void {}
}
