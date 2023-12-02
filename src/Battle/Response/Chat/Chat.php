<?php

declare(strict_types=1);

namespace Battle\Response\Chat;

use Battle\Action\ActionException;
use Battle\Action\ActionInterface;
use Battle\Container\ContainerException;
use Battle\Container\ContainerInterface;
use Battle\Translation\TranslationInterface;

class Chat implements ChatInterface
{
    /**
     * @var TranslationInterface
     */
    private TranslationInterface $translation;

    /**
     * @var string[]
     */
    private array $messages = [];

    /**
     * @param ContainerInterface $container
     * @throws ContainerException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->translation = $container->getTranslation();
    }

    /**
     * Добавляет сообщение в чат на основе типа и параметров Action
     *
     * @param ActionInterface $action
     * @return string
     * @throws ChatException
     * @uses damage, damageAbility, heal, healAbility, manaRestore, manaRestoreAbility, summon, wait, paralysis, stunned, buff, resurrected, selfRaceResurrected, applyEffect, effectDamage, effectHeal, effectManaRestore, skip
     */
    public function addMessage(ActionInterface $action): string
    {
        $createMethod = $action->getMessageMethod();

        if (!method_exists($this, $createMethod)) {
            throw new ChatException(ChatException::UNDEFINED_MESSAGE_METHOD . ': ' . $createMethod);
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
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function damage(ActionInterface $action): string
    {
        $targetNames = $this->getTargetsName($action);
        $blockedNames = $this->getTargetsBlockedName($action);
        $dodgedNames = $this->getTargetsDodgedName($action);

        if ($targetNames && $blockedNames && $dodgedNames) {
            return
                $this->getDamagedMessage($action, $targetNames) . '. ' . $this->getBlockedMessage($action, $blockedNames) . ' ' . $this->getDodgedMessage($action, $dodgedNames);
        }

        if ($targetNames && $blockedNames) {
            return $this->getDamagedMessage($action, $targetNames) . '. ' . $this->getBlockedMessage($action, $blockedNames);
        }

        if ($targetNames && $dodgedNames) {
            return $this->getDamagedMessage($action, $targetNames) . '. ' . $this->getDodgedMessage($action, $dodgedNames);
        }

        if ($blockedNames && $dodgedNames) {
            return $this->getBlockedMessage($action, $blockedNames) . ' ' . $this->getDodgedMessage($action, $dodgedNames);
        }

        if ($blockedNames) {
            return $this->getBlockedMessage($action, $blockedNames);
        }

        if ($dodgedNames) {
            return $this->getDodgedMessage($action, $dodgedNames);
        }

        return $this->getDamagedMessage($action, $targetNames);
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
        $targetNames = $this->getTargetsName($action);
        $blockedNames = $this->getTargetsBlockedName($action);
        $dodgedNames = $this->getTargetsDodgedName($action);

        if ($targetNames && $blockedNames && $dodgedNames) {
            return
                $this->getDamagedAbilityMessage($action, $targetNames) . '. ' . $this->getBlockedAbilityMessage($action, $blockedNames) . ' ' . $this->getDodgedAbilityMessage($action, $dodgedNames);
        }

        if ($targetNames && $blockedNames) {
            return $this->getDamagedAbilityMessage($action, $targetNames) . '. ' . $this->getBlockedAbilityMessage($action, $blockedNames);
        }

        if ($targetNames && $dodgedNames) {
            return $this->getDamagedAbilityMessage($action, $targetNames) . '. ' . $this->getDodgedAbilityMessage($action, $dodgedNames);
        }

        if ($blockedNames && $dodgedNames) {
            return $this->getBlockedAbilityMessage($action, $blockedNames) . ' ' . $this->getDodgedAbilityMessage($action, $dodgedNames);
        }

        if ($blockedNames) {
            return $this->getBlockedAbilityMessage($action, $blockedNames);
        }

        if ($dodgedNames) {
            return $this->getDodgedAbilityMessage($action, $dodgedNames);
        }

        return $this->getDamagedAbilityMessage($action, $targetNames);
    }

    /**
     * Формирует сообщение о лечении в формате:
     * $unit heal $unit on $power life
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function heal(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s heal %s on %d life'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getTargetsName($action),
            $action->getFactualPower()
        );
    }

    /**
     * Формирует сообщение о использовании лечения со способности. Сообщение будет разным для ситуаций, когда лечение
     * применяется на себя или на другие цели.
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    public function healAbility(ActionInterface $action): string
    {
        if (count($action->getTargetUnits()) === 1) {
            foreach ($action->getTargetUnits() as $targetUnit) {
                if ($action->getActionUnit()->getId() === $targetUnit->getId()) {
                    return $this->healAbilitySelfTarget($action);
                }
            }
        }

        return $this->healAbilityOtherTarget($action);
    }

    /**
     * Формирует сообщение о лечении со способности по другой цели:
     * $unit use $icon $ability and heal $unit on $power life
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function healAbilityOtherTarget(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s use %s %s and heal %s on %d life'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $this->getTargetsName($action),
            $action->getFactualPower()
        );
    }

    /**
     * Формирует сообщение о лечении со способности себя:
     * $unit use $icon $ability and healed itself on $power life
     *
     * @param ActionInterface $action
     * @return string
     */
    private function healAbilitySelfTarget(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s use %s %s and healed itself on %d life'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $action->getFactualPower()
        );
    }

    /**
     * Формирует сообщение о восстановлении маны в формате:
     * $unit restore $unit $power mana
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function manaRestore(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s restore %s %d mana'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getTargetsName($action),
            $action->getFactualPower()
        );
    }

    /**
     * Формирует сообщение о восстановлении маны от способности в формате:
     * $unit use $icon $ability and restore $unit $power mana
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function manaRestoreAbility(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s use %s %s and restore %s %d mana'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $this->getTargetsName($action),
            $action->getFactualPower()
        );
    }

    /**
     * Формирует сообщение о призыве существа в формате:
     * $unit summon $icon $name
     *
     * @param ActionInterface $action
     * @return string
     */
    private function summon(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s summon %s %s'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>'
        );
    }

