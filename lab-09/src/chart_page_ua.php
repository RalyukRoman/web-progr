<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Дослідження діаграм - СР</title>
</head>

<body>
    <h1>Результати генерації діаграми з файла</h1>
    <div>
        <div>
            <h3>Растрова версія (PNG)</h3>
            <img src="chart_png_ua.php?width=600&amp;height=380" alt="Діаграма PNG" />
        </div>

        <div>
            <h3>Векторна версія (Динамічний SVG)</h3>
            <img src="chart_svg_ua.php?width=600&amp;height=380" alt="Діаграма SVG" />
        </div>
    </div>

    <h2>Вихідні дані</h2>
    <table>
        <thead>
            <tr>
                <th>Категорія ресурсу</th>
                <th>Кількість використання</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $data = json_decode(file_get_contents(__DIR__ . '/chart-data-ua.json'), true);
            
            foreach ($data as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['label']) ?></strong></td>
                    <td><?= number_format($row['value'], 0, ',', ' ') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>