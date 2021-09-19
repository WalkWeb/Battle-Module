<?php

declare(strict_types=1);

namespace Battle\Action;

use Battle\Command\CommandInterface;
use Battle\Unit\Effect\EffectCollection;
use Battle\Unit\UnitInterface;

class EffectAction extends AbstractAction
{
    private const HANDLE_METHOD            = 'applyEffectAction';
    private const DEFAULT_ANIMATION_METHOD = 'effect';
    private const DEFAULT_MESSAGE_METHOD   = 'applyEffect';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    protected $animationMethod;

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
        EffectCollection $effects,
        string $animationMethod = self::DEFAULT_ANIMATION_METHOD
    )
    {
        parent::__construct($actionUnit, $enemyCommand, $alliesCommand, $typeTarget);
        $this->name = $name;
        $this->effects = $effects;
        $this->animationMethod = $animationMethod;
    }

    public function getHandleMethod(): string
    {
        return self::HANDLE_METHOD;
    }

    /**
     * TODO При применении эффектов на других юнитов необходимо расширять механику поиска цели - отсеивая тех, на кого
     * TODO эффект уже есть. Сейчас же, при применении эффекта на противника, просто выбирается случайный

     * TODO Как вариант - добавить два новых типа поиска цели - эффект на команду/эффект на врагов, где будет такая
     * TODO проверка
     *
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

    /**
     * TODO Сейчас бой периодически падает с ошибкой "Can't be used action. Action unit: Succubus. Action: use Poison"
     * TODO Из-за того, что выбранный случайно юнит вначале не имеет эффекта, а потом, при применении эффекта выбирается
     * TODO другой юнит, с эффектом, и эта же проверка уже возвращает false. Необходимо добавить новый поиск цели.
     *
     * @return bool
     * @throws ActionException
     */
    public function canByUsed(): bool
    {
        $this->targetUnit = $this->searchTargetUnit();

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

    public function getAnimationMethod(): string
    {
        return $this->animationMethod;
    }

    public function getMessageMethod(): string
    {
        return self::DEFAULT_MESSAGE_METHOD;
    }

    public function setFactualPower(int $factualPower): void {}
}
