<?php
$dbFile = __DIR__ . '/sqlite.db';

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Помилка підключення до бази даних: " . $e->getMessage();
    exit();
}

$sql = "SELECT id, name, price, description FROM products";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Список товарів</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 32px; 
            background-color: #f9f9f9; 
        }

        th, td { 
            border: 1px solid #ddd;
            padding: 12px;
        }

        table { border-collapse: collapse; }
        
        td { background: #fff; }
        th { background-color: #eee; }
    </style>
</head>

<body>
    <h2>Список товарів із бази даних</h2>

    <?php if (!empty($products)): ?>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Назва товару</th>
                    <th>Ціна</th>
                    <th>Опис</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['price']) ?> грн</td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <h2>Таблиця порожня</h2>
    <?php endif; ?>
</body>
</html>
