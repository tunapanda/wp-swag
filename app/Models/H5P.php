<?php

namespace Swag\App\Models;

use \Swag\Framework\Model;
use \Swag\App\Services\CurrentUser;
use \Swag\App\Adapters\XAPIAdapter;
use \Swag\App\Interfaces\SwagifactTemplate;
/**
 * Represents a H5P
 *
 * @since 1.0.0
 */
class H5P extends Model implements SwagifactTemplate
{
  public $xapi_object;
  public $swagpath;

  /**
   * Initializes with H5P data and swagifact it belongs to
   *
   * @param array $data
   * @param Swagpath $swagpath
   */
  public function __construct(array $data, Swagpath $swagpath)
  {
    parent::__construct($data);

    $this->swagpath = $swagpath;

    $this->xapi_object = [
      "objectType" => "Activity",
      "id" => $this->get_permalink(),
      "definition" => [
        "name" => [
          "en-GB" => $data['title'],
        ],
        "type" => "http://tunapanda.org/swag/swagifact",
        "extensions" => [
          "http://tunapanda.org/swag/swagifact/id" => $data['id'],
          "http://h5p.org/x-api/h5p-local-content-id" => $data['id'],
        ],
      ],
    ];
  }

  /**
   * Initialize via H5P slug
   *
   * @param string $slug
   * @param Swagpath $swagpath
   * @return H5P
   */
  public static function create_by_slug(string $slug, Swagpath $swagpath)
  {
    global $wpdb;

    $h5p = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}h5p_contents WHERE slug = \"$slug\"", ARRAY_A);

    return new static($h5p, $swagpath);
  }

  /**
   * Get permalink to H5P/Swagifact
   *
   * @param string $slug
   * @return string The Permalink
   */
  public function get_permalink($slug = false) : string
  {
    return $this->swagpath->get_permalink() . ($slug ? $slug : $this->slug ). '/';
  }

    /**
   * Has the user completed this swagifact, it will default to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function is_completed($user = null) : bool {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();


    $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}swagifact_status WHERE `user_id` = {$user->ID} AND `swagifact_id` = {$this->id} AND `status` = 'completed';", ARRAY_A);

    return (bool) $result;
  }

      /**
   * Has the user attempted this swagifact, it will default to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function is_attempted($user = null) : bool {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();


    $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}swagifact_status WHERE `user_id` = {$user->ID} AND `swagifact_id` = {$this->id} AND `status` = 'attempted';", ARRAY_A);

    return (bool) $result;
  }

    /**
   * this swagifact has been attempted by the specified user, updates the database, it will default to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function attempted($user = null) {
    global $wpdb;

    if($this->is_completed()) {
      return true;
    }

    $user = $user ?? CurrentUser::Get();

    $result = $wpdb->query("INSERT INTO {$wpdb->prefix}swagifact_status (`user_id`, `swagifact_id`, `status`) VALUES ({$user->ID}, {$this->id}, 'attempted') ON DUPLICATE KEY UPDATE `status` = 'attempted'");

    // $this->xapi_attempted();
    do_action("swag_swagifact_attempted", $user, $this);

    return (bool) $result;
  }

  /**
   * this swagifact has been completed by the specified user, updates the database, it will default to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function completed($user = null) {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();

    $result = $wpdb->query("INSERT INTO {$wpdb->prefix}swagifact_status (`user_id`, `swagifact_id`, `status`) VALUES ({$user->ID}, {$this->id}, 'completed') ON DUPLICATE KEY UPDATE `status` = 'completed'");

    // $this->xapi_completed();
    do_action("swag_swagifact_completed", $user, $this);

    return (bool) $result;
  }

    /**
   * send attempted xapi statement to LRS
   *
   * @return void
   */
  public function xapi_attempted() {
    $user = $user ?? CurrentUser::Get();
    $xapi = XAPIAdapter::Get();

    $statement = [
      'object' => $this->xapi_object,
      'actor' => $user->xapi_agent,
      'verb' => [
        "id" => 'http://adlnet.gov/expapi/verbs/attempted',
        'display' => [
          'en-US' => 'attempted'
        ]
      ]
    ];

    $xapi->submit_statement($statement);
  }

    /**
   * send completed xapi statement to LRS
   *
   * @return void
   */
  public function xapi_completed() {
    $user = $user ?? CurrentUser::Get();
    $xapi = XAPIAdapter::Get();

    $statement = [
      'object' => $this->xapi_object,
      'actor' => $user->xapi_agent,
      'verb' => [
        "id" => 'http://adlnet.gov/expapi/verbs/completed',
        'display' => [
          'en-US' => 'completed'
        ]
      ]
    ];

    $xapi->submit_statement($statement);
  }
}
