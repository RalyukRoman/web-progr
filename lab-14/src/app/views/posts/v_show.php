<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Картка публікації — ЛР 14</title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<main class="container narrow">
    <header class="page-header">
        <p class="eyebrow">Лабораторна робота 14 · Екземпляр моделі</p>
        <h1>Картка публікації</h1>
        <a href="index.php?entity=posts">← До списку</a>
    </header>
    <?php if ($post !== null): ?>
        <article class="card profile">
            <span class="badge">ID <?= $post->id ?></span>
            <h2><?= e($post->title) ?></h2>
            <dl>
                <dt>Кількість переглядів</dt>
                <dd><?= $post->views ?></dd>
                <dt>Текст публікації</dt>
                <dd><?= nl2br(e($post->content)) ?></dd>
            </dl>
            <?php if ($canEdit): ?>
                <p><a href="index.php?entity=posts&action=edit&id=<?= $post->id ?>">Редагувати або видалити</a></p>
            <?php endif; ?>
        </article>
    <?php else: ?>
        <p class="notice">Публікацію не знайдено.</p>
    <?php endif; ?>
</main>
</body>
</html>