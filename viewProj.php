<?php
include '/var/www/php/sessions.php';
include '/var/www/php/db.php';
include '/var/www/php/projectInfo.php';
include '/var/www/php/reloader.php';
include '/var/www/php/startProjectGenerating.php';
include '/var/www/php/getGenState.php';

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
if (isset($_FILES['geopodosnova']) || isset($_FILES['mr-version'])) {
    if (isset($_FILES['mr-version'])) {
        $upst = 'Cannot upload file';
        if ($_FILES['mr-version']['type'] == 'text/xml') {
            saveInfoFromXML($_FILES['mr-version']['tmp_name'], $_GET['id']);
            $upst = 'Uploaded successful';
        }
    }
    if (isset($_FILES['geopodosnova'])) {
        $upst = 'Cannot upload file';
        //if ($_FILES['geopodosnova']['type'] == 'image/vnd.dwg'){
        move_uploaded_file($_FILES['geopodosnova']['tmp_name'], $dir . '/geopodosnova.dwg');
        $upst = 'Uploaded successful';
        /*}*/
    }
    if (!isset($_POST['generate']) || $_POST['generate'] != 'true') reload($upst);
}
$errormsg = '';
if (isset($_POST['generate']) && $_POST['generate'] == 'true') {
    saveInfo($_POST, $_GET['id']);
    if (checkProjectInfo($_GET['id'])) {
        $g = generateProject($_GET['id']);
        if ($g['success']) {
            $db->updateStatus($_GET['id'], 1);
        } else {
            $errormsg = '<p class="errmsg">Неизвестная ошибка при генерации</p>';
        }
    } else {
        $errormsg = '<p class="errmsg">Заполнены не все поля</p>';
    }
}

$proj = $db->getProject($_GET['id']);
if ($proj['status'] != 0) {
    syncState($_GET['id'], $db);
    $proj = $db->getProject($_GET['id']);
}

if ($proj['status'] == 2){
    http_response_code(302);
    header('Location: /viewModel?id='.$_GET['id']);
    die();
}

$geopodosn_state = '(не загружена)';
if (is_file($dir . '/geopodosnova.dwg')) {
    $geopodosn_state = '(загружена, MD5: ' . md5_file($dir . '/geopodosnova.dwg') . ')';
}

$prinfo = simplexml_load_file($dir . '/project.xml');
$coords = [];
if (isset($prinfo->coordinates[0])) {
    foreach ($prinfo->coordinates[0]->coord as $c) {
        $coords[] = $c[0];
    }
}
$coordsjs = preg_replace("/\r/", '', implode('/n', $coords));
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проект «<?php echo $prinfo->name; ?>»</title>
    <link rel="stylesheet" href="/styles/style.css">
    <style>
        #mapcontainer {
            width: 100%;
            height: 600px;
        }

        .minpb {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
            max-width: 810px;
            gap: 10px;
            margin-bottom: 10px;
        }

        .minpb .input {
            margin: 0;
            width: 400px;
        }

        @media (max-width: 930px) {
            .minpb .input {
                width: 100%;
            }
        }
    </style>
    <script>
        var prid = '<?php echo $_GET['id']; ?>';

        function step(num) {
            if (!(num >= 1 & num <= 3)) return;
            for (var i = 1; i < 4; i++) {
                document.getElementById('step' + i).style.display = 'none';
            }
            document.getElementById('step' + num).style.display = '';
            saveStep(num);
        }

        onload = function() {
            var inputs = document.querySelectorAll('input:not([type=file]), textarea');
            inputs.forEach(function(element) {
                element.onchange = sync;
            });
            step(<?php echo $proj['step']; ?>);
        };

        function saveStep(step) {
            var fd = new FormData();
            fd.append('project_id', prid);
            fd.append('step', step);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/savestep');
            xhr.send(fd);
        }

        function sync() {
            var inputs = document.querySelectorAll('input:not([type=file]), textarea');
            var fd = new FormData();
            fd.append('project_id', prid);
            inputs.forEach(function(element) {
                if (element.value) {
                    fd.append(element.name, element.value);
                }
            });
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/savechanges');
            xhr.send(fd);
        }

        function submit() {
            form.submit();
        }
    </script>
</head>

