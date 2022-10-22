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

// Чтобы каждый раз не делать запрос к Translation, один раз получаем нужный перевод и используем переменные
$meleeTitle = $this->getTranslation()->trans('Melee');
$raceTitle = $this->getTranslation()->trans('Race');
$lifeTitle = $this->getTranslation()->trans('Life');
$mataTitle = $this->getTranslation()->trans('Mana');
$aliveTitle = $this->getTranslation()->trans('Alive');
$actionTitle = $this->getTranslation()->trans('Action');
$physicalDamageTitle = $this->getTranslation()->trans('Physical Damage');
$fireDamageTitle = $this->getTranslation()->trans('Fire Damage');
$waterDamageTitle = $this->getTranslation()->trans('Water Damage');
$airDamageTitle = $this->getTranslation()->trans('Air Damage');
$earthDamageTitle = $this->getTranslation()->trans('Earth Damage');
$lifeDamageTitle = $this->getTranslation()->trans('Life Magic Damage');
$deathDamageTitle = $this->getTranslation()->trans('Death Magic Damage');
$accuracyTitle = $this->getTranslation()->trans('Accuracy');
$magicAccuracyTitle = $this->getTranslation()->trans('Magic Accuracy');
$chanceCriticalDamageTitle = $this->getTranslation()->trans('Chance Critical Damage');
$criticalDamageMultiplierTitle = $this->getTranslation()->trans('Critical Damage Multiplier');
$attackSpeedTitle = $this->getTranslation()->trans('Attack Speed');
$castSpeedTitle = $this->getTranslation()->trans('Cast Speed');
$yesTitle = $this->getTranslation()->trans('Yes');
$noTitle = $this->getTranslation()->trans('No');
$concentrationTitle = $this->getTranslation()->trans('Concentration');
$rageTitle = $this->getTranslation()->trans('Rage');
$levelTitle = $this->getTranslation()->trans('Level');
$mentalBarrierTitle = $this->getTranslation()->trans('Mental Barrier');
$damageTypeTitle = $this->getTranslation()->trans('Damage Type');
$weaponTypeTitle = $this->getTranslation()->trans('Weapon Type');
$physicalDamageResistTitle = $this->getTranslation()->trans('Physical Damage Resistance');
$fireDamageResistTitle = $this->getTranslation()->trans('Fire Damage Resistance');
$waterDamageResistTitle = $this->getTranslation()->trans('Water Damage Resistance');
$airDamageResistTitle = $this->getTranslation()->trans('Air Damage Resistance');
$earthDamageResistTitle = $this->getTranslation()->trans('Earth Damage Resistance');
$lifeDamageResistTitle = $this->getTranslation()->trans('Life Magic Damage Resistance');
$deathDamageResistTitle = $this->getTranslation()->trans('Death Magic Damage Resistance');
$defenseTitle = $this->getTranslation()->trans('Defense');
$magicDefenseTitle = $this->getTranslation()->trans('Magic Defense');
$blockTitle = $this->getTranslation()->trans('Block');
$magicBlockTitle = $this->getTranslation()->trans('Magic Block');
$blockIgnoringTitle = $this->getTranslation()->trans('Block Ignoring');
$vampirismTitle = $this->getTranslation()->trans('Vampirism');

