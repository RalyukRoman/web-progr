<?php
define('SECURE_CORE', true);

require __DIR__ . '/common.php';
require __DIR__ . '/calculate_stats.php';

$error = '';
$text = '';
$name = '';

$total_words = 0;
$unique_words_count = 0;
$top_words = [];
$non_empty_lines_count = 0;

$search_query = '';
$search_count = null;

$analyzed_lines = [];

try {
    $path = selectedPath();
    $name = basename($path);
    $text = file_get_contents($path);
    
    if ($text === false) {
        throw new RuntimeException('Не вдалося прочитати файл');
    }

    $stats = calculateTextStats($text);
    $total_words = $stats['total'];
    $unique_words_count = $stats['unique'];
    $top_words = $stats['top'];
    $non_empty_lines_count = $stats['non_empty_lines']; 

    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $all_words = words($text);

    if (isset($_GET['search'])) {
        $search_param = $_GET['search'];

        if (is_string($search_param)) {
            $search_words = words($search_param);

            if (count($search_words) === 1) {
                $search_query = $search_words[0];
                $search_count = 0;

                foreach ($all_words as $w) {
                    if ($w === $search_query) {
                        $search_count++;
                    }
                }
            } else if (trim($search_param) !== '') {
                $error = 'Для пошуку потрібно ввести рівно одне слово';
            }
        }
    }

    $clean_text = preg_replace('/\n\z/', '',  $text);
    $raw_lines = explode("\n", $clean_text);

    foreach ($raw_lines as $index => $line) {
        $char_count = mb_strlen($line, 'UTF-8');
        $line_words_count = count(words($line));

        $analyzed_lines[] = [
            'number' => $index + 1,
            'chars'  => $char_count,
            'words'  => $line_words_count
        ];
    }

} catch (RuntimeException $exception) {
    http_response_code(400);
    $error = $exception->getMessage();
}
?>

<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Аналізатор тексту</title>
</head>
<body>
    <h1>Аналіз тексту</h1>

    <?php if ($error !== ''): ?>
        <p role="alert"><?= e($error) ?></p>
    <?php else: ?>
        <?php if ($name === ''): ?>
            <p>Виберіть файл для аналізу</p>
        <?php else: ?>
            <h3>Статистика слів</h3>
            <?php if ($total_words === 0): ?>
                <p>У тексті файлу взагалі немає слів</p>
            <?php else: ?>
                <ul>
                    <li>Загальна кількість слів: <?= $total_words ?></li>
                    <li>Кількість унікальних слів: <?= $unique_words_count ?></li>
                    <li>Кількість непорожніх рядків: <?= $non_empty_lines_count ?></li>
                </ul>

                <h4>Топ-5 найчастіших слів:</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Слово</th>
                            <th>Частота появи</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_words as $word => $count): ?>
                            <tr>
                                <td><?= e($word) ?></td>
                                <td><?= $count ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Пошук слова у тексті</h3>
            <form method="get" action="analyze.php">
                <input type="hidden" name="file" value="<?= e($name) ?>">
                
                <label for="search">Введіть одне слово:</label>
                <input type="text" id="search" name="search" value="<?= e($_GET['search'] ?? '') ?>" required>
                
                <button type="submit">Шукати</button>
            </form>

            <?php if ($search_count !== null): ?>
                <p>Результат пошуку слова "<?= e($search_query) ?>": 
                    <span class="success">
                        <?= $search_count ?> входжень
                    </span>
                </p>
            <?php endif; ?>

            <h3>Аналіз структури рядків</h3>
            <table>
                <thead>
                    <tr>
                        <th>№ рядка</th>
                        <th>Кількість символів</th>
                        <th>Кількість слів</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analyzed_lines as $line): ?>
                        <tr>
                            <td><?= $line['number'] ?></td>
                            <td><?= $line['chars'] ?></td>
                            <td><?= $line['words'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <br><br>
            <a href="download.php?file=<?= e(rawurlencode($name)) ?>">Завантажити звіт (.txt)</a>
            <br><br>
            <p><a href="read_file.php?file=<?= e(rawurlencode($name)) ?>">До вибраного файлу</a></p>
            <p><a href="read_1.php?file=<?= e(rawurlencode($name)) ?>">Прочитати порядково</a></p>
        <?php endif; ?>
    <?php endif; ?>

    <p>
        <a href="write_1.php">Нова нотатка</a> 
        · 
        <a href="upload.php">Імпорт</a>
        · 
        <a href="archive.php">Архів ідей</a>
    </p>
</body>
</html>
