<?php

namespace Swag\App\Models;

use \Swag\Framework\Model;

/**
 * A single swagifact, a polymorphic datatype, basically proxies to the underlying data e.g. a H5P that fulfuils the "Swagifact" Interface
 */
class Swagifact extends Model
{
  public function __construct($data, $type, $swagpath) {
    $this->type = $type;
    if($type === 'h5p') {
      $this->data = new H5P($data, $swagpath);
    }
  }

  public function __call($name, $args) {
    if(method_exists($this->data, $name)) {
      call_user_func_array([$this->data, $name], $args);
    }
  }
}
