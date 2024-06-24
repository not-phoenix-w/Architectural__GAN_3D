<?php
include '/var/www/php/sessions.php';
include '/var/www/php/db.php';
include '/var/www/php/api/checkProjectAccess.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}
if (isset($_POST['project_id']) && !empty($_POST['project_id']) && isset($_POST['step']) && !empty($_POST['step'])) {
    if (checkProjectAccess($_POST['project_id'], $_SESSION['uid'])) {
        $res = $db->updateStep($_POST['project_id'], $_POST['step']);
        if ($res) die(json_encode(['success' => true]));
        else die(json_encode(['success' => false, 'message' => 'Ошибка при сохранении']));
    }
} else {
    die(json_encode(['success' => false, 'message' => 'Не указаны необходимые параметры'], JSON_UNESCAPED_UNICODE));
}
