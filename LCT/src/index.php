<?php
include('/var/www/php/sessions.php');
include('/var/www/php/db.php');
include('/var/www/php/getGenState.php');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проекты - Сервис для проектирования зданий на заданной территории</title>
    <link rel="stylesheet" href="/styles/style.css">
</head>

<body>
    <div class="container">
        <div class="projects">
            <h2>Мои проекты</h2>
            <?php
            $pr = $db->fetchProjects($_SESSION['uid']);
            foreach ($pr as $p) {
                syncState($p['id'], $db);
            }
            $pr = $db->fetchProjects($_SESSION['uid']);
            foreach ($pr as $p) {
                echo '<a href="/viewProj?id=' . $p['id'] . '" class="native"><div class="project"><h1>' . $p['name'] . '</h1><h3>' . PrDB::getStatus($p['status']) . '</h3></div></a>';
            }
            ?>
            <a href="/newProj" class="native">
                <button class="uibtn">Создать новый проект</button>
            </a>
        </div>
    </div>
</body>

</html>