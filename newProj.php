<?php
include '/var/www/php/sessions.php';
include '/var/www/php/db.php';
if (isset($_POST['prname']) & !empty($_POST['prname'])) {
    $prid = $db->createProject($_SESSION['uid'], $_POST['prname']);
    http_response_code(302);
    header('Location: /viewProj?id=' . $prid);
    die();
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/style.css">
    <title>Новый проект</title>
</head>

<body>
    <div class="container">
        <form method="post">
            <p>Создание проекта</p>
            <input type="text" class="input white" required name="prname" placeholder="Название проекта">
            <button class="uibtn" type="submit">Создать</button>
        </form>
    </div>
</body>

</html>