<?php
function generateProject($project_id, $json=true)
{
    $res = file_get_contents("http://{$_ENV['AI_HOST']}:{$_ENV['AI_PORT']}/start-gen/{$project_id}");
    if ($json) return json_decode($res, true);
    else return $res;
}
