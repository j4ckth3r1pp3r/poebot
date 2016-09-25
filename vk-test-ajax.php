<?php

$args = array (
  'table_name' => 'lastfm',
  'row' => 'lastfm_user',
  'key' => 'vk_id',
  'key_value' => '12223'
);

 //Посылаем запрос серверу

$array = array(
    'rows' => 'lastfm_user, vk_id',
    'rows_value' => "'rang3r', 10526677"
  );


if ( isset($_POST['msg']) ) {
  $args = array(
    'group_id' => '128191703', //текст сообщения
    'user_msg' => $_POST['msg'], //текст сообщения
    'user_id' => 10526677, //айди юзера
    'token' => $token,
    'lastfm_api_key' => $lastfm_api_key
  );

  if (isset($_POST['id'])) {
    $args['user_id'] = $_POST['id'];
  }

  $message = new MessageTest( $args );

  print_r($message->send_params['message']);
}
?>
