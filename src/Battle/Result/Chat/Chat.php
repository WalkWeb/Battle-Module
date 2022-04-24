<?php

declare(strict_types=1);

namespace Battle\Result\Chat;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Translation\Translation;
use Battle\Translation\TranslationInterface;

/**
 * TODO Можно подумать над оптимизацией количества строк и объединением методов
 *
 * @package Battle\Result\Chat
 */
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
     * Формирует сообщение о нанесении урона.
     *
     * Сообщение может быть в трех вариантах:
     * 1. Нанесение урона
     * 2. Урон заблокирован
     * 3. Нанесение урона + урон заблокирован, если было атаковано сразу несколько целей, и там были и те, кто получил
     *    урон и те, кто его заблокировал
     *
     * TODO В текущей механике юниты запрашиваются два раза - в текущем методе, и потом еще раз при формировании
     * TODO сообщения. Можно оптимизировать
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function damage(ActionInterface $action): string
    {
        $damagedUnits = $this->getTargetsName($action);
        $blockedUnits = $this->getTargetsBlockedName($action);
        $dodgedUnits = $this->getTargetsDodgedName($action);

        if ($damagedUnits && $blockedUnits && $dodgedUnits) {
            return
                $this->getDamagedMessage($action) . '. ' . $this->getBlockedMessage($action) . ' ' . $this->getDodgedMessage($action);
        }

        if ($damagedUnits && $blockedUnits) {
            return $this->getDamagedMessage($action) . '. ' . $this->getBlockedMessage($action);
        }

        if ($damagedUnits && $dodgedUnits) {
            return $this->getDamagedMessage($action) . '. ' . $this->getDodgedMessage($action);
        }

        if ($blockedUnits && $dodgedUnits) {
            return $this->getBlockedMessage($action) . ' ' . $this->getDodgedMessage($action);
        }

        if ($blockedUnits) {
            return $this->getBlockedMessage($action);
        }

        if ($dodgedUnits) {
            return $this->getDodgedMessage($action);
        }

        return $this->getDamagedMessage($action);
    }

    /**
     * Отдельный метод для формирования урона от способностей. В базовом варианте сообщение выглядит так:
     *
     * Unit use <icon> Heavy Strike at Enemy on 50 damage
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function damageAbility(ActionInterface $action): string
    {
        $damagedUnits = $this->getTargetsName($action);
        $blockedUnits = $this->getTargetsBlockedName($action);
        $dodgedUnits = $this->getTargetsDodgedName($action);

        if ($damagedUnits && $blockedUnits && $dodgedUnits) {
            return
                $this->getDamagedAbilityMessage($action) . '. ' . $this->getBlockedAbilityMessage($action) . ' ' . $this->getDodgedAbilityMessage($action);
        }

        if ($damagedUnits && $blockedUnits) {
            return $this->getDamagedAbilityMessage($action) . '. ' . $this->getBlockedAbilityMessage($action);
        }

        if ($damagedUnits && $dodgedUnits) {
            return $this->getDamagedAbilityMessage($action) . '. ' . $this->getDodgedAbilityMessage($action);
        }

        if ($blockedUnits && $dodgedUnits) {
            return $this->getBlockedAbilityMessage($action) . ' ' . $this->getDodgedAbilityMessage($action);
        }

        if ($blockedUnits) {
            return $this->getBlockedAbilityMessage($action);
        }

        if ($dodgedUnits) {
            return $this->getDodgedAbilityMessage($action);
        }

        return $this->getDamagedAbilityMessage($action);
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

    // TODO Add selfResurrected

    /**
     * Сообщение строится по разному, в зависимости от того, на кого применяется эффект - на себя, или на другого юнита
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function applyEffect(ActionInterface $action): string
    {
        // Если цель у эффекта одна, и эта цель сам юнит - сообщение нужно формировать по другому
        if ($this->isApplySelf($action)) {
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
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getTargetsName(ActionInterface $action): string
    {
        $targets = clone $action->getTargetUnits();
        $names = [];

        foreach ($targets as $target) {
            if (!$action->isBlocked($target) && !$action->isDodged($target)) {
                $names[] = '<span style="color: ' . $target->getRace()->getColor() . '">' . $target->getName() . '</span>';
            }
        }

        return $this->gluingNames($names);
    }

    /**
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getTargetsBlockedName(ActionInterface $action): string
    {
        $targets = clone $action->getTargetUnits();
        $names = [];

        foreach ($targets as $target) {
            if ($action->isBlocked($target)) {
                $names[] = '<span style="color: ' . $target->getRace()->getColor() . '">' . $target->getName() . '</span>';
            }
        }

        return $this->gluingNames($names);
    }

    /**
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getTargetsDodgedName(ActionInterface $action): string
    {
        $targets = clone $action->getTargetUnits();
        $names = [];

        foreach ($targets as $target) {
            if ($action->isDodged($target)) {
                $names[] = '<span style="color: ' . $target->getRace()->getColor() . '">' . $target->getName() . '</span>';
            }
        }

        return $this->gluingNames($names);
    }

    /**
     * Формирует строку на основании количества целей:
     *
     * 1 цель: unit
     * 2 цели: unit and unit
     * 3+ цели: unit, unit and unit
     *
     * @param array $names
     * @return string
     */
    private function gluingNames(array $names): string
    {
        $count = count($names);

        if ($count === 0) {
            return '';
        }

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
    
    /**
     * @param ActionInterface $action
     * @return bool
     * @throws ActionException
     */
    private function isApplySelf(ActionInterface $action): bool
    {
        if (count($action->getTargetUnits()) > 1) {
            return false;
        }

        /*
         * Можно написать проще:
         *
         * foreach ($action->getTargetUnits() as $targetUnit) {
         *    return $targetUnit->getId() === $action->getActionUnit()->getId();
         * }
         *
         * Но IDE ругается на такой foreach как на ошибку. По этому написан более сложный вариант.
         */

        $target = null;

        foreach ($action->getTargetUnits() as $targetUnit) {
            $target =  $targetUnit;
        }

        return $target->getId() === $action->getActionUnit()->getId();
    }

    /**
     * Формирует сообщение о юнитах, которые получили урон
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getDamagedMessage(ActionInterface $action): string
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
     * Формирует сообщение о юнитах которые заблокировали удар.
     *
     * @param $action
     * @return string
     * @throws ActionException
     */
    private function getBlockedMessage(ActionInterface $action): string
    {
        return
            // unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "tried to strike, but"
            $this->translation->trans('tried to strike, but') . ' ' .
            // targets
            $this->getTargetsBlockedName($action) . ' ' .
            // "blocked it!"
            $this->translation->trans('blocked it') . '!';
    }

    /**
     * Формирует сообщение о юнитах которые увернулись от удара
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getDodgedMessage(ActionInterface $action): string
    {
        return
            // unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "tried to strike, but"
            $this->translation->trans('tried to strike, but') . ' ' .
            // targets
            $this->getTargetsDodgedName($action) . ' ' .
            // "dodged!"
            $this->translation->trans('dodged') . '!';
    }

    /**
     * Формирует сообщение о юнитах, которые получили урон от способности
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getDamagedAbilityMessage(ActionInterface $action): string
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
            // targets
            ' ' . $this->getTargetsName($action) . ' ' .
            // "on"
            $this->translation->trans('on') . ' ' .
            // # damage
            $action->getFactualPower() . ' ' . $this->translation->trans('damage');
    }

    /**
     * Формирует сообщение о юнитах которые заблокировали урон от способности
     *
     * @param $action
     * @return string
     * @throws ActionException
     */
    private function getBlockedAbilityMessage(ActionInterface $action): string
    {
        return
            // unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "use"
            $this->translation->trans('use') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span> ' .
            // "but"
            $this->translation->trans('but') . ' ' .
            // targets
            $this->getTargetsBlockedName($action) . ' ' .
            // "blocked it!"
            $this->translation->trans('blocked it') . '!';
    }


    /**
     * Формирует сообщение о юнитах которые уклонились от способности
     *
     * @param $action
     * @return string
     * @throws ActionException
     */
    private function getDodgedAbilityMessage(ActionInterface $action): string
    {
        return
            // unit
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span> ' .
            // "use"
            $this->translation->trans('use') . ' ' .
            // ability icon
            $this->getIcon($action) .
            // ability name
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span> ' .
            // "but"
            $this->translation->trans('but') . ' ' .
            // targets
            $this->getTargetsDodgedName($action) . ' ' .
            // "blocked it!"
            $this->translation->trans('dodged') . '!';
    }
}
