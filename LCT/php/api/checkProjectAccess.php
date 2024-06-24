<?php
function checkProjectAccess($project_id, $user_id){
    if (!PrDB::isValidUuid($project_id)) die(json_encode(['success' => false, 'message' => 'Некорректный ID проекта'], JSON_UNESCAPED_UNICODE));
    if (!$GLOBALS['db']->checkProjectOwner($project_id, $user_id)) die(json_encode(['success' => false, 'message' => 'У вас нет доступа к этому проекту'], JSON_UNESCAPED_UNICODE));
    return true;
}