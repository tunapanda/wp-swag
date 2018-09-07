<?php
namespace Swag\App\Interfaces;

use \Swag\App\Models\Swagpath;

interface SwagifactTemplate {
  public static function create_by_slug(string $slug, Swagpath $swagpath);

  public function get_permalink($slug = false) : string;

  public function is_completed($user = null) : bool;

  public function is_attempted($user = null) : bool;

  public function attempted($user = null);

  public function completed($user = null);
}