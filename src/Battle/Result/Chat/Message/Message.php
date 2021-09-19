<?php

declare(strict_types=1);

namespace Battle\Result\Chat\Message;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
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
     * @param ActionInterface $action
     * @return string
     * @throws MessageException
     * @uses damage, heal, summon, wait, buff, applyEffect
     */
    public function createMessage(ActionInterface $action): string
    {
        $createMethod = $action->getMessageMethod();

        if (!method_exists($this, $createMethod)) {
            throw new MessageException(MessageException::UNDEFINED_MESSAGE_METHOD);
        }

        return $this->$createMethod($action);
    }

    /**
     * @param DamageAction $action
     * @return string
     * @throws ActionException
     */
    private function damage(DamageAction $action): string
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
    private function heal(HealAction $action): string
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
    private function summon(SummonAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param WaitAction $action
     * @return string
     */
    private function wait(WaitAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param BuffAction $action
     * @return string
     */
    private function buff(BuffAction $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * Сообщение строится по разному, в зависимости от того, на кого применяется эффект - на себя, или на другого юнита
     *
     * @param EffectAction $action
     * @return string
     * @throws ActionException
     */
    private function applyEffect(EffectAction $action): string
    {
        if ($action->getActionUnit()->getId() === $action->getTargetUnit()->getId()) {
            return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
                $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
        }

        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' .
            $this->translation->trans($action->getNameAction()) . ' ' . $this->translation->trans('on') .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() . '</span>';
    }
}
