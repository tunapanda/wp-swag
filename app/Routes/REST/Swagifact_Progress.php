<?php

namespace Swag\App\Routes\REST;

use \Swag\App\Models\Swagpath;
use \Swag\App\Models\H5P;
use \Swag\App\Models\Statement;

/**
 * REST Route for recording xAPI Statements from H5P
 */
class Swagifact_Progress
{
  /**
   * Initializes the rest route in Wordpress REST API api
   */
  public function __construct()
  {
    add_action('rest_api_init', function () {
      register_rest_route('swag/v1', '/swagifact/progress', array(
        'methods' => 'POST',
        'callback' => array($this, 'progress'),
      ));
    });

  }

  /**
   * POST /xAPI/statements
   *
   * @param WP_REST_Request $request
   * @return array
   */
  public function progress(\WP_REST_Request $request):array
  {
    $data = $request->get_json_params();

    $swagpath_id = $data['swagpath'];
    $swagifact_slug = $data['swagifact'];

    $swagpath = Swagpath::create_by_id($swagpath_id);

    if(!$swagifact_slug) {
      $swagifact = $swagpath->get_swagifacts()->object_at(0);
    } else {
      $swagifact = H5P::create_by_slug($swagifact_slug, $swagpath);
    }

    if($data['statement']['verb']['id'] === 'http://adlnet.gov/expapi/verbs/progressed') {
      $swagifact->attempted();
    }

    if($data['statement']['verb']['id'] === 'http://adlnet.gov/expapi/verbs/completed') {
      $swagifact->completed();
    }

    $statement = Statement::create($data['statement']);


    if ($data['statement']['verb']['id'] === 'http://adlnet.gov/expapi/verbs/progressed' || $data['statement']['verb']['id'] === 'http://adlnet.gov/expapi/verbs/completed') {
      //override grouping
      $statement->statement['context']['contextActivities']['grouping'] = [$swagpath->xapi_object];
  
      //override object
      $statement->statement['object'] = $swagifact->xapi_object;
    }

    if ($data['statement']['verb']['id'] === 'http://adlnet.gov/expapi/verbs/answered' || $data['statement']['verb']['id'] === 'http://adlnet.gov/expapi/verbs/interacted' ) {
      unset($statement->statement['context']['extensions']); // breaks Learning Locker for some reason
      $statement->statement['object']['id'] = $swagifact->xapi_object['id'] . $statement->statement['object']['definition']['extensions']['http://h5p.org/x-api/h5p-subContentId'];
      $statement->statement['context']['contextActivities']['parent'][0]['id'] = $swagifact->xapi_object['id'];
    }

    $statement->save();

    return ["success" => true];
    // $statement_data = $data['statement'];
    // $swagpath = $data['swagpath'];
    // $swagifact = $data['swagifact'];

    // $swagpath = Swagpath::create_by_id($swagpath);

    // $h5p = H5P::create_by_slug($swagifact, $swagpath);

    // $statement = Statement::create($statement_data);

    // //override grouping
    // $statement->statement['context']['contextActivities']['grouping'] = [$swagpath->xapi_object];

    // //override object
    // $statement->statement['object'] = $h5p->xapi_object;

    // $result = $statement->save();
    // if ($result) {
    //   return ["success" => true];
    // }
    // return ["success" => false];
  }
}
