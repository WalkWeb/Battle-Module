<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var UnitInterface
     */
    protected $actionUnit;

    /**
     * @var UnitInterface
     */
    protected $targetUnit;

    /**
     * @var CommandInterface
     */
    protected $alliesCommand;

    /**
     * @var CommandInterface
     */
    protected $enemyCommand;

    /**
     * Тип выбора цели для применения события, например: на себя, на врага, на самого раненого союзника и т.д.
     *
     * @var int
     */
    protected $typeTarget;

    /**
     * @var int
     */
    protected $factualPower = 0;

    public function __construct(
        UnitInterface $actionUnit,
        CommandInterface $enemyCommand,
        CommandInterface $alliesCommand,
        int $typeTarget
    )
    {
        $this->actionUnit = $actionUnit;
        $this->enemyCommand = $enemyCommand;
        $this->alliesCommand = $alliesCommand;
        $this->typeTarget = $typeTarget;

        // TODO Возможно имеет смысл сразу проверять $typeTarget на существование, чтобы созданный объект был валидным
    }

    public function getActionUnit(): UnitInterface
    {
        return $this->actionUnit;
    }

    public function getTypeTarget(): int
    {
        return $this->typeTarget;
    }

    /**
     * @return UnitInterface
     * @throws ActionException
     */
    public function getTargetUnit(): UnitInterface
    {
        if ($this->targetUnit === null) {
            throw new ActionException(ActionException::NO_TARGET_UNIT);
        }

        return $this->targetUnit;
    }

    /**
     * @return int
     * @throws ActionException
     */
    public function getPower(): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    public function getFactualPower(): int
    {
        return $this->factualPower;
    }

    /**
     * Сейчас всего 6 типа событий, и 4 из них могут примениться по-умолчанию, это:
     *
     * 1. Урон (если все противники умерли - бой заканчивается)
     * 2. Пропуск хода
     * 3. Призыв
     * 4. Баф
     *
     * И только для двух событий, нужно делать проверку на возможность применения:
     *
     * 5. Лечение
     * 6. Эффект (если юнит уже имеет аналогичный эффект - накладывать его еще раз не нужно)
     *
     * Соответственно внутри HealAction и EffectAction данный метод переназначается
     *
     * TODO При добавлении эффектов и баффов механика работы событий усложнилась, и текущий дефолтный return true
     * TODO может вызвать ошибки. Пока оставляем, но при любых непонятных ошибках с Action нужно будет отказаться от
     * TODO такого варианта по умолчанию - пусть каждый Action делает фактическую проверку своего применения
     *
     * @return bool
     */
    public function canByUsed(): bool
    {
        return true;
    }

    /**
     * @return UnitInterface
     * @throws ActionException
     */
    public function getSummonUnit(): UnitInterface
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return string
     * @throws ActionException
     */
    public function getModifyMethod(): string
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @param int $revertValue
     * @throws ActionException
     */
    public function setRevertValue(int $revertValue): void
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return int
     * @throws ActionException
     */
    public function getRevertValue(): int
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return ActionInterface
     * @throws ActionException
     */
    public function getRevertAction(): ActionInterface
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * @return EffectCollection
     * @throws ActionException
     */
    public function getEffects(): EffectCollection
    {
        throw new ActionException(ActionException::NO_METHOD . ': ' . __CLASS__ . '::' . __METHOD__);
    }

    /**
     * Ищет юнита для применения события.
     *
     * @return UnitInterface|null
     * @throws ActionException
     */
    protected function searchTargetUnit(): ?UnitInterface
    {
        switch ($this->typeTarget) {
            case self::TARGET_SELF:
                return $this->actionUnit;
            case self::TARGET_RANDOM_ENEMY:
               return $this->getRandomEnemyUnit();
            case self::TARGET_WOUNDED_ALLIES:
                return $this->alliesCommand->getUnitForHeal();
        }

        throw new ActionException(ActionException::UNKNOWN_TYPE_TARGET . ': ' . $this->typeTarget);
    }

    /**
     * @return UnitInterface|null
     */
    private function getRandomEnemyUnit(): ?UnitInterface
    {
        if ((!$this->actionUnit->isMelee()) || ($this->actionUnit->isMelee() && !$this->enemyCommand->existMeleeUnits())) {
            return $this->enemyCommand->getUnitForAttacks();
        }

        return $this->enemyCommand->getMeleeUnitForAttacks();
    }
}
