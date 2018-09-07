<?php
namespace Swag\App\Models;

use \Swag\App\Adapters\XAPIAdapter;
use \Swag\Framework\Model;

/**
 * Represents an xAPI Statement
 *
 * @since 1.0.0
 */
class Statement extends Model
{
  public $statement;

  /**
   * initializes with raw xAPI statement
   *
   * @param [type] $statement
   */
  public function __construct($statement)
  {
    $this->statement = $statement;

    // $this->data = [
    //   "statement" => $statement,
    //   "user" => explode(':', $statement['actor']['mbox'])[1],
    //   "verb" => $statement['verb']['id'],
    //   "object" => $statement['object']['id'],
    //   "category" => array_column($statement['context']['contextActivities']['category'], 'id'),
    //   "grouping" => array_column($statement['context']['contextActivities']['grouping'], 'id'),
    // ];
  }

  // public function __set($key, $value)
  // {
  //   if ($key === 'grouping') {
  //     $this->data['context'] = ['contextActivities' => ['grouping' => $value]];
  //     return;
  //   }
  //   if ($key === 'category') {
  //     $this->data['context'] = ['contextActivities' => ['category' => $value]];
  //     return;
  //   }
  //   return parent::__set($key, $value);
  // }

  /**
   * saves the xAPI statement to the LRS
   *
   * @return array
   */
  public function save(): void
  {
    $adapter = XAPIAdapter::Get();

    $adapter->submit_statement($this->statement);
  }
}
