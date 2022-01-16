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
     * @uses damage, damageAbility, heal, healAbility, summon, wait, buff, resurrected, applyEffect, effectDamage, effectHeal, skip
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
        // TODO На данный момент механика применения Action ко множеству целей в процессе добавления
        // TODO Задача поделена на несколько этапов, и обновление формирования сообщений будет сделано отдельно
        // TODO Для того, чтобы все работало как раньше - выбираем первую цель (пока нет событий с несколькими целями)
        $targetUnit = $action->getTargetUnits()[0];

        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // ability icon, if any
            $this->getIcon($action) .
            // attack
            $this->translation->trans( $action->getNameAction()) .
            // Target
            ' <span style="color: ' . $targetUnit->getRace()->getColor() . '">' . $targetUnit->getName() . '</span> ' .
            // "on"
            $this->translation->trans('on') . ' ' .
            // # damage
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
        // Временно, с.м. комментарий выше
        $targetUnit = $action->getTargetUnits()[0];

        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "use"
            $this->translation->trans('use') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span> ' .
            // "at"
            $this->translation->trans('at') .
            // Target
            ' <span style="color: ' . $targetUnit->getRace()->getColor() . '">' . $targetUnit->getName() . '</span> ' .
            // "on"
            $this->translation->trans('on') . ' ' .
            // # damage
            $action->getFactualPower() . ' ' . $this->translation->trans('damage');
    }

    /**
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function heal(ActionInterface $action): string
    {
        // Временно, с.м. комментарий выше
        $targetUnit = $action->getTargetUnits()[0];

        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // ability icon, if any
            $this->getIcon($action) .
            // heal
            $this->translation->trans($action->getNameAction()) .
            // Target
            ' <span style="color: ' . $targetUnit->getRace()->getColor() . '">' . $targetUnit->getName() . '</span> ' .
            // "on"
            $this->translation->trans('on') . ' ' .
            // # life
            $action->getFactualPower() . ' ' . $this->translation->trans('life');
    }

    /**
     * Использование лечение со способности. В отличие от обычного лечения добавляется иконка способности
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    public function healAbility(ActionInterface $action): string
    {
        // Временно, с.м. комментарий выше
        $targetUnit = $action->getTargetUnits()[0];

        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "use"
            $this->translation->trans('use') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span> ' .
            // "and heal"
            $this->translation->trans('and heal') .
            // Target
            ' <span style="color: ' . $targetUnit->getRace()->getColor() . '">' . $targetUnit->getName() . '</span> ' .
            // "on"
            $this->translation->trans('on') . ' ' .
            // Power
            $action->getFactualPower() . ' ' .
            // "life"
            $this->translation->trans('life');
    }

    /**
     * Призыв существ сейчас используется только со способностей
     *
     * @param ActionInterface $action
     * @return string
     */
    private function summon(ActionInterface $action): string
    {
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // summon
            $this->translation->trans('summon') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>';
    }

    /**
     * @param ActionInterface $action
     * @return string
     */
    private function wait(ActionInterface $action): string
    {
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // preparing to attack
            $this->translation->trans($action->getNameAction());
    }

    /**
     * В текущих способностях сам баф не формирует сообщение - его формирует эффект, от которого применяется баф.
     *
     * @param ActionInterface $action
     * @return string
     */
    private function buff(ActionInterface $action): string
    {
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // use message
            $this->translation->trans($action->getNameAction());
    }

    /**
     * Воскрешение может быть использовано только со способностей. Формирует сообщение для события ResurrectionAction в
     * виде:
     *
     * "Unit use <icon> NameAction and resurrected Target"
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
        // Временно, с.м. комментарий выше
        $targetUnit = $action->getTargetUnits()[0];

        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "use"
            $this->translation->trans('use') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span> ' .
            // "and resurrected"
            $this->translation->trans('and resurrected') .
            // Target
            ' <span style="color: ' . $targetUnit->getRace()->getColor() . '">' . $targetUnit->getName() . '</span>';
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
        // Временно, с.м. комментарий выше
        $targetUnit = $action->getTargetUnits()[0];

        if ($action->getActionUnit()->getId() === $targetUnit->getId()) {
            return
                // Unit
                '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
                // "use"
                $this->translation->trans('use') . ' ' .
                // ability icon
                $this->getIcon($action) .
                // ability name
                '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>';
        }

        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "use"
            $this->translation->trans('use') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span> ' .
            // "on"
            $this->translation->trans('on') .
            // Target
            ' <span style="color: ' . $targetUnit->getRace()->getColor() . '">' . $targetUnit->getName() . '</span>';
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
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "received damage"
            $this->translation->trans('received damage') . ' ' .
            // "on" #
            $this->translation->trans('on') . ' ' . $action->getFactualPower() . ' ' .
            // "life from effect"
            $this->translation->trans('life from effect') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>';
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
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "restored"
            $this->translation->trans('restored') . ' ' .
            // # "life from effect"
            $action->getFactualPower() . ' ' . $this->translation->trans('life from effect') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>';
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
