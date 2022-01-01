<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;

class Chat implements ChatInterface
{
    /**
     * @var TranslationInterface
     */
    private $translation;

    /**
     * @var string[]
     */
    private $messages = [];

    /**
     * @param TranslationInterface|null $translation
     */
    public function __construct(?TranslationInterface $translation = null)
    {
        $this->translation = $translation ?? new Translation();
    }

    /**
     * @param ActionInterface $action
     * @return string
     * @throws ChatException
     * @uses damage, damageAbility, heal, summon, wait, buff, resurrected, applyEffect, effectDamage, effectHeal, skip, applyEffectImproved
     */
    public function addMessage(ActionInterface $action): string
    {
        $createMethod = $action->getMessageMethod();

        if (!method_exists($this, $createMethod)) {
            throw new ChatException(ChatException::UNDEFINED_MESSAGE_METHOD);
        }

        $message = $this->$createMethod($action);

        if ($message !== '') {
            $this->messages[] = $message;
        }

        return $message;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function damage(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->getIcon($action) .
            $this->translation->trans( $action->getNameAction()) .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() .
            '</span> ' . $this->translation->trans('on') . ' ' .
            $action->getFactualPower() . ' ' . $this->translation->trans('damage');
    }

    /**
     * Отдельный метод для формирования урона от способностей. Сообщение выглядит так:
     *
     * Unit use <icon> Heavy Strike at Enemy on 50 damage
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function damageAbility(ActionInterface $action): string
    {
        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->translation->trans('use') . ' ' .
            $this->getIcon($action) . $this->translation->trans($action->getNameAction()) . ' ' .
            $this->translation->trans('at') .
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
            $action->getActionUnit()->getName() . '</span> ' . $this->getIcon($action) .
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
            $action->getActionUnit()->getName() . '</span> ' . $this->getIcon($action) .
            $this->translation->trans($action->getNameAction());
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
            $action->getActionUnit()->getName() . '</span> ' . $this->getIcon($action) .
            $this->translation->trans($action->getNameAction()) .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() .
            '</span>';
    }

    /**
     * Сообщение строится по разному, в зависимости от того, на кого применяется эффект - на себя, или на другого юнита
     *
     * TODO Устаревший метод формирования сообщения об эффекте. Когда все способности будут обновлены для формирования
     * TODO сообщения по-новому типу - этот метод будет удален.
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function applyEffect(ActionInterface $action): string
    {
        if ($action->getActionUnit()->getId() === $action->getTargetUnit()->getId()) {
            return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
                $action->getActionUnit()->getName() . '</span> ' . $this->getIcon($action) .
                $this->translation->trans($action->getNameAction());
        }

        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' . $this->getIcon($action) .
            $this->translation->trans($action->getNameAction()) . ' ' . $this->translation->trans('on') .
            ' <span style="color: ' . $action->getTargetUnit()->getRace()->getColor() . '">' .
            $action->getTargetUnit()->getName() . '</span>';
    }

    /**
     * Улучшенный метод для формирования сообщения о примененном эффекте. В нем название способности отделено от
     * действия, что позволяет добавить иконку способности именно перед названием способности.
     *
     * Было так:
     * Юнит <icon> использовал Способность
     *
     * Теперь так:
     * Юнит использовал <icon> Способность
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function applyEffectImproved(ActionInterface $action): string
    {
        if ($action->getActionUnit()->getId() === $action->getTargetUnit()->getId()) {
            return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
                $action->getActionUnit()->getName() . '</span> ' .
                $this->translation->trans('use') . ' ' .
                $this->getIcon($action) .
                $this->translation->trans($action->getNameAction());
        }

        return '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' .
            $action->getActionUnit()->getName() . '</span> ' .
            $this->translation->trans('use') . ' ' . $this->getIcon($action) .
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
            $this->getIcon($action) . $this->translation->trans($action->getNameAction());
    }

    /**
     * Пропускает генерацию сообщения
     *
     * @return string
     */
    private function skip(): string
    {
        return '';
    }

    /**
     * @param ActionInterface $action
     * @return string
     */
    private function getIcon(ActionInterface $action): string
    {
        return $action->getIcon() === '' ? '' : '<img src="' . $action->getIcon() . '" alt="" /> ';
    }
}
