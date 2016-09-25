<?php

//---- класс для работы с сообщениями ----//

class Message {

  //---- Переменные ----//
  protected $data; //Информация из вне
  protected $send_params;
  protected $db_args = array (
                        'table_name' => 'lastfm',
                        'row'        => 'lastfm_user',
                        'key'        => 'vk_id',
                      );

  public function __construct( $args = null ) {
    if ( $args ) {
      //---- Помещаем параметры в переменную для общего пользования и проверяем сообщение ----//
      $this->data = $args;
      $this->send_params = array( //Входные параметры
        'message' => 'Ну и зачем ты написал: "'.$this->data["user_msg"].'"?',
        'user_id' => $this->data['user_id'],
        'access_token' => $this->data['token'],
        'v' => '5.0'
      );
      $this->data['time'] = date('Y.m.d_H.i.s', $this->data['time']);

      $this->check_msg_type();
      $this->check_member();
    } else {
      echo 'Не хватает параметра';
      die;
    }
  }


  public function send() {


    //---- формируем строку для отправки ----//
    $get_params = http_build_query($this->send_params);

    //---- Отправляем сообщение ----//
    sleep(1);
    $this->send_file = file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);

    $this->log_message( 'error' );

  }

  //---- Проверяем является ли участником группы ----//
  protected function check_member () {
    $args = array(
      'group_id' => $this->data['group_id'],
      'user_id' => $this->data['user_id']
    );

    $args_query = http_build_query($args);

    $check = json_decode(file_get_contents('https://api.vk.com/method/groups.isMember?'. $args_query));

    if ($check->response == 0) $this->send_params['message'] = 'Я отвечаю только участникам сообщества!';

  }

  //---- Записываем лог ----//
  private function log_message( $type ) {

    if (property_exists($this->read(), $type)) {

      $filename = 'log/message_'.$type.'_'.$this->data['time'].'.txt';
      file_put_contents($filename, print_r($this->read(), true));

      //---- Если ошибка - отправляем сообщение ----//
      if ( $type == 'error' ) {
        $this->error_msg();
      }
    }
  }

  //---- Авторизация ----//
  private function registration() {

    $params = array(
      'client_id' => $this->data['client_id'],
      'display' => 'page',
      'redirect_uri' => 'http://test1.lp5.com.ua/j4ck/vkbot.php',
      'response_type' => 'code',
      'v' => '5.53'
    );
    $params = http_build_query($params);

    $this->send_params['message'] = "Заходи: https://oauth.vk.com/authorize?{$params}";
  }

  private function help() {
    $this->send_params['message'] = "ну типа\nвторая строка";
  }

  //---- Последняя песня ласта ----//
  private function getTrack() {

    //---- Берем сообщение юзера и вытаскиваем имя пользователя ----//
    $lastfm_user = $this->check_second_word();

    //---- Если логин не указан, но записан юзер по умолчанию ----//
    if (!$lastfm_user) {

      //---- Проверяем юзера по базе ----//
      $vk_id = $this->data['user_id'];
      $this->db_args['key_value'] = $vk_id;
      $db = new Database( $this->db_args );
      $key = $this->db_args['row'];
      $lastfm_user = $db->read();
      $lastfm_user = $lastfm_user[$key];

      //---- если не находим ----/
      if(!$lastfm_user) {
        $this->send_params['message'] = "Пользователь по умолчанию не задан\nЗадай его по умолчанию коммандой \"/lastfm логин ластфма\"\nили пиши \"/песня логин ластфма\"";
        return;
      }
    }

    $lastfm = new Lastfm($this->data['lastfm_api_key'], $lastfm_user);
    $this->send_params['message'] = $lastfm->getTrack();

  }

  protected function addLastfmUser() {

    $vk_id = $this->data['user_id'];
    $lastfm_user = $this->check_second_word();
    if (!$lastfm_user) {

      //---- Проверяем юзера по базе ----//
      $vk_id = $this->data['user_id'];
      $this->db_args['key_value'] = $vk_id;
      $db = new Database( $this->db_args );
      $key = $this->db_args['row'];
      $lastfm_user = $db->read();
      $lastfm_user = $lastfm_user[$key];

      //---- если не находим ----/
      if(!$lastfm_user) {
        $this->send_params['message'] = "Пользователь по умолчанию не задан\nЗадай его по умолчанию коммандой \"/lastfm логин ластфма\"\nили пиши \"/песня логин ластфма\"";
        return;
      }
      $this->send_params['message'] = "Установленный логин LastFM: \"{$lastfm_user}\"";
      return;
    }


    //---- Добавляем айдишник пользователя ----//
    $this->db_args['key_value'] = $vk_id;

    $db = new Database( $this->db_args );

    $array = array(
        'rows' => 'lastfm_user, vk_id',
        'rows_value' => "'{$lastfm_user}', {$vk_id}"
      );

    $check = $db->write($array);

    if ($check) $message = "Логин LastFM: \"{$lastfm_user}\" успешно добавлен";
    else $message = 'Произошла ошибка';

    $this->send_params['message'] = $message;
  }

  //---- Достаем второе слово ----//
  private function check_second_word() {

    //---- Берем сообщение юзера и вытаскиваем второе слово ----//
    $message = $this->data['user_msg'];
    $second_word = stripos($message, ' ');
    if ($second_word) $second_word = substr($message, $second_word+1);
    if ($second_word && $second_word != '0') return $second_word;
    else return false;
  }

  private function checkPC () {

    $check = fsockopen('79.171.123.216', '80',  $errno, $errstr, 2);

    if ($check) {
      $message = 'Компуктер включен';
    } else $message = 'Компуктер выключен';

    $this->send_params['message'] = $message;
  }

  //---- Проверка на сообщение ----//
  private function check_msg_type() {

    preg_match('/\/(\S*)/i', mb_strtolower($this->data['user_msg']), $matches);
    switch($matches[1]) {
      case 'зайти':
        $this->registration();
        break;
      case 'помощь':
        $this->help();
        break;
      case 'песня':
        $this->getTrack();
        break;
      case 'lastfm':
        $this->addLastfmUser();
        break;
      case 'пк':
        if ($this->data['user_id'] == 10526677) {
          $this->checkPC();
        } else $this->send_params['message'] = "Нит тут ничего\nпроцем клянус! :-)";
        break;
      default:
        //$this->send_params['message'] = "Пиши /помощь, для того чтоб узнать команды";
        break;
    }
  }

  private function error_msg() {
    $this->send_params['message'] = 'ну не спамь, братишка :-)';
    $this->send();
  }

  //---- Прочитать сообщение ----//
  public function read() {
    return json_decode($this->send_file);
  }
}

 ?>
