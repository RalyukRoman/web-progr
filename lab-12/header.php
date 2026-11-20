<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav style="
    background-color: #eee; 
    padding: 10px; 
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
">
    <div>
        <a href="index.php">Головна</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            | <a href="users.php">Користувачі</a>
        <?php endif; ?>
    </div>
    
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
                Вітаємо, <strong><?= htmlspecialchars($_SESSION['login']) ?></strong>! 
                <a href="logout.php">Вийти</a>]
        <?php else: ?>
                <a href="login.php">Увійти</a> | 
                <a href="register.php">Реєстрація</a>
        <?php endif; ?>
    </div>
</nav>