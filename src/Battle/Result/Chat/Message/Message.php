<?php

declare(strict_types=1);

namespace Battle\Result\Chat\Message;

use Battle\Action\ActionException;
use Battle\Action\BuffAction;
use Battle\Action\DamageAction;
use Battle\Action\EffectAction;
use Battle\Action\HealAction;
use Battle\Action\WaitAction;
use Battle\Action\SummonAction;
use Battle\Translation\Translation;

class Message implements MessageInterface
{
    /**
     * @var Translation
     */
    private $translation;

    /**
     * @param Translation|null $translation
     */
    public function __construct(?Translation $translation = null)
    {
        $this->translation = $translation ?? new Translation();
    }

    /**
     * @param DamageAction $action
     * @return string
     * @throws ActionException
     */
    public function damage(DamageAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' .
            $this->translation->trans( $action->getNameAction()) .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() .
            '</span> ' . $this->translation->trans('on') . ' ' .
            $action->getFactualPower() . ' ' . $this->translation->trans('damage');
    }

    /**
     * @param HealAction $action
     * @return string
     * @throws ActionException
     */
    public function heal(HealAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' .
            $this->translation->trans($action->getNameAction()) .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() .
            '</span> ' . $this->translation->trans('on') . ' ' . $action->getFactualPower() . ' ' .
            $this->translation->trans('life');
    }

    /**
     * @param SummonAction $action
     * @return string
     */
    public function summon(SummonAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param WaitAction $action
     * @return string
     */
    public function wait(WaitAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param BuffAction $action
     * @return string
     */
    public function buff(BuffAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param EffectAction $action
     * @return string
     */
    public function applyEffect(EffectAction $action): string
    {
        // TODO Пока эффект применяется только на себя, в будущем сообщение нужно формировать по разному, в зависимости
        // TODO от того, применен эффект на себя или на кого-то другого

        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }
}
