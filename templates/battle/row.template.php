<?php

use Battle\View\ViewException;

if (!isset($leftRangeUnits, $leftMeleeUnits, $rightMeleeUnits, $rightRangeUnits)) {
    throw new ViewException(ViewException::MISSING_COMMAND);
}

?>
<div class="row">
    <table>
        <tr>
            <td class="w25"><?= $leftRangeUnits ?></td>
            <td class="w25"><?= $leftMeleeUnits ?></td>
            <td class="w25"><?= $rightMeleeUnits ?></td>
            <td class="w25"><?= $rightRangeUnits ?></td>
        </tr>
    </table>
</div>