<?php

/** @var array $diary */ ?>
<form action="/diaries/<?= $diary['id'] ?>/update" method="post">
    <div class="container" style="padding: 20px;">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2>📚 detail page</h2>
        </div>

        <div style="margin-bottom: 20px; display: flex; flex-direction: column; justify-content: start; padding-inline: 10px;">
            <div style="margin-right: 20px;">
                <h3>title</h3>
            </div>
            <div>
                <input type='text' name='title' style="width: 100%; height: 40px;" value="<?= htmlspecialchars($diary['title']) ?>" required />
            </div>
        </div>


        <div style="margin-bottom: 20px; display: flex; flex-direction: column; justify-content: start; padding-inline: 10px;">
            <div style="margin-right: 20px;">
                <h3>date</h3>
            </div>
            <div>
                <input type='date' name='date' style="width: 200px;" value="<?= htmlspecialchars($diary['date']) ?>" required />
            </div>
        </div>

        <div style="margin-bottom: 20px; display: flex; flex-direction: column; justify-content: start; padding-inline: 10px;  ">
            <div style="margin-right: 20px;">
                <h3>contents</h3>
            </div>
            <div>
                <textarea name='contents' style="width: 100%; height: 100px; min-height: 100px; height: 350px;" required><?= htmlspecialchars($diary['contents']) ?></textarea>
            </div>
        </div>



        <div style="display: flex ; justify-content: flex-end; padding-inline: 10px;">
            <div style="display: flex; justify-content: flex-end; padding-inline: 10px;">
                <button type="submit" class="btn"
                    style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none;">update</button>

            </div>
            <div style="display: flex; justify-content: flex-end; padding-inline: 10px;">
                <a href="/diaries/<?= $diary['id'] ?>/delete" class="btn" onclick="return confirm('Are you sure you want to delete this diary entry?');"
                    style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none;">delete</a>

            </div>
            <div style="display: flex; justify-content: flex-end; padding-inline: 10px;">
                <a href="/diaries" class="btn"
                    style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none;">back</a>

            </div>
        </div>
    </div>
</form>