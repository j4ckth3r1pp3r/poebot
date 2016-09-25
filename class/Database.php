<?php

//---- Класс для работы с базой данных ----//

class Database {

  protected $table_name;
  protected $row;
  protected $row_value;
  protected $key;
  protected $key_value;

  public function __construct( $args ) {

    //---- Подключение к серверу MySQL ----//
    $this->mysqli = new mysqli('localhost', DB_USER, DB_PASSWORD, DB_NAME);

    if (mysqli_connect_errno()) {
       printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
       exit;
    }
    //---- Проверяем и назначаем переменные ----//
    $this->_args( $args );
  }

  public function __destruct() {

    //---- Закрываем соединение ----//
    $this->mysqli->close();

  }

  public function read( $array ) {

    //---- Проверяем аргументы ----//
    $array = $this->_check_variables( $array );
    $row = $array['row'];
    $key = $array['key'];
    $key_value = $array['key_value'];

    //---- Запрос на получение данных ----//
    $result = $this->mysqli->query("SELECT {$row}  FROM {$this->table_name} WHERE {$key}='{$key_value}'")->fetch_assoc();
    return $result;

  }

  public function write( $array ) {

    //---- Назначаем аргументы ----//
    $array = $this->_check_variables( $array );
    $rows = $array['rows'];
    $rows_value = $array['rows_value'];

    //---- Берем вторые строки и используем в качестве ключа ----//
    $row_array = explode(', ', $rows);
    $key = $row_array[1];
    $row_value_array = explode(', ', $rows_value);
    $key_value = $row_value_array[1];

    //---- Передаем в массив ключи для проверки ----//
    $array['key'] = $key;
    $array['key_value'] = $key_value;

    //---- Запрос на сохранение данных ----//
    if ( $this ->read($array) ) {

      /*$key = explode(', ', $rows);
      $key = $key[1];
      $key_value = explode(', ', $rows_value);
      $key_value = $key_value[1];*/

      $row = $row_array[0];
      $row_value = $row_value_array[0];

      $result = $this->mysqli->query("UPDATE {$this->table_name} SET $row = $row_value WHERE {$key}='{$key_value}'");
    }
    else $result = $this->mysqli->query("INSERT INTO {$this->table_name} ($rows) VALUES ($rows_value)");
    return $result;

  }

  protected function _args( $args ) {

    //---- Записываем в отдельные переменные аргументы ----//
    if ( is_array( $args ) ) {
      $this->table_name = $args['table_name'];
      $this->row = $args['row'];
      $this->row_value = $args['row_value'];
      $this->key = $args['key'];
      $this->key_value = $args['key_value'];
    } else {
      $this->table_name = $args;
    }

  }

  protected function _check_variables( $array ) {

    //---- Записываем "дефолтные" аргументы при их отсутствии ----//
    if (! array_key_exists('row', $array)) $array['row'] = $this->row;
    if (! array_key_exists('row_value', $array)) $array['row_value'] = $this->row_value;
    if (! array_key_exists('key', $array)) $array['key'] = $this->key;
    if (! array_key_exists('key_value', $array)) $array['key_value'] = $this->key_value;
    return $array;

  }

}


 ?>
