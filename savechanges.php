<?php
include '/var/www/php/sessions.php';
include '/var/www/php/db.php';
include '/var/www/php/projectInfo.php';
include '/var/www/php/api/checkProjectAccess.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST'){
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}
if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
    if (checkProjectAccess($_POST['project_id'], $_SESSION['uid'])){
        saveInfo($_POST, $_POST['project_id']);
        echo json_encode(['success' => true]);
    }
} else {
    die(json_encode(['success' => false, 'message' => 'Не указан ID проекта'], JSON_UNESCAPED_UNICODE));
}