<body>
    <div class="container" style="height: max-content;">
        <?php
        if ($proj['status'] == 1) echo '
        <div>
            <p>Идёт генерация проекта...</p>
            <p>Вы можете закрыть страницу. Готовый проект отобразится в личном кабинете</p>
        </div>
        ';

        if ($proj['status'] == -1) echo '
        <div>
            <p class="errmsg">Произошла ошибка при генерации проекта: ' . getGenerationState($_GET['id'])['message'] . '</p>
        </div>
        ';

        ?>
        <form id="form" method="post" <?php if ($proj['status'] == 1) echo 'style="display: none;"'; ?> enctype="multipart/form-data">
            <?php echo $errormsg; ?>
            <div id="step1">
                <p>Шаг 1/3. Название проекта</p>
                <input type="text" class="white input" placeholder="Название проекта" name="name" value="<?php echo $prinfo->name; ?>">
                <p>или</p>
                <label class="fileinp uibtn gray mwdt" for="mrversion">Заполнить из XML-файла</label>
                <input type="file" class="fileinp" onchange="submit()" name="mr-version" id="mrversion" accept=".xml">
                <button class="uibtn mwdt nm" onclick="step(2)">Дальше</button>
            </div>
            <div id="step2" style="display: none;">
                <p>Шаг 2/3. Границы проекта</p>
                <div id="mapcontainer"></div>
                <button class="uibtn gray minwdt" id="clear">Очистить карту</button>
                <div style="display: flex; gap: 10px;">
                    <button class="uibtn gray nm" onclick="step(1)">Назад</button>
                    <button class="uibtn nm" onclick="step(3)">Дальше</button>
                </div>
                <input type="hidden" name="coordinates" id="coordinates_inp">
                <script src="https://mapgl.2gis.com/api/js/v1?callback=initMap" async defer></script>
                <script>
                    var polygon = [];
                    var map;
                    var coords_ish = '<?php echo $coordsjs; ?>';

                    function initMap() {
                        map = new mapgl.Map('mapcontainer', {
                            key: '<?php echo $_ENV['TWOGIS_KEY']; ?>',
                            center: [37.617698, 55.755864],
                            zoom: 12,
                        });
                        setPolygonFromStr(coords_ish);
                        setCoord();
                        map.on('click', function(e) {
                            addPointToPolygon(e.lngLat);
                            drawPolygon();
                            setCoord();
                        });

                        for (var j = 0; j < form.elements.length; j++) {
                            var element = form.elements[j];
                            if (element.tagName === 'BUTTON') {
                                if (!element.hasAttribute('type')) {
                                    element.setAttribute('type', 'button');
                                }
                            }
                        }
                    }

                    function setPolygonFromStr(str) {
                        var s = str.split('/n');
                        polygon = [];
                        s.forEach(element => {
                            polygon.push(element.split(', '));
                        });
                        drawPolygon();
                    }

                    function setCoord() {
                        var vals = [];
                        var val = '';
                        for (var i = 0; i < polygon.length; i++) {
                            vals.push(polygon[i][0] + ', ' + polygon[i][1])
                        }
                        coordinates_inp.value = vals.join("\n");
                    }

                    function addPointToPolygon(point) {
                        if (!polygon) {
                            polygon = [];
                        }
                        polygon.push(point);
                    }

                    var polyf;

                    function drawPolygon() {
                        if (polygon && polygon.length > 0) {
                            console.log(polygon);
                            if (polyf) polyf.destroy();
                            polyf = new mapgl.Polygon(map, {
                                coordinates: [polygon],
                                color: '#990000',
                                strokeWidth: 3,
                                strokeColor: '#bb0000',
                            });
                        }
                    }

                    function clearPolygon() {
                        if (polygon) {
                            polyf.destroy();
                            polygon = [];
                            setCoord();
                        }
                    }

                    document.getElementById('clear').addEventListener('click', clearPolygon);
                </script>
            </div>
            <div id="step3" style="display: none;">
                <p>Шаг 3/3. Параметры проекта</p>
                <label class="fileinp uibtn gray mwdt" for="geopodosnova" style="max-width: min(100%, 500px);">Загрузить Геоподоснову в формате DWG</label>
                <label for="geopodosnova" style="margin-bottom: 10px; display: block;"><?php echo $geopodosn_state; ?></label>
                <input type="file" class="fileinp" onchange="submit()" id="geopodosnova" name="geopodosnova" accept=".dwg">
                <div class="minpb">
                    <input class="white input" type="number" name="buildingarea" placeholder="Площадь здания, тыс. кв. м" value="<?php echo $prinfo->buildingarea; ?>">
                    <input class="white input" type="number" name="places" placeholder="Количество мест" value="<?php echo $prinfo->places; ?>">
                    <input class="white input" type="number" name="area" placeholder="Площадь ЗУ, Га" value="<?php echo $prinfo->area; ?>">
                    <input class="white input" type="number" name="height" placeholder="Высота здания, м" value="<?php echo $prinfo->height; ?>">
                    <input class="white input" type="number" name="floors" placeholder="Количество этажей" value="<?php echo $prinfo->floors; ?>">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="uibtn gray nm" onclick="step(2)">Назад</button>
                    <button class="uibtn nm" type="submit" name="generate" value="true">Создать</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>