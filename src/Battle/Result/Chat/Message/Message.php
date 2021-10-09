<?php

declare(strict_types=1);

namespace Battle\Result\Chat\Message;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Translation\Translation;

// TODO Вынести функционал в Chat, потому что текущий Message не является объектом чата, это скорее объект-сервис

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
     * @uses damage, heal, summon, wait, buff, resurrected, applyEffect, effectDamage, effectHeal
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
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function damage(ActionInterface $action): string
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
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function heal(ActionInterface $action): string
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
     * @param ActionInterface $action
     * @return string
     */
    private function summon(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param ActionInterface $action
     * @return string
     */
    private function wait(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * @param ActionInterface $action
     * @return string
     */
    private function buff(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction());
    }

    /**
     * Формирует сообщение для события ResurrectionAction в виде:
     *
     * "$name воскресил $targetName"
     *
     * Воскрешение подразумевается только одним юнитом другого. Воскрешение самого себя на данный момент не
     * предусмотренно
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function resurrected(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans($action->getNameAction()) .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() .
            '</span>';
    }

    /**
     * Сообщение строится по разному, в зависимости от того, на кого применяется эффект - на себя, или на другого юнита
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function applyEffect(ActionInterface $action): string
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

    /**
     * Формирует сообщение урона от эффекта в формате:
     * "$name получил урон на $damage от эффекта $effectName"
     *
     * @param ActionInterface $action
     * @return string
     */
    private function effectDamage(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans('received damage') . ' ' .
            $this->translation->trans('on') . ' ' . $action->getFactualPower() . ' ' . $this->translation->trans('life from effect') . ' ' .
            $this->translation->trans($action->getNameAction());
    }

    /**
     * Формирует сообщение здоровья от эффекта в формате:
     * "$name восстановил $power здоровья, от эффекта $effectName"
     *
     * @param ActionInterface $action
     * @return string
     */
    private function effectHeal(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans('restored') . ' ' .
            $action->getFactualPower() . ' ' . $this->translation->trans('life from effect') . ' ' .
            $this->translation->trans($action->getNameAction());
    }
}
