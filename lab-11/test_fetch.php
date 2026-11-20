<?php
require_once 'common.php'; 
$db = connectDatabase();

$sql = 'SELECT id, product_code, product_name, price FROM products ORDER BY id';
$result = mysqli_query($db, $sql);

echo "<h3>1. mysqli_fetch_assoc:</h3>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "Товар: " . $row['product_name'] . " — Цена: " . $row['price'] . "<br>";
}

mysqli_data_seek($result, 0);

echo "<h3>2. mysqli_fetch_row:</h3>";
while ($row = mysqli_fetch_row($result)) {
    echo "Товар: " . $row[2] . " — Цена: " . $row[3] . "<br>";
}

mysqli_data_seek($result, 0);

echo "<h3>3. mysqli_fetch_object:</h3>";
while ($row = mysqli_fetch_object($result)) {
    echo "Товар: " . $row->product_name . " — Цена: " . $row->price . "<br>";
}

mysqli_data_seek($result, 0);

echo "<h3>4. mysqli_fetch_array (MYSQLI_BOTH):</h3>";
while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
    echo "По имени: " . $row['product_name'] . " | По индексу: " . $row[2] . "<br>";
}

mysqli_free_result($result);
mysqli_close($db);
?>
