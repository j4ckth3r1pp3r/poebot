<?php

//---- Тип отвечающий за реакцию на новое сообщение

//---- получаем id автора ----//
$user_id = $data->object->user_id;
$user_msg = $data->object->body;

//---- формируем данные для отправки классу ----//
$args = array(
  'group_id' => $data->group_id, //id группы
  'user_msg' => $data->object->body, //текст сообщения
  'client_id' => $client_id, //передаем айди клиента
  'time' => $data->object->date, //Время отправки
  'user_id' => $data->object->user_id, //айди юзера
  'token' => $token, //ключ авторизации
  'lastfm_api_key' => $lastfm_api_key //ключ ласта
);

$message = new Message( $args );


//---- Отправка сообщения ----//
$message->send();

echo('ok');

 ?>