?>
<div class="units_stats_box">
    <table class="units_stats">
        <?php foreach (array_merge(iterator_to_array($leftCommand->getUnits()), iterator_to_array($rightCommand->getUnits())) as $unit): ?>
            <tr class="header">
                <td colspan="1" rowspan="4"><p><img src="<?= $unit->getAvatar() ?>" width="90" alt="" /><br /><?= $unit->getName() ?></p></td>
                <td><p><?= $meleeTitle ?>?</p></td>
                <td><p><?= $raceTitle ?></p></td>
                <td><p><abbr title="<?= $lifeTitle ?>"><img src="/images/battle/stats_icon/life.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $mataTitle ?>"><img src="/images/battle/stats_icon/mana.png" alt=""></abbr></p></td>
                <td><p><?= $aliveTitle ?>?</p></td>
                <td><p><?= $actionTitle ?>?</p></td>
                <td><p><abbr title="<?= $physicalDamageTitle ?>"><img src="/images/battle/stats_icon/physical_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $fireDamageTitle ?>"><img src="/images/battle/stats_icon/fire_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $waterDamageTitle ?>"><img src="/images/battle/stats_icon/water_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $airDamageTitle ?>"><img src="/images/battle/stats_icon/air_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $earthDamageTitle ?>"><img src="/images/battle/stats_icon/earth_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $lifeDamageTitle ?>"><img src="/images/battle/stats_icon/life_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $deathDamageTitle ?>"><img src="/images/battle/stats_icon/death_damage.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $accuracyTitle ?>"><img src="/images/battle/stats_icon/accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $magicAccuracyTitle ?>"><img src="/images/battle/stats_icon/magic_accuracy.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $chanceCriticalDamageTitle ?>"><img src="/images/battle/stats_icon/critical_chance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $criticalDamageMultiplierTitle ?>"><img src="/images/battle/stats_icon/critical_multiplication.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $attackSpeedTitle ?>"><img src="/images/battle/stats_icon/attack_speed.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $attackSpeedTitle ?>"><img src="/images/battle/stats_icon/cast_speed.png" alt=""></abbr></p></td>
            </tr>
            <tr>
                <td><p><?= ($unit->isMelee() ? $yesTitle : $noTitle) ?></p></td>
                <td><p><?= $this->getTranslation()->trans($unit->getRace()->getSingleName()) ?></p></td>
                <td><p><?= $unit->getLife() ?>/<?= $unit->getTotalLife() ?></p></td>
                <td><p><?= $unit->getMana() ?>/<?= $unit->getTotalMana() ?></p></td>
                <td><p><?= ($unit->isAlive() ? $yesTitle : $noTitle) ?></p></td>
                <td><p><?= ($unit->isAction() ? $yesTitle : $noTitle) ?></p></td>
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
                <td><p><?= $unit->getOffense()->getCastSpeed() ?></p></td>
            </tr>
            <tr class="header">
                <td><p><?= $concentrationTitle ?></p></td>
                <td><p><?= $rageTitle ?></p></td>
                <td><p><?= $levelTitle ?></p></td>
                <td><p><abbr title="<?= $mentalBarrierTitle ?>"><img src="/images/battle/stats_icon/mental_barrier.png" alt=""></abbr></p></td>
                <td><p><?= $damageTypeTitle ?></p></td>
                <td><p><?= $weaponTypeTitle ?></p></td>
                <td><p><abbr title="<?= $physicalDamageResistTitle ?>"><img src="/images/battle/stats_icon/physical_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $fireDamageResistTitle ?>"><img src="/images/battle/stats_icon/fire_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $waterDamageResistTitle ?>"><img src="/images/battle/stats_icon/water_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $airDamageResistTitle ?>"><img src="/images/battle/stats_icon/air_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $earthDamageResistTitle ?>"><img src="/images/battle/stats_icon/earth_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $lifeDamageResistTitle ?>"><img src="/images/battle/stats_icon/life_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $deathDamageResistTitle ?>"><img src="/images/battle/stats_icon/death_resistance.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $defenseTitle ?>"><img src="/images/battle/stats_icon/defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $magicDefenseTitle ?>"><img src="/images/battle/stats_icon/magic_defence.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $blockTitle ?>"><img src="/images/battle/stats_icon/block.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $magicBlockTitle ?>"><img src="/images/battle/stats_icon/magic_block.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $blockIgnoringTitle ?>"><img src="/images/battle/stats_icon/block_ignore.png" alt=""></abbr></p></td>
                <td><p><abbr title="<?= $vampirismTitle ?>"><img src="/images/battle/stats_icon/vampirism.png" alt=""></abbr></p></td>
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
                <td><p><?= $unit->getOffense()->getVampire() ?>%</p></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>