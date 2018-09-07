<?php

namespace Swag\App\Models;

use \Swag\Adapters\xAPIAdapter;
use \Swag\Framework\Model;
use \Swag\App\Collections\Swagpaths;

/**
 * Decorates WP_User with Swag features
 *
 * @since 1.0.0
 */
class SwagUser extends Model
{
  private $user;

  /**
   * constructor only accepts a WP_User object
   *
   * @param WP_User $user The user to decorate
   */
  public function __construct(\WP_User $user)
  {
    $this->user = $user;

    $this->xapi_agent = [
      "mbox" => "mailto:" . $user->user_email,
      "name" => $user->display_name,
      // "account" => [
      //   "homePage" => get_option('siteurl'),
      //   "name" => $user->user_login
      // ],
      "objectType" => "Agent"
    ];

    // $this->load_progress_data();
  }

  /**
   * override to check user object for property otherwise use $this->user or $this->user->data
   *
   * @param [string] $key
   * @return mixed
   */
  public function __get($key)
  {
    if (isset($this->user) && (property_exists($this->user, $key) || property_exists($this->user->data, $key))) {
      return $this->user->{$key};
    }

    return parent::__get($key);
  }

  public static function create_by_slug(string $slug) {
    $user = \get_user_by('slug', $slug);
    return new static($user);
  }

  /**
   * user attempted swagpath
   *
   * @param Swagpath $swagpath
   * @return boolean
   */
  public function attempted_swagpath(Swagpath $swagpath) {
    return $swagpath->attempted_by_user($this);
  }

    /**
   * user completed swagpath
   *
   * @param Swagpath $swagpath
   * @return boolean
   */
  public function completed_swagpath(Swagpath $swagpath) {
    return $swagpath->completed_by_user($this);
  }

  /**
   * retrieve completed swagpaths
   *
   * @return Swagpaths
   */
  public function get_completed_swagpaths() {
    global $wpdb;

    $results = $wpdb->get_col("SELECT `swagpath_id` FROM {$wpdb->prefix}swagpath_status WHERE `user_id` = {$this->ID} AND `status` = 'completed';");

    if ($results) {
      return Swagpaths::create_from_ids($results);
    }
    return new Swagpaths();
  }

    /**
   * retrieve attempted swagpaths
   *
   * @return Swagpaths
   */
  public function get_attempted_swagpaths() {
    global $wpdb;

    $results = $wpdb->get_col("SELECT `swagpath_id` FROM {$wpdb->prefix}swagpath_status WHERE `user_id` = {$this->ID} AND `status` = 'attempted';");

    if ($results) {
      return Swagpaths::create_from_ids($results);
    }
    return new Swagpaths();
  }

  public function get_completed_swagpaths_count() {
    return count($this->get_completed_swagpaths()->to_array());
  }

  public function get_attempted_swagpaths_count() {
    return count($this->get_attempted_swagpaths()->to_array());
  }

}
