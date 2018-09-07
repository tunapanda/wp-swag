<?php

namespace Swag\App\Models;

use \Swag\Framework\Exceptions\InvalidPostTypeException;
use \Swag\App\Collections\Swagifacts;
use \Swag\App\Collections\Swagpaths;
use \Swag\App\Services\CurrentUser;
use \Swag\App\Adapters\XAPIAdapter;

/**
 * Decorates Swagpath posts with swag features
 *
 * @since 1.0.0
 * @see WP_Post
 */
class Swagpath extends WPPost
{
  /**
   * A representaion of this swagpaths xAPI object
   *
   * @var array
   */
  protected $xapi_object = [];

  /**
   * constructor only accepts WP_Post object
   *
   * @param \WP_Post $post
   * @throws Exception if the provided WP_Post does not have a post_type swagpath
   */
  public function __construct(\WP_Post $post)
  {
    if ($post->post_type !== 'swagpath') {
      throw new InvalidPostTypeException("WP_Post post_type must be 'swagpath'");
    }

    $this->xapi_object = [
      "objectType" => "Activity",
      "id" => get_permalink($post),
      "definition" => [
        "name" => [
          "en-GB" => $post->post_title,
        ],
        "description" => [
          "en-GB" => $post->post_excerpt,
        ],
        "type" => "http://tunapanda.org/swag/swagpath",
        "extensions" => [
          "http://tunapanda.org/swag/swagpath/id" => $post->ID,
        ],
      ],
    ];

    return parent::__construct($post);
  }

  /**
   * initialize via wordpress post ID
   *
   * @param Integer $id
   * @return Swagpath
   */
  public static function create_by_id($id): Swagpath
  {
    return new static(get_post($id));
  }

  public static function create_by_slug(string $slug): Swagpath
  {
    return new static(get_page_by_path($slug, OBJECT, 'swagpath'));
  }

  /**
   * get the Swagpath permalink
   *
   * @return String The permalink
   */
  public function get_permalink(): string
  {
    return get_permalink($this->post);
  }

  /**
   * gets swagifacts for swagpath
   *
   * @return Swagifacts
   */
  public function get_swagifacts(): Swagifacts {
    $field = get_field_object('swagifacts', $this->post->ID);

    if ($field) {
      $ids = $field['value'];

      $results = Swagifacts::create_from_h5p_ids($ids, $this);

      return $results;
    }
    return [];
  }

  public function get_prerequisites(): Swagpaths {
    $ids = get_field('prerequisites', $this->post->ID);

    if (count($ids)) {
      $results = Swagpaths::create($ids);

      return $results;
    }
    return Swagpaths::create();
  }

  /**
   * Has the user completed this swagpath, defaults to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function is_completed($user = null): bool {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();

    $result = $wpdb->get_row("SELECT `status` FROM {$wpdb->prefix}swagpath_status WHERE `user_id` = {$user->ID} AND `swagpath_id` = {$this->ID} AND `status` = 'completed'", ARRAY_A);

    if (!$result) {
      $all_completed = array_reduce($this->get_swagifacts()->to_array(), function($prev, $swagifact) {
        return $prev && $swagifact->is_completed();
      }, true);

      if($all_completed) {
        $this->completed();
        return true;
      }
    }

    return (bool) $result;
  }

    /**
   * Has the user atempted this swagpath, defaults to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function is_attempted($user = null) : bool {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();

    $result = $wpdb->get_row("SELECT `status` FROM {$wpdb->prefix}swagpath_status WHERE `user_id` = {$user->ID} AND `swagpath_id` = {$this->ID} AND `status` = 'attempted'", ARRAY_A);

    if (!$result) {
      $any_atemptted = array_reduce($this->get_swagifacts()->to_array(), function($prev, $swagifact) {
        return $prev || $swagifact->is_attempted();
      }, false);

      if($any_atemptted) {
        $this->attempted();
        return true;
      }
    }

    return (bool) $result;
  }

  /**
   * Does the supplied user have incomplete swagpaths needed for this one
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function has_incomplete_prerequisites($user = null) : bool {
    $user = $user ?? CurrentUser::Get();

    $prerequisites = $this->get_prerequisites();

    if ($prerequisites->length() < 1) {
      return false;
    }

    $all_completed = array_reduce($prerequisites->to_array(), function($prev, $swagpath) {
      return $prev && $swagpath->is_completed($user);
    }, true);

    if($all_completed) {
      return false;
    }
    return true;
  }

  /**
   * Get the status of this swagpath for the supplied user
   *
   * @param SwagUser $user
   * @return string
   */
  public function get_status($user = null) : string {
    $user = $user ?? CurrentUser::Get();

    if ($this->is_completed($user)) {
      return 'completed';
    } else if ($this->has_incomplete_prerequisites($user)) {
      return 'locked';
    } else if ($this->is_attempted($user)) {
      return 'attempted';
    } else {
      return 'available';
    }
  }

    /**
   * this swagpath has been attempted by the specified user, updates the database, defaults to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function attempted($user = null) {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();

    $result = $wpdb->query("INSERT INTO {$wpdb->prefix}swagpath_status (`user_id`, `swagpath_id`, `status`) VALUES ({$user->ID}, {$this->ID}, 'attempted') ON DUPLICATE KEY UPDATE `status` = 'attempted'");

    // $this->xapi_attempted();
    do_action("swag_swagpath_attempted", $user, $this);

    return (bool) $result;
  }

  /**
   * this swagpath has been completed by the specified user, updates the database, defaults to the currently logged in user
   *
   * @param SwagUser $user
   * @return boolean
   */
  public function completed($user = null) {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();

    $result = $wpdb->query("INSERT INTO {$wpdb->prefix}swagpath_status (`user_id`, `swagpath_id`, `status`) VALUES ({$user->ID}, {$this->ID}, 'completed') ON DUPLICATE KEY UPDATE `status` = 'completed'");

    // $this->xapi_completed();
    do_action("swag_swagpath_completed", $user, $this);

    return (bool) $result;
  }

  public function get_completion_date($user = null) {
    global $wpdb;

    $user = $user ?? CurrentUser::Get();

    $timestamps = $wpdb->get_col("SELECT `timestamp` FROM {$wpdb->prefix}swagpath_status WHERE `user_id` = {$user->ID} AND `swagpath_id` = {$this->ID}");
    
    if(count($timestamps)) {
      return $timestamps[0];
    }
    return false;
  }

  /**
   * get the Open Badge data for this swagpath
   *
   * @param SwagUser $user
   * @return array
   */
  public function get_openbadge($user = null) {
    $user = $user ?? CurrentUser::Get();
    $settings = get_option('open_badges_issuer');

    return [
      "name" => $this->post_title,
      "description" => $this->post_content,
      "image" => get_field("badge", $this->ID),
      "date_issued" => date('d/m/Y', strtotime($this->get_completion_date())),
      "path_permalink" => get_permalink($this->ID),
      "badge_permalink" => get_author_posts_url($user->ID) . 'badge/' . $this->post_name
    ];
  }

  /**
   * send attempted xapi statement to LRS
   *
   * @param SwagUser $user
   * @return void
   */
  public function xapi_attempted($user = null) : void {
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
   * @param SwagUser $user
   * @return void
   */
  public function xapi_completed($user = null) : void {
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
