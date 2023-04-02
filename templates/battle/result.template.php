<?php

// TODO Rename to battle.template.php

use Battle\Response\ResponseInterface;
use Battle\View\ViewException;

if (!isset($response) || !($response instanceof ResponseInterface)) {
    throw new ViewException(ViewException::MISSING_RESULT);
}

?>

<p class="ptitle">
    <?= $response->getTranslation()->trans('Round') ?> #<span id="num_step">1</span>,
    <?= $response->getTranslation()->trans('Stroke') ?> #<span id="num_attack">1</span>
</p>

<?= $this->renderCommandView($response->getStartLeftCommand(), $response->getStartRightCommand()) ?>

<div class="com_container">
    <div class="com_content" id="comment">
        <?php foreach ($response->getChat()->getMessages() as $message): ?>
            <?= '<p class="none">' . $message . '</p>' ?>
        <?php endforeach; ?>
    </div>
</div>

<script> window.scenario = [
        <?= $response->getScenario()->getJson() ?>
    ];
    document.addEventListener("DOMContentLoaded", function() {
        goScenario();
    });
</script>

<div class="button_box">
    <div class="button_row">
        <div class="battle_button" onclick="showBattleLog([
            '<?= $response->getTranslation()->trans('Show Battle Log') ?>',
            '<?= $response->getTranslation()->trans('Hidden Battle Log') ?>'])" id="battle_log_button">
            <?= $response->getTranslation()->trans('Show Battle Log') ?>
        </div>
    </div>
    <div class="button_row">
        <div class="battle_button" onclick="showBattleStatistic([
            '<?= $response->getTranslation()->trans('Show Battle Statistic') ?>',
            '<?= $response->getTranslation()->trans('Hidden Battle Statistic') ?>'])" id="battle_statistic_button">
            <?= $response->getTranslation()->trans('Show Battle Statistic') ?>
        </div>
    </div>
</div>

<div class="spoiler_cont" id="battle_statistic">
    <div class="statistics_box">
        <table class="statistics">
            <tr class="header">
                <td colspan="11"><p><?= $response->getTranslation()->trans('Statistics') ?></p></td>
            </tr>
            <tr class="header">
                <td><p><span class="stat_unit"><?= $response->getTranslation()->trans('Unit') ?></span></p></td>
                <td><p><span class="stat_damage"><?= $response->getTranslation()->trans('Hits') ?></span></p></td>
                <td><p><span class="stat_damage"><?= $response->getTranslation()->trans('Critical Hits') ?></span></p></td>
                <td><p><span class="stat_damage"><?= $response->getTranslation()->trans('Caused Damage') ?></span></p></td>
                <td><p><span class="stat_taken"><?= $response->getTranslation()->trans('Taken Damage') ?></span></p></td>
                <td><p><span class="stat_taken"><?= $response->getTranslation()->trans('Blocked') ?></span></p></td>
                <td><p><span class="stat_taken"><?= $response->getTranslation()->trans('Dodged') ?></span></p></td>
                <td><p><span class="stat_heal"><?= $response->getTranslation()->trans('Heal') ?></span></p></td>
                <td><p><span class="stat_kill"><?= $response->getTranslation()->trans('Killing') ?></span></p></td>
                <td><p><span class="stat_kill"><?= $response->getTranslation()->trans('Summons') ?></span></p></td>
                <td><p><span class="stat_kill"><?= $response->getTranslation()->trans('Resurrections') ?></span></p></td>
            </tr>
            <?php foreach ($response->getStatistic()->getUnitsStatistics() as $unit): ?>
                <tr>
                    <td>
                        <p>
                            <img src="<?= $unit->getUnit()->getAvatar() ?>" class="stat_ava" alt="" />
                            <span class="stat_unit"><?= $unit->getUnit()->getName() ?></span>
                            <img src="/images/icons/damage.png" width="21" height="23" class="stat_damage" alt="" />
                            <?= $unit->getUnit()->getOffense()->getDPS() ?>
                            <img src="/images/icons/life.png" width="21" height="19" class="stat_damage" alt="" />
                            <?= $unit->getUnit()->getTotalLife() ?>
                        </p>
                    </td>
                    <td><p><span class="stat_damage"><?= $unit->getHits() ?></span></p></td>
                    <td><p><span class="stat_damage"><?= $unit->getCriticalHits() ?></span></p></td>
                    <td><p><span class="stat_damage"><?= $unit->getCausedDamage() ?></span></p></td>
                    <td><p><span class="stat_taken"><?= $unit->getTakenDamage() ?></span></p></td>
                    <td><p><span class="stat_taken"><?= $unit->getBlockedHits() ?></span></p></td>
                    <td><p><span class="stat_taken"><?= $unit->getDodgedHits() ?></span></p></td>
                    <td><p><span class="stat_heal"><?= $unit->getHeal() ?></span></p></td>
                    <td><p><span class="stat_kill"><?= $unit->getKilling() ?></span></p></td>
                    <td><p><span class="stat_kill"><?= $unit->getSummons() ?></span></p></td>
                    <td><p><span class="stat_kill"><?= $unit->getResurrections() ?></span></p></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td><p class="right"><?= $response->getTranslation()->trans('Total rounds') ?>:</p></td>
                <td colspan="10"><p class="left"><?= $response->getStatistic()->getRoundNumber() ?></p></td>
            </tr>
            <tr>
                <td><p class="right"><?= $response->getTranslation()->trans('Total stroke') ?>:</p></td>
                <td colspan="10"><p class="left"><?= $response->getStatistic()->getStrokeNumber() ?></p></td>
            </tr>
            <tr>
                <td><p class="right"><?= $response->getTranslation()->trans('Runtime') ?>:</p></td>
                <td colspan="10"><p class="left"><?= $response->getStatistic()->getRuntime() ?> ms</p></td>
            </tr>
            <tr>
                <td><p class="right"><?= $response->getTranslation()->trans('Memory cost') ?>:</p></td>
                <td colspan="10"><p class="left"><?= $response->getStatistic()->getMemoryCostClipped() ?></p></td>
            </tr>
        </table>
    </div>
</div>

<div class="spoiler_cont" id="battle_log">
    <div class="full_log">

        <?php foreach ($response->getFullLog()->getLog() as $row): ?>
            <?= $row ?>
        <?php endforeach; ?>

        <h1><?= $response->getTranslation()->trans($response->getWinnerText()) ?></h1>
    </div>
</div>
