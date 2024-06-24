<?php

function getGenerationState($project_id, $json = true)
{
    $res = file_get_contents("http://{$_ENV['AI_HOST']}:{$_ENV['AI_PORT']}/get-status/{$project_id}");
    if ($json) return json_decode($res, true);
    else return $res;
}

function syncState($project_id, $db)
{
    $r = getGenerationState($project_id);
    switch ($r) {
        case 'ready':
            $db->updateStatus(2);
            break;
        case 'process':
            $db->updateStatus(1);
            break;
        case 'error':
            $db->updateStatus(-1);
            break;
    }
}
