<?php

namespace Swag\App\Routes\REST;

/**
 * REST Route for recording xAPI Statements from H5P
 */
class XAPI_Statements
{
  /**
   * Initializes the rest route in Wordpress REST API api
   */
  public function __construct()
  {
    add_action('rest_api_init', function () {
      register_rest_route('swag/v1', '/xAPI/statements', array(
        'methods' => 'POST',
        'callback' => array($this, 'create_statement'),
      ));
    });

  }

  /**
   * POST /xAPI/statements
   *
   * @param WP_REST_Request $request
   * @return array
   */
  public function create_statement(WP_REST_Request $request):array
  {
    $data = $request->get_json_params();

    $statement_data = $data['statement'];
    $swagpath = $data['swagpath'];
    $swagifact = $data['swagifact'];

    $swagpath = Swagpath::create_by_id($swagpath);

    $h5p = H5P::create_by_slug($swagifact, $swagpath);

    $statement = Statement::create($statement_data);

    //override grouping
    $statement->statement['context']['contextActivities']['grouping'] = [$swagpath->xapi_object];

    //override object
    $statement->statement['object'] = $h5p->xapi_object;

    $result = $statement->save();
    if ($result) {
      return ["success" => true];
    }
    return ["success" => false];
  }
}
