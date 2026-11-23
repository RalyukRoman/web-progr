<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Картка запису <?= e($title) ?> — ЛР 17</title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<main class="container narrow">
    <header class="page-header">
        <p class="eyebrow">Лабораторна робота 17 · Екземпляр <?= e($modelClass) ?></p>
        <h1>Картка запису: <?= e($title) ?></h1>
        <a href="index.php?entity=<?= e($entityKey) ?>">← До списку</a>
    </header>

    <?php if ($item !== null): ?>
        <article class="card profile">
            <span class="badge">ID <?= $item->id ?> · Клас: <?= e($modelClass) ?></span>
            <dl>
                <?php foreach ($columns as $field => $label): ?>
                    <dt><?= e($label) ?></dt>
                    <dd><?= e((string) ($item->$field ?? '—')) ?></dd>
                <?php endforeach; ?>

                <?php foreach ($belongsToList as $rel): ?>
                    <?php
                    $method  = $rel['method'];
                    $related = $item->$method();

                    $labelField   = $rel['labelField'];
                    $targetEntity = $rel['targetEntity'];
                    $emptyText    = $rel['emptyText'];
                    ?>

                    <dt><?= e($rel['label']) ?></dt>
                    <dd>
                        <?php if ($related !== null): ?>
                            <a href="index.php?entity=<?= e($targetEntity) ?>&id=<?= $related->id ?>">
                                <?= e((string) $related->$labelField) ?>
                            </a>
                        <?php else: ?>
                            <span class="muted"><?= e($emptyText) ?></span>
                        <?php endif; ?>
                    </dd>
                <?php endforeach; ?>
            </dl>
        </article>

        <?php foreach ($hasManyList as $rel): ?>
            <?php
            $method   = $rel['method'];
            $children = $item->$method();

            $title        = $rel['title'];
            $labelField   = $rel['labelField'];
            $targetEntity = $rel['targetEntity'];
            $emptyText    = $rel['emptyText'];
            ?>

            <section class="card" style="margin-top: 1.5rem;">
                <h2><?= e($title) ?> (<?= count($children) ?>)</h2>
                <?php if ($children === []): ?>
                    <p class="muted"><?= e($emptyText) ?></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($children as $child): ?>
                            <li>
                                <a href="index.php?entity=<?= e($targetEntity) ?>&id=<?= $child->id ?>">
                                    <?= e((string) $child->$labelField) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="notice">Запис не знайдено.</p>
    <?php endif; ?>
</main>
</body>
</html>
