<?php

/** @var string $title */
/** @var string $date */
/** @var string $contents */ ?>
<div class="container" style="padding: 20px;">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2>📚 confirm page</h2>
    </div>

    <div style="margin-bottom: 20px; display: flex; flex-direction: column; justify-content: start; padding-inline: 10px;">
        <div style="margin-right: 20px;">
            <h3>title</h3>
        </div>
        <div>
            <p><?= htmlspecialchars($title) ?></p>
        </div>
    </div>


    <div style="margin-bottom: 20px; display: flex; flex-direction: column; justify-content: start; padding-inline: 10px;">
        <div style="margin-right: 20px;">
            <h3>date</h3>
        </div>
        <div>
            <p><?= htmlspecialchars($date) ?></p>
        </div>
    </div>

    <div style="margin-bottom: 20px; display: flex; flex-direction: column; justify-content: start; padding-inline: 10px;  ">
        <div style="margin-right: 20px;">
            <h3>contents</h3>
        </div>
        <div>
            <p><?= nl2br(htmlspecialchars($contents)) ?></p>
        </div>
    </div>

    <div style="display: flex ; justify-content: flex-end; padding-inline: 10px;">
        <div style="display: flex; justify-content: flex-end; padding-inline: 10px;">
            <a href="?action=form" class="btn"
                style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none;">back</a>
        </div>

        <div style="display: flex; justify-content: flex-end; padding-inline: 10px;">
            <a href="?action=insert" class="btn"
                style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none;">submit</a>
        </div>
    </div>
</div>