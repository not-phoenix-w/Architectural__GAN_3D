<?php
class PrDB extends mysqli
{
    function __construct()
    {
        $this->connect($_ENV['MYSQL_HOST'], $_ENV['MYSQL_LOGIN'], '3k$hUQ0#+JvR()L', $_ENV['MYSQL_DB']);
    }
    function login($login, $password)
    {
        $stmt = $this->prepare('SELECT name, organization, id FROM users WHERE login = ? AND password = ?');
        $ress = $stmt->execute([$login, hash('sha256', $password)]);
        if (!$ress) {
            return ['success' => false, 'message' => 'Произошла ошибка. Пожалуйста, попробуйте позже.'];
        } else {
            $r = $stmt->get_result();
            if ($r->num_rows > 0) {
                $res = $r->fetch_assoc();
                return ['success' => true, 'name' => $res['name'], 'org' => $res['organization'], 'uid' => $res['id']];
            } else return ['success' => false, 'message' => 'Неправильный логин или пароль'];
        }
    }
    static function getStatus($status){
        switch ($status){
            case 0:
                return 'Новый проект';
            case 1:
                return 'Идёт генерация...';
            case 2:
                return 'Генерация завершена';
            case -1:
                return 'Ошибка генерации';
        }
    }
    function fetchProjects($userid)
    {
        $stmt = $this->prepare('SELECT id, name, status FROM projects WHERE owner = ?');
        $ress = $stmt->execute([$userid]);
        if (!$ress) {
            return false;
        } else {
            $r = $stmt->get_result();
            return $r->fetch_all(MYSQLI_ASSOC);
        }
    }
    function getProject($id){
        $stmt = $this->prepare('SELECT id, name, status, owner, step FROM projects WHERE id = ?');
        $ress = $stmt->execute([$id]);
        if (!$ress) {
            return false;
        } else {
            $r = $stmt->get_result();
            if ($r->num_rows == 0) return false;
            return $r->fetch_assoc();
        }
    }

    static function isValidUuid($uuid) {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    } 

    function checkProjectOwner($id, $userid){
        $stmt = $this->prepare('SELECT owner FROM projects WHERE id = ?');
        $ress = $stmt->execute([$id]);
        if (!$ress) {
            return false;
        } else {
            $r = $stmt->get_result();
            if ($r->num_rows == 0) return false;
            return $r->fetch_assoc()['owner'] == $userid;
        }
    }

    function createProject($owner, $name){
        $uuid = $this->query('SELECT UUID();')->fetch_column();
        $stmt = $this->prepare('INSERT INTO projects (owner, name, id) VALUES (?, ?, ?);');
        $stmt->execute([$owner, $name, $uuid]);
        mkdir('/projects/'.$uuid);
        $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><project></project>');
        $xml->addChild('name', $name);
        $xml->asXML('/projects/'.$uuid.'/project.xml');
        return $uuid;
    }

    function updateStep($project_id, $step){
        $stmt = $this->prepare('UPDATE projects SET step = ? WHERE id = ?;');
        return $stmt->execute([$step, $project_id]);
    }

    function updateStatus($project_id, $status){
        $stmt = $this->prepare('UPDATE projects SET status = ? WHERE id = ?;');
        return $stmt->execute([$status, $project_id]);
    }
}

$db = new PrDB();
