<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

use Battle\Action\Damage\DamageAction;
use Battle\Action\Heal\HealAction;
use Battle\Action\Summon\SummonAction;
use Battle\Translation\Translation;
use Battle\Translation\TranslationException;

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
     * @throws TranslationException
     */
    public function damage(DamageAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> ' .
            $this->translation->trans( $action->getNameAction()) . ' <b>' .
            $action->getTargetUnit()->getName() .
            '</b> ' . $this->translation->trans('on') . ' ' .
            $action->getFactualPower() . ' ' . $this->translation->trans('damage');
    }

    /**
     * @param HealAction $action
     * @return string
     * @throws TranslationException
     */
    public function heal(HealAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() . '</b> ' .
            $this->translation->trans($action->getNameAction()) .
            ' ' . $this->translation->trans('to') . ' <b>' .
            $action->getTargetUnit()->getName() .
            '</b> ' . $this->translation->trans('on') . ' ' . $action->getFactualPower() . ' ' .
            $this->translation->trans('life');
    }

    /**
     * @param SummonAction $action
     * @return string
     * @throws TranslationException
     */
    public function summon(SummonAction $action): string
    {
        return '<b>' .
            $action->getActionUnit()->getName() .
            '</b> '  . $this->translation->trans($action->getNameAction());
    }
}
