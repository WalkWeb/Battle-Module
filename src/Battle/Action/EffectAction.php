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
        string $name,
        EffectCollection $effects
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand);
        $this->name = $name;
        $this->effects = $effects;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    public function handle(): string
    {
        // TODO Сейчас эффект применяется только на себе, в будущем нужно добавить параметр, на кого должен применяться
        // TODO эффект

        return $this->actionUnit->applyAction($this);
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
        // TODO Доработать метод, когда цель будет выбираться не только по себе

        foreach ($this->effects as $effect) {
            // Если хотя бы один из накладываемых эффектов еще есть на юните - событие считаем невозможным для применения
            if ($this->actionUnit->getEffects()->exist($effect)) {
                return false;
            }
        }

        return true;
    }

    public function setFactualPower(int $factualPower): void {}
}
