<?php
session_name('prservice_sessid');
session_start();
include('/var/www/php/db.php');
$msg = '';
if (isset($_POST['username']) && !empty($_POST['username'])) {
    $cred = $db->login($_POST['username'], $_POST['password']);
    if (!$cred['success']) {
        $msg = '<p class="errmsg">' . $cred['message'] . '</p>';
    } else {
        $_SESSION['name'] = $cred['name'];
        $_SESSION['org'] = $cred['org'];
        $_SESSION['uid'] = $cred['uid'];
        header('Location: /');
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/style.css">
    <title>Вход - Сервис для проектирования зданий на заданной территории</title>
</head>

<body>
    <div class="container" style="max-width: 500px;">
        <form method="post">
            <h3>Войти в личный кабинет</h3>
            <input class="gray input" type="text" name="username" required placeholder="Логин">
            <input class="gray input" type="password" name="password" required placeholder="Пароль">
            <?php echo $msg; ?>
            <button class="uibtn" type="submit">Войти</button>
        </form>
    </div>
</body>

</html>