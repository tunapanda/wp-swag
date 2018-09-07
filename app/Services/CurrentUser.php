<?php

namespace Swag\App\Services;

use \Swag\App\Models\SwagUser;

/**
 * Singleton SwagUser to represet the current logged in user
 *
 * usage: $user = CurrentUser::Get();
 */
final class CurrentUser extends SwagUser
{
  public static function Get()
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new static(wp_get_current_user());
    }
    return $inst;
  }
}