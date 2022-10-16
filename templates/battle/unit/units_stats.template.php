<?php

use Battle\Command\CommandInterface;
use Battle\Unit\Offense\OffenseInterface;
use Battle\Unit\UnitInterface;
use Battle\View\ViewException;
use Battle\View\ViewInterface;

if (empty($leftCommand) || !($leftCommand instanceof CommandInterface)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

if (empty($rightCommand) || !($rightCommand instanceof CommandInterface)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

if (empty($this) || !($this instanceof ViewInterface)) {
    throw new ViewException(ViewException::MISSING_VIEW);
}

/** @var UnitInterface $unit */

// TODO При выводе информации одни и те же слова каждый раз запрашиваются в Translation
// TODO Имеет смысл один раз получить их, записать в переменные и далее использовать переменные

?>
<div class="units_stats_box">
    <table class="units_stats">
        <?php foreach (array_merge(iterator_to_array($leftCommand->getUnits()), iterator_to_array($rightCommand->getUnits())) as $unit): ?>
            <tr class="header">
                <td colspan="1" rowspan="4"><p><img src="<?= $unit->getAvatar() ?>" width="90" alt="" /><br /><?= $unit->getName() ?></p></td>
                <td><p><?= $this->getTranslation()->trans('Melee') ?>?</p></td>
                <td><p><?= $this->getTranslation()->trans('Race') ?></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Life') ?>"><img src="/images/battle/stats_icon/life.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Mana') ?>"><img src="/images/battle/stats_icon/mana.png" alt=""></abbr></p></td>
                <td><p><?= $this->getTranslation()->trans('Alive') ?>?</p></td>
                <td><p><?= $this->getTranslation()->trans('Action') ?>?</p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Physical Damage') ?>"><img src="/images/battle/stats_icon/physical_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Fire Damage') ?>"><img src="/images/battle/stats_icon/fire_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Water Damage') ?>"><img src="/images/battle/stats_icon/water_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Air Damage') ?>"><img src="/images/battle/stats_icon/air_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Earth Damage') ?>"><img src="/images/battle/stats_icon/earth_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Life Magic Damage') ?>"><img src="/images/battle/stats_icon/life_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Death Magic Damage') ?>"><img src="/images/battle/stats_icon/death_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Accuracy') ?>"><img src="/images/battle/stats_icon/accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Magic Accuracy') ?>"><img src="/images/battle/stats_icon/magic_accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Chance Critical Damage') ?>"><img src="/images/battle/stats_icon/critical_chance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Critical Damage Multiplier') ?>"><img src="/images/battle/stats_icon/critical_multiplication.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Attack Speed') ?>"><img src="/images/battle/stats_icon/attack_speed.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p><?= ($unit->isMelee() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= $this->getTranslation()->trans($unit->getRace()->getSingleName()) ?></p></td>
                <td><p><?= $unit->getLife() ?>/<?= $unit->getTotalLife() ?></p></td>
                <td><p><?= $unit->getMana() ?>/<?= $unit->getTotalMana() ?></p></td>
                <td><p><?= ($unit->isAlive() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= ($unit->isAction() ? $this->getTranslation()->trans('Yes') : $this->getTranslation()->trans('No')) ?></p></td>
                <td><p><?= $unit->getOffense()->getPhysicalDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getFireDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getWaterDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getAirDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getEarthDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getLifeDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getDeathDamage() ?></p></td>
                <td><p><?= $unit->getOffense()->getAccuracy() ?></p></td>
                <td><p><?= $unit->getOffense()->getMagicAccuracy() ?></p></td>
                <td><p><?= $unit->getOffense()->getCriticalChance() ?>%</p></td>
                <td><p><?= $unit->getOffense()->getCriticalMultiplier() ?>%</p></td>
                <td><p><?= $unit->getOffense()->getAttackSpeed() ?></p></td>
            </tr>
            <tr class="header">
                <td><p><?= $this->getTranslation()->trans('Concentration') ?></p></td>
                <td><p><?= $this->getTranslation()->trans('Rage') ?></p></td>
                <td><p><?= $this->getTranslation()->trans('Level') ?></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Mental Barrier') ?>"><img src="/images/battle/stats_icon/mental_barrier.png" alt=""></abbr></p></td>
                <td><p><?= $this->getTranslation()->trans('Type Damage') ?></p></td>
                <td><p><?= $this->getTranslation()->trans('Weapon Type') ?></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Physical Damage Resistance') ?>"><img src="/images/battle/stats_icon/physical_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Fire Damage Resistance') ?>"><img src="/images/battle/stats_icon/fire_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Water Damage Resistance') ?>"><img src="/images/battle/stats_icon/water_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Air Damage Resistance') ?>"><img src="/images/battle/stats_icon/air_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Earth Damage Resistance') ?>"><img src="/images/battle/stats_icon/earth_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Life Magic Damage Resistance') ?>"><img src="/images/battle/stats_icon/life_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Death Magic Damage Resistance') ?>"><img src="/images/battle/stats_icon/death_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Defense') ?>"><img src="/images/battle/stats_icon/defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Magic Defense') ?>"><img src="/images/battle/stats_icon/magic_defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Block') ?>"><img src="/images/battle/stats_icon/block.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Magic Block') ?>"><img src="/images/battle/stats_icon/magic_block.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $this->getTranslation()->trans('Block Ignoring') ?>"><img src="/images/battle/stats_icon/block_ignore.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p><?= $unit->getConcentration() ?>/<?= $unit::MAX_CONCENTRATION ?></p></td>
                <td><p><?= $unit->getRage() ?>/<?= $unit::MAX_RAGE ?></p></td>
                <td><p><?= $unit->getLevel() ?></p></td>
                <td><p><?= $unit->getDefense()->getMentalBarrier() ?>%</p></td>
                <td><p><?= ($unit->getOffense()->getDamageType() === OffenseInterface::TYPE_ATTACK ? $this->getTranslation()->trans('Attack') : $this->getTranslation()->trans('Spell')) ?></p></td>
                <td><p><?= $this->getTranslation()->trans($unit->getOffense()->getWeaponType()->getName()) ?></p></td>
                <td><p><?= $unit->getDefense()->getPhysicalResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getFireResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getWaterResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getAirResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getEarthResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getLifeResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getDeathResist() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getDefense() ?></p></td>
                <td><p><?= $unit->getDefense()->getMagicDefense() ?></p></td>
                <td><p><?= $unit->getDefense()->getBlock() ?>%</p></td>
                <td><p><?= $unit->getDefense()->getMagicBlock() ?>%</p></td>
                <td><p><?= $unit->getOffense()->getBlockIgnore() ?></p></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>