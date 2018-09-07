<?php

namespace Swag\App\Models;

use Swag\Framework\Model;

/**
 * Decorate Wordpress Post Class because it is not extendable :/
 *
 * @since 1.0.0
 */
class WPPost extends Model
{
  /**
   * WP_Post Object
   */
  private $post;

  public function __construct(\WP_Post $post)
  {
    $this->post = $post;
  }

  /**
   * override to check post object for property otherwise use $this->data
   *
   * @param [string] $key
   * @return mixed
   */
  public function __get($key)
  {
    if (property_exists($this, $key)) {
      return $this->$key;
    }

    if (isset($this->post) && property_exists($this->post, $key)) {
      return $this->post->$key;
    }

    return parent::__get($key);
  }
}
