<?php
/**
 * @var $slots
 */

?>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/slot/create" class="pure-button">Добавить слот</a>
    </div>
    <div class="pure-u-1">
        <div class="pure-g">
            <?php foreach ($slots as $slotId => $slot): ?>
                <div class="pure-u-1-2">
                    #<?=$slotId ?>: <?= $slot['name']; ?>
                </div>
                <div class="pure-u-1-2">
                    <?= $slot['type']; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
