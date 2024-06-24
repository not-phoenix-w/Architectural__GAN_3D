<?php
include '/var/www/php/sessions.php';
include '/var/www/php/db.php';
include '/var/www/php/projectInfo.php';
include '/var/www/php/api/checkProjectAccess.php';

if ($_SERVER['REQUEST_METHOD'] != 'GET'){
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}
if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
    if (checkProjectAccess($_GET['project_id'], $_SESSION['uid'])){
        if (!isset($_GET['var'])) $_GET['var'] = 1;
        if (!is_numeric($_GET['var'])) die(json_encode(['success' => false, 'message' => 'Некорректный вариант проекта'], JSON_UNESCAPED_UNICODE));
        $dir = '/projects/'.$_GET['project_id'].'/';
        $file = $dir.'var'.$_GET['var'].'.obj';
        if (!is_file($file)) die(json_encode(['success' => false, 'message' => 'Вариант не найден. Убедитесь, что вы указали его правильно и проект был сгенерирован успешно.'], JSON_UNESCAPED_UNICODE));
        header('Content-Type: model/obj');
        echo file_get_contents($file);
    }
} else {
    die(json_encode(['success' => false, 'message' => 'Не указан ID проекта'], JSON_UNESCAPED_UNICODE));
}
