<?php

//---- Класс для получения последнего трека на ласте ----//

class Lastfm {

  public $lastfm_params = array(
    'method' => 'user.getrecenttracks',
    'limit' => '1',
    'format' => 'json'
  );
  protected $lastfm_params_query;

  public function __construct($lastfm_api_key, $user = null) {

    //---- Добавляем ключ API и юзера ласта ----//
    $this->lastfm_params['api_key'] = $lastfm_api_key;
    $this->lastfm_params['user'] = $user;

    //---- Формируем из массива строку запроса ----//
    $this->lastfm_params_query = http_build_query($this->lastfm_params);
    //file_put_contents('1112.txt', $this->lastfm_params_query);

  }

  public function getTrack() {

    //---- Получаем песню ----//
    $lastfm_data = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?'.$this->lastfm_params_query));

    //---- Проверяем на ошибки ----//
    if ( $this->checkProblem( $lastfm_data ) ) {
      return $this->checkProblem( $lastfm_data );
      die;
    }

    //---- Формируем переменные ----//
    $artist = $lastfm_data->recenttracks->track[0]->artist->{'#text'};
    $track = $lastfm_data->recenttracks->track[0]->name;
    $nowplaying = $lastfm_data->recenttracks->track[0]->{'@attr'}->nowplaying;
    $listenday_unix = $lastfm_data->recenttracks->track[0]->date->uts;
    $listenday = date('d.m.Y в H:i', $listenday_unix);

    //---- Добавляем логин ----//
    $message = "Логин: {$this->lastfm_params['user']}\n";

    //---- Основная часть ----//
    $message .= $artist." - ".$track;

    //---- Дописываем статус ----//
    if ($nowplaying == 'true') {
      $message .= ' (играет сейчас)';
    } else {
      $message .= ' (слушал '.$listenday.')';
    }


    return $message;
  }

  protected function checkProblem( $data ) {

    if( property_exists($data, 'error') ) {

      //---- Возвращаем текст ошибки ----//
      switch($data->error) {
        case 6:
          return 'Пользователь не найден';
          break;
        default:
          return "Ошибка №{$data->error}, \"{$data->message}\"";
          break;
      }

    } //endif
    elseif (empty($data->recenttracks->track)) return 'Пользователь вообще ничего не слушал за жизнь :D';
    else return false;

  }

}

 ?>
