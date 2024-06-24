<?php
session_name('prservice_sessid');
session_start();
if (!isset($_SESSION['uid'])){
    http_response_code(401);
    header('Location: /login');
    die();
}