<?php

namespace Swag\App\Collections;

use Swag\Framework\Collection;
use Swag\App\Models\H5P;
use Swag\App\Models\Swagpath;

class Swagpaths extends Collection {
  public function __construct($data = null)
  {
    if (is_array($data)) {
      $swagpaths = array_map(function($swagpath) { return new Swagpath($swagpath); }, $data);
      return $this->data = $swagpaths;
    }
    $this->data = [];
  }

  /**
   * Create Swagpaths collection from an array of IDs
   *
   * @param array $ids
   * @return Swagpaths
   */
  public static function create_from_ids(array $ids): Swagpaths {
    $posts = get_posts([
      'post__in' => $ids,
      'post_type' => 'swagpath'
    ]);

    return new static($posts);
  }
}