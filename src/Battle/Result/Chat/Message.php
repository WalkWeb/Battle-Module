<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

use Battle\Action\ActionException;
use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\HealAction;
use Battle\Action\Other\WaitAction;
use Battle\Action\Summon\SummonAction;
use Battle\Translation\Translation;

// todo add interface

class Message
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
}
