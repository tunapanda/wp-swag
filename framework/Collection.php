<?php

namespace Swag\Framework;

/**
 * Collection represents an array of Models
 */
class Collection implements \Iterator, \ArrayAccess
{
  /**
   * Underlying data for this collection
   *
   * @var array
   */
  protected $data = [];

  public function __construct($data)
  {
    if (is_array($data)) {
      $this->data = array_map(Model::create, $data);
    }
  }

  public static function create($data = [])
  {
    return new static($data);
  }

  public function object_at($i) {
    return $this->data[$i];
  }

  public function to_array() {
    return $this->data;
  }

  public function length() {
    return count($this->data);
  }

  public function rewind()
  {
    reset($this->data);
  }

  public function current()
  {
    return current($this->data);
  }

  public function key()
  {
    return key($this->data);
  }

  public function next()
  {
    return next($this->data);
  }

  public function valid()
  {
    $key = key($this->data);
    $var = ($key !== null && $key !== false);
    return $var;
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
        $this->data[] = $value;
    } else {
        $this->data[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
      return isset($this->data[$offset]);
  }

  public function offsetUnset($offset) {
      unset($this->data[$offset]);
  }

  public function offsetGet($offset) {
      return isset($this->data[$offset]) ? $this->data[$offset] : null;
  }
}
