<?php

namespace Swag\App\Collections;

use Swag\Framework\Collection;
use Swag\App\Models\Swagifact;
use Swag\App\Models\Swagpath;

/**
 * Represents an assortment of swagifacts
 */
class Swagifacts extends Collection {
  public function __construct($data, $type, $swagpath)
  {
    if (is_array($data)) {
      $swagifacts = array_map(function($swagifact) use ($swagpath, $type) { return new Swagifact($swagifact, $type, $swagpath); }, $data);
      $this->data = $swagifacts;
    }
  }

  /**
   * Create Swagifacts collection from an array of IDs
   *
   * @param array $ids
   * @return Swagifacts
   */
  public static function create_from_h5p_ids(array $ids, Swagpath $swagpath): Swagifacts {
    global $wpdb;
    $ids = implode(',', $ids);

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}h5p_contents WHERE id IN ($ids) ORDER BY FIELD(id, $ids)", ARRAY_A);

    return new static($results, 'h5p', $swagpath);
  }
}