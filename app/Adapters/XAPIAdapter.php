<?php

namespace Swag\App\Adapters;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;

/**
 * Singleton Adapter for communicating with LRS
 *
 * usage: $user = CurrentUser::Get();
 * 
 * @since 1.0.0
 */
final class XAPIAdapter
{
  /**
   * Initializes with LRS Settings
   */
  private function __construct()
  {
    $this->endpoint_settings = get_option('swag_xapi');

    $this->client = new Client([
      'base_uri' => $this->endpoint_settings['endpoint_url'],
      'auth' => [
        $this->endpoint_settings['endpoint_client_key'],
        $this->endpoint_settings['endpoint_client_secret'],
      ],
      'headers' => [
        'X-Experience-API-Version' => '1.0.1',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ],
    ]);
  }

  /**
   * Retrive Singleton
   *
   * @return XAPIAdapter
   */
  public static function Get()
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new xAPIAdapter();
    }
    return $inst;
  }

  /**
   * create an xAPI statement on the LRS, this is ayncronous so as to not affect performance of the app
   *
   * @todo log errors
   * @param array $statement
   * @return void
   */
  public function submit_statement(array $statement) : void
  {
    error_log("saving statement: " . var_export($statement, true));
    $this->client->post('statements', ['json' => $statement]);

    // return json_decode($response->getBody());
  }

  public function save() :void {
    $this->submit_statement();
  }

  /**
   * Get xAPI statements from the LRS
   *
   * @param array $statement
   * @return array
   */
  public function get_statements(array $statement) : array
  {
    // try {
      $response = $this->client->get('statements', ['query' => $statement]);
    // } catch (ClientException $e) {
    //   echo $e->getResponse();
    //   return;
    // }

    return json_decode($response->getBody());
  }
}
