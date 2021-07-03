<?php

use Battle\View\ViewException;

if (!isset($leftRangeUnits, $leftMeleeUnits, $rightMeleeUnits, $rightRangeUnits)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

?>
<div class="row">
    <table>
        <tr>
            <td class="w25" id="left_command_range"><?= $leftRangeUnits ?></td>
            <td class="w25" id="left_command_melee"><?= $leftMeleeUnits ?></td>
            <td class="w25" id="right_command_melee"><?= $rightMeleeUnits ?></td>
            <td class="w25" id="right_command_range"><?= $rightRangeUnits ?></td>
        </tr>
    </table>
</div>