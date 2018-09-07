<?php

namespace Swag\Framework;

/**
 * Model represents a single piece of data
 */
class Model
{
  /**
   * Underlying data for this model
   *
   * @var array
   */
  protected $data = [];

  public function __construct($data)
  {
    $this->data = $data;
  }

  public function __get($key)
  {
    if (property_exists($this, $key)) {
      return $this->$key;
    }

    if (array_key_exists($key, $this->data)) {
      return $this->data[$key];
    }

    if (array_key_exists($key, $this->data->data)) {
      return $this->data->data[$key];
    }

    return null;
    // $trace = debug_backtrace();
    // trigger_error(
    //   'Undefined property via __get(): ' . $key .
    //   ' in ' . $trace[0]['file'] .
    //   ' on line ' . $trace[0]['line'],
    //   E_USER_NOTICE);
    // return null;

  }

  public function __set($key, $value)
  {
    $this->data[$key] = $value;
  }

  public static function create($data)
  {
    return new static($data);
  }

}
