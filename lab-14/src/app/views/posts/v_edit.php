<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагування публікації — ЛР 14</title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<main class="container narrow">
    <header class="page-header">
        <p class="eyebrow">Лабораторна робота 14 · Зміна даних</p>
        <h1>Редагування публікації</h1>
        <a href="index.php?entity=posts&id=<?= $post->id ?>">← Назад до картки</a>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="notice">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="card" method="post" action="index.php?entity=posts&action=edit&id=<?= $post->id ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <div style="display:flex; gap: 24px;">
            <label>Заголовок</label>
            <input type="text" name="title" value="<?= e($formData['title']) ?>" maxlength="150" required>
        </div>
        <br>
        <div style="display:flex; gap: 24px;">         
            <label>Текст публікації</label>
            <textarea name="content" rows="6" required><?= e($formData['content']) ?></textarea>
        </div>
        <br>
        <button type="submit">Зберегти зміни</button>
    </form>

    <form class="card" method="post" action="index.php?entity=posts&action=delete&id=<?= $post->id ?>" onsubmit="return confirm('Видалити цю публікацію?');">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <button type="submit">Видалити запис</button>
    </form>
</main>
</body>
</html>