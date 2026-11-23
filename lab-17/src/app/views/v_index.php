<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — ЛР 17</title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <p class="eyebrow">Лабораторна робота 17</p>
        <h1><?= e($title) ?></h1>
        <p class="muted">Екземпляри класу <strong><?= e($modelClass) ?></strong>.</p>
        <nav style="display: flex; gap: 1rem;">
            <?php foreach ($entities as $key => $cfg): ?>
                <a href="index.php?entity=<?= e($key) ?>" <?= $key === $entityKey ? 'style="font-weight: bold;"' : '' ?>>
                    <?= e($cfg['title']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>

    <?php if ($items === []): ?>
        <section class="card">
            <h2>Список порожній</h2>
            <p>У таблиці ще немає записів.</p>
        </section>
    <?php else: ?>
        <section class="card table-scroll">
            <table>
                <caption>Кількість записів: <?= count($items) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <?php foreach ($columns as $label): ?>
                            <th scope="col"><?= e($label) ?></th>
                        <?php endforeach; ?>
                        <?php foreach ($belongsToList as $rel): ?>
                            <th scope="col"><?= e($rel['label']) ?></th>
                        <?php endforeach; ?>
                        <th scope="col">Дія</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item->id ?></td>

                        <?php foreach ($columns as $field => $label): ?>
                            <td><?= e((string) ($item->$field ?? '—')) ?></td>
                        <?php endforeach; ?>

                        <?php foreach ($belongsToList as $rel): ?>
                            <?php
                            $method  = $rel['method'];
                            $related = $item->$method();

                            $labelField   = $rel['labelField'];
                            $targetEntity = $rel['targetEntity'];
                            $emptyText    = $rel['emptyText'];
                            ?>

                            <td>
                                <?php if ($related !== null): ?>
                                    <a href="index.php?entity=<?= e($targetEntity) ?>&id=<?= $related->id ?>">
                                        <?= e((string) $related->$labelField) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="muted"><?= e($emptyText) ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <a href="index.php?entity=<?= e($entityKey) ?>&id=<?= $item->id ?>">
                                Відкрити запис <?= $item->id ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>
</body>
</html>