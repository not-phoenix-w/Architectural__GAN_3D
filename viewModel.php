<?php
include '/var/www/php/sessions.php';
include '/var/www/php/db.php';
include '/var/www/php/projectInfo.php';
include '/var/www/php/reloader.php';

if (!isset($_GET['id'])) {
    http_response_code(302);
    header('Location: /');
    die();
}
if (!PrDB::isValidUuid($_GET['id'])) {
    http_response_code(400);
    header('Location: /');
    die('Неверно указан ID проекта');
}
if (!$db->checkProjectOwner($_GET['id'], $_SESSION['uid'])) {
    http_response_code(403);
    die('У вас нет прав для просмотра этого проекта.');
}
$dir = '/projects/' . $_GET['id'];
$prinfo = simplexml_load_file($dir . '/project.xml');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр модели</title>
    <link rel="stylesheet" href="/styles/style.css">
    <script src='https://unpkg.com/three@0.99.0/build/three.min.js'></script>
    <script src='https://unpkg.com/three@0.99.0/examples/js/loaders/OBJLoader.js'></script>
    <script src='https://unpkg.com/three@0.99.0/examples/js/controls/OrbitControls.js'></script>
</head>

<body>
    <div class="container">
        <script>
            var scene = new THREE.Scene();
            var container = document.querySelector('.container');
            scene.background = new THREE.Color(0xffffff);
            var camera = new THREE.PerspectiveCamera(75, container.innerWidth / 600, 0.1, 1000);
            camera.position.z = 5;
            var renderer = new THREE.WebGLRenderer();
            renderer.setSize(container.innerWidth, 600);
            var controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true; // плавное движение
            controls.dampingFactor = 0.25; // настройка плавности движения
            controls.enableZoom = true; // возможность зума
            container.appendChild(renderer.domElement);
            var loader = new THREE.OBJLoader();
            loader.load(
                '/api/getObj?project_id=<?php echo $_GET['id']; ?>&var=1',
                function(object) {
                    console.log(object);
                    scene.add(object);
                }
            );

            function animate() {
                requestAnimationFrame(animate);
                renderer.render(scene, camera);
            }
            animate();
        </script>
        <h2>Технико-экономические параметры</h2>
        <table>
            <tr>
                <td>Площадь здания</td>
                <td><?php echo $prinfo->buildingarea; ?> тыс. кв. м</td>
            </tr>
            <tr>
                <td>Количество мест</td>
                <td><?php echo $prinfo->places; ?></td>
            </tr>
            <tr>
                <td>Площадь ЗУ</td>
                <td><?php echo $prinfo->area; ?> Га</td>
            </tr>
            <tr>
                <td>Высота здания</td>
                <td><?php echo $prinfo->height; ?> м</td>
            </tr>
            <tr>
                <td>Количество этажей</td>
                <td><?php echo $prinfo->floors; ?></td>
            </tr>
        </table>
        <a href="/api/getObj?project_id=<?php echo $_GET['id']; ?>&var=1" download="download"><button class="uibtn">Скачать</button> </a>
    </div>
</body>

</html>