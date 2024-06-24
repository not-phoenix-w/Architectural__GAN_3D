<?php
function reload($msg){
    http_response_code(302);
    header('Location: '.$_SERVER['REQUEST_URI']);
    die($msg);
}