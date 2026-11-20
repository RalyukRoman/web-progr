<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Власний блог</title>
</head>
<body>
<h1>Публікації блогу</h1>

<form method="get">
    <label>ID допису: <input type="number" name="id" min="1" required></label>
    <button type="submit">Знайти</button>
</form>

<?php if ($error !== ''): ?>
    <p style="color: red;"><?= escape($error) ?></p>
<?php else: ?>
    <h2>Усі дописи</h2>
    <ul>
    <?php foreach ($rows as $post): ?>
        <li>
            <strong><?= escape($post->getLabel()) ?></strong><br>
            <small>Переглядів: <?= (int) $post->views ?></small><br>
            <em>Анонс:</em> <?= escape($post->getSnippet(70)) ?>
        </li>
    <?php endforeach; ?>
    </ul>

    <?php if ($rows === []): ?>
        <p>Блог порожній.</p>
    <?php endif; ?>

    <?php if ($hasSearch): ?>
        <hr>
        <h2>Результат пошуку</h2>
        <?php if ($found === false): ?>
            <p>Запис не знайдено.</p>
        <?php else: ?>
            <article>
                <h3><?= escape($found->getLabel()) ?></h3>
                <p><strong>Переглядів:</strong> <?= (int) $found->views ?></p>
                <p><?= nl2br(escape($found->content)) ?></p>
            </article>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>