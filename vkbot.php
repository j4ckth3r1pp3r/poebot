<?php

//---- Подключаем настройки ----//
include_once('config.php');

if (!isset($_REQUEST)) {
      return;
}


//---- подключаем нужные классы ----//
function autoload_main($class_name) {
    include 'class/'.$class_name . '.php';
}
spl_autoload_register('autoload_main');

if ( isset($_GET['test'] ) ) {
  include ('test.php');
}

//---- Для теста бота через ajax ----//
if ( isset($_POST['ajax'] ) ) {
  include ('vk-test-ajax.php');
}

if ( isset($_GET['code']) ) {
  $vkauth = new vkauth;

}

//---- Получаем и декодируем уведомление ----//
$data = json_decode(file_get_contents('php://input'));

//---- Получаем тип запроса ----//
$file_type = $data->type;

//---- На основе типа запроса подключаем нужный файл ----//
if (include("type/$file_type.php")) return;
elseif ($file_type) echo "Can't find {$file_type}.php";


?>
