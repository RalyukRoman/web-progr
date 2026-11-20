<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Публікації — ЛР 14</title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <p class="eyebrow">Лабораторна робота 14 · Модель і представлення</p>
        <h1>Публікації</h1>
        <p class="muted">Список екземплярів моделі Post.</p>
    </header>
    <?php if ($posts === []): ?>
        <section class="card"><h2>Список порожній</h2><p>У навчальній базі ще немає публікацій.</p></section>
    <?php else: ?>
        <section class="card table-scroll">
            <table>
                <caption>Кількість публікацій: <?= count($posts) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Заголовок</th>
                        <th scope="col">Перегляди</th>
                        <th scope="col">Перегляд</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($posts as $item): ?>
                    <tr>
                        <td><?= $item->id ?></td>
                        <td><?= e($item->title) ?></td>
                        <td><?= $item->views ?></td>
                        <td><a href="index.php?entity=posts&id=<?= $item->id ?>" aria-label="<?= e('Переглянути: ' . $item->title) ?>">Відкрити</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
    <footer class="muted"><a href="index.php">Перейти до списку користувачів</a></footer>
</main>
</body>
</html>
