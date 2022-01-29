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
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // ability icon, if any
            $this->getIcon($action) .
            // attack
            $this->translation->trans( $action->getNameAction()) .
            // Targets
            ' ' . $this->getTargetsName($action) . ' ' .
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
            // Targets
            ' ' . $this->getTargetsName($action) . ' ' .
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
        return
            // Unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // ability icon, if any
            $this->getIcon($action) .
            // heal
            $this->translation->trans($action->getNameAction()) .
            // Targets
            ' ' . $this->getTargetsName($action) . ' ' .
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
            // Targets
            ' ' . $this->getTargetsName($action) . ' ' .
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
            // Targets
            ' ' . $this->getTargetsName($action);
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

        // Если цель у эффекта одна, и эта цель сам юнит - сообщение нужно формировать по другому
        if (count($action->getTargetUnits()) === 1 && $action->getActionUnit()->getId() === $targetUnit->getId()) {
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
            // Targets
            ' ' . $this->getTargetsName($action);
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

    /**
     * Формирует строку на основании количества целей:
     *
     * 1 цель: unit
     * 2 цели: unit and unit
     * 3+ цели: unit, unit and unit
     *
     * На данный момент несколько целей могут быть только у удара или лечения
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getTargetsName(ActionInterface $action): string
    {
        // TODO На всякий случай добавить проверку на то, что у Action нет цели

        $targets = clone $action->getTargetUnits();
        $names = [];

        foreach ($targets as $target) {
            $names[] = '<span style="color: ' . $target->getRace()->getColor() . '">' . $target->getName() . '</span>';
        }

        $count = count($names);

        if ($count === 1) {
            return $names[0];
        }

        if ($count === 2) {
            return $names[0] . ' ' . $this->translation->trans('and') . ' ' . $names[1];
        }

        $last = $names[$count - 1];
        unset($names[$count - 1]);

        return implode(', ', $names) . ' ' . $this->translation->trans('and') . ' ' . $last;
    }
}
