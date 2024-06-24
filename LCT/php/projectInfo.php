<?php

function editOrCreate($key, $value, $sxml)
{
    $el = $sxml->xpath('//' . $key);
    if ($el) {
        $el[0][0] = $value;
    } else {
        $sxml->addChild($key, $value);
    }
}

function saveInfo($info, $project_id)
{
    $folder = '/projects/' . $project_id . '/';
    $filename = $folder . 'project.xml';
    $xml = simplexml_load_file($filename);
    $allowed = ['name', 'coordinates', 'buildingarea', 'places', 'area', 'height', 'floors'];
    foreach ($info as $key => $value) {
        if (in_array($key, $allowed)) {
            if (isset($value) && !empty($value)) {
                if ($key == 'coordinates') {
                    $coord = preg_split('/\n/', $value);
                    $e = $xml->xpath('//coordinates');
                    if (!$e) {
                        $e = $xml->addChild('coordinates');
                    } else {
                        $e = $e[0];
                        unset($xml->coordinates->coord);
                    }
                    foreach ($coord as $c) {
                        $e->addChild('coord', $c);
                    }
                } else {
                    editOrCreate($key, $value, $xml);
                }
            }
        }
    }
    $xml->asXML($filename);
    return true;
}

function saveInfoFromXML($xml_file, $project_id)
{
    $sxml = simplexml_load_file($xml_file);
    $data = array();
    foreach ($sxml as $key => $value) {
        if (isset($value) && !empty($value)) {
            if ($key == 'coordinates') {
                $resa = array();
                foreach ($value as $c) {
                    $resa[] = (string)$c[0];
                }
                $data[$key] = implode("\n", $resa);
            } else $data[$key] = (string)$value[0];
        }
    }
    return saveInfo($data, $project_id);
}

function checkProjectInfo($project_id)
{
    $folder = '/projects/' . $project_id . '/';
    $filename = $folder . 'project.xml';
    $xml = simplexml_load_file($filename);
    $allowed = ['name', 'coordinates', 'buildingarea', 'places', 'area', 'height', 'floors'];
    foreach ($allowed as $a) {
        $el = $xml->xpath('//' . $a);
        if (!$el) return false;
    }
    return is_file($folder . 'geopodosnova.dwg');
}