    /**
     * Формирует сообщение о пропуске хода из-за атаки меньше 1:
     * $unit preparing to attack
     *
     * @param ActionInterface $action
     * @return string
     */
    private function wait(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s preparing to attack'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>'
        );
    }

    /**
     * Формирует сообщение о пропуске хода из-за эффекта паралича:
     * $unit paralyzed and unable to move
     *
     * @param ActionInterface $action
     * @return string
     */
    private function paralysis(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s paralyzed and unable to move'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>'
        );
    }

    /**
     * Формирует сообщение о пропуске хода из-за эффекта оглушения:
     * $unit stunned and unable to move
     *
     * @param ActionInterface $action
     * @return string
     */
    private function stunned(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s stunned and unable to move'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>'
        );
    }

    /**
     * В текущих способностях сам баф не формирует сообщение - его формирует эффект, от которого применяется баф.
     *
     * Но метод оставляется на будущее, т.к. могут появиться способности где такой тип сообщения понадобится
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
     * $unit use $icon $ability and resurrected $targets
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function resurrected(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s use %s %s and resurrected %s'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $this->getTargetsName($action)
        );
    }

    /**
     * Самовоскрешение юнитом себя от расовой способности. Сообщение вида:
     *
     * $unit died, but due to the innate ability $ability came back to life
     * $unit умер, но благодаря врожденной способности $name вернулся к жизни
     *
     * @param ActionInterface $action
     * @return string
     */
    private function selfRaceResurrected(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s died, but due to the innate ability %s %s came back to life'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>'
        );
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
        // Если цель у эффекта одна, и эта цель сам юнит - сообщение нужно формировать по другому
        if ($this->isApplySelf($action)) {

            // $unit use $icon $ability
            return sprintf(
                $this->translation->trans('%s use %s %s'),
                '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
                $this->getIcon($action),
                '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>'
            );
        }

        // $unit use $icon $ability on $targets
        return sprintf(
            $this->translation->trans('%s use %s %s on %s'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $this->getTargetsName($action)
        );
    }

    /**
     * Формирует сообщение урона от эффекта в формате:
     * $unit received $power damage from effect $icon $ability
     *
     * @param ActionInterface $action
     * @return string
     */
    private function effectDamage(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s received %d damage from effect %s %s'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $action->getFactualPower(),
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>'
        );
    }

    /**
     * Формирует сообщение здоровья от эффекта в формате:
     * "$name восстановил $power здоровья от эффекта $effectName"
     *
     * @param ActionInterface $action
     * @return string
     */
    private function effectHeal(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s restored %d life from effect %s %s'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $action->getFactualPower(),
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>'
        );
    }

    /**
     * Формирует сообщение восстановления маны от эффекта в формате:
     * "$name восстановил $power маны от эффекта $effectName"
     *
     * @param ActionInterface $action
     * @return string
     */
    private function effectManaRestore(ActionInterface $action): string
    {
        return sprintf(
            $this->translation->trans('%s restored %d mana from effect %s %s'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $action->getFactualPower(),
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>'
        );
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
        return $action->getIcon() === '' ? '' : '<img src="' . $action->getIcon() . '" alt="" />';
    }

    /**
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getTargetsName(ActionInterface $action): string
    {
        $names = [];

        foreach ($action->getTargetUnits() as $target) {
            if (!$action->isBlocked($target) && !$action->isEvaded($target)) {
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
        $names = [];

        foreach ($action->getTargetUnits() as $target) {
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
        $names = [];

        foreach ($action->getTargetUnits() as $target) {
            if ($action->isEvaded($target)) {
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
     * Можно написать проще:
     *
     * foreach ($action->getTargetUnits() as $targetUnit) {
     *    return $targetUnit->getId() === $action->getActionUnit()->getId();
     * }
     *
     * Но IDE ругается на такой foreach как на ошибку. По этому написан более сложный вариант.
     *
     * @param ActionInterface $action
     * @return bool
     * @throws ActionException
     */
    private function isApplySelf(ActionInterface $action): bool
    {
        if (count($action->getTargetUnits()) > 1) {
            return false;
        }

        $target = null;

        foreach ($action->getTargetUnits() as $targetUnit) {
            $target = $targetUnit;
        }

        return $target->getId() === $action->getActionUnit()->getId();
    }

    /**
     * Формирует сообщение о юнитах, которые получили урон в формате:
     * $unit [critical] hit for $damage damage against $targets
     *
     * @param ActionInterface $action
     * @param string $targetNames
     * @return string
     * @throws ActionException
     */
    private function getDamagedMessage(ActionInterface $action, string $targetNames): string
    {
        if ($action->isCriticalDamage()) {
            $message = '%s critical hit for %d damage against %s';
        } elseif ($action->getRandomDamageMultiplier() > 1.5) {
            $message = '%s hit for %d <i>crushing</i> damage against %s';
        } elseif ($action->getRandomDamageMultiplier() < 0.6) {
            $message = '%s hit for %d <i>unlucky</i> damage against %s';
        } else {
            $message = '%s hit for %d damage against %s';
        }

        return sprintf(
            $this->translation->trans($message),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $action->getFactualPower(),
            $targetNames
        ) . $this->getVampirismMessage($action);
    }

    /**
     * Формирует сообщение о юнитах которые заблокировали удар.
     *
     * $unit tried to strike, but $targets blocked it!
     *
     * @param ActionInterface $action
     * @param string $blockedNames
     * @return string
     */
    private function getBlockedMessage(ActionInterface $action, string $blockedNames): string
    {
        return sprintf(
            $this->translation->trans('%s tried to strike, but %s blocked it!'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $blockedNames
        );
    }

    /**
     * Формирует сообщение о юнитах которые увернулись от удара:
     *
     * $unit tried to strike, but $targets dodged!
     *
     * @param ActionInterface $action
     * @param string $dodgedNames
     * @return string
     */
    private function getDodgedMessage(ActionInterface $action, string $dodgedNames): string
    {
        return sprintf(
            $this->translation->trans('%s tried to strike, but %s dodged!'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $dodgedNames
        );
    }

    /**
     * Формирует сообщение о юнитах, которые получили урон от способности:
     *
     * $unit use $icon $ability and [critical] hit for $damage damage against $targets
     *
     * @param ActionInterface $action
     * @param string $targetNames
     * @return string
     * @throws ActionException
     */
    private function getDamagedAbilityMessage(ActionInterface $action, string $targetNames): string
    {
        $message = $action->isCriticalDamage() ?
            '%s use %s %s and critical hit for %d damage against %s' : '%s use %s %s and hit for %d damage against %s';

        return sprintf(
            $this->translation->trans($message),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $action->getFactualPower(),
            $targetNames
        ) . $this->getVampirismMessage($action);
    }

    /**
     * Формирует сообщение о юнитах которые заблокировали урон от способности:
     *
     * $unit use $icon $ability but $targets blocked it!
     *
     * @param ActionInterface $action
     * @param string $blockedNames
     * @return string
     */
    private function getBlockedAbilityMessage(ActionInterface $action, string $blockedNames): string
    {
        return sprintf(
            $this->translation->trans('%s use %s %s but %s blocked it!'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $blockedNames
        );
    }

    /**
     * Формирует сообщение о юнитах которые уклонились от способности:
     *
     * $unit use $icon $ability but $targets dodged!
     *
     * @param ActionInterface $action
     * @param string $dodgedNames
     * @return string
     */
    private function getDodgedAbilityMessage(ActionInterface $action, string $dodgedNames): string
    {
        return sprintf(
            $this->translation->trans('%s use %s %s but %s dodged!'),
            '<span style="color: ' . $action->getActionUnit()->getRace()->getColor() . '">' . $action->getActionUnit()->getName() . '</span>',
            $this->getIcon($action),
            '<span class="ability">' . $this->translation->trans($action->getNameAction()) . '</span>',
            $dodgedNames
        );
    }

    /**
     * Формирует сообщение о восстановленном здоровье и/или маны от удара, если оно было
     *
     * @param ActionInterface $action
     * @return string
     * @throws ActionException
     */
    private function getVampirismMessage(ActionInterface $action): string
    {
        if ($action->getRestoreLifeFromVampirism() > 0 && $action->getRestoreManaFromMagicVampirism() > 0) {
            return sprintf(
                $this->translation->trans(' and restore %d life and %d mana'),
                $action->getRestoreLifeFromVampirism(),
                $action->getRestoreManaFromMagicVampirism()
            );
        }

        if ($action->getRestoreLifeFromVampirism() > 0) {
            return sprintf(
                $this->translation->trans(' and restore %d life'),
                $action->getRestoreLifeFromVampirism()
            );
        }

        if ($action->getRestoreManaFromMagicVampirism() > 0) {
            return sprintf(
                $this->translation->trans(' and restore %d mana'),
                $action->getRestoreManaFromMagicVampirism()
            );
        }

        return '';
    }
}
