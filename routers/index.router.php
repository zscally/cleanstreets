<?php

use lib\Core;
use lib\Config;
use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;

/**
 * @SWG\Swagger(
 *     schemes={"http","https"},
 *     host="sss.louisvilleky.gov",
 *     basePath="/",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Street Sweeper Notification API",
 *         description="Simple API to relay message from sharepoint up to govdelivery network",
 *         termsOfService="",
 *         @SWG\Contact(
 *             email="zachary.scally@louisvilleky.com"
 *         ),
 *         @SWG\License(
 *             name="Private License",
 *             url="URL to the license"
 *         )
 *     )
 * )
 */

$app->get('/', function($request, $response, $app){
    return $response->withRedirect('/admin');
});

/**  @SWG\Post(
 *     path="/adduser",
 *     tags={"adduser"},
 *     summary="Add User",
 *     description="Adds a new streetsweeper user to database and govdelivery",
 *     consumes={"application/json",
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         emailAddress="body",
 *         required=false,
 *         phoneNumber="body",
 *         mailingAddress="Pet object that needs to be added to the store",

 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="User Added",
 *     ),
 * )
 */
$app->post('/adduser', function($request, $response, $args){
    $validator = new lib\Validator();

    /** accept data from louisvilleky.gov
     *  2) validate information making sure key peaces are present
     *      Either phone or email are required both cannot be blank.
     *      If both are present than both message types need to be sent
     *
     *      a. Email address (Option) (validate email)
     *      b. Phone (Optional) (if present validate phone)
     *      c. Mailing Address (Required)
     *      d. Area (required) (INT)
     *      E. Route (required) (INT)
     *      G. X_coord (require) (INT)
     *      H. Y_coord (require) (INT)
     *
     *   return - If any required field is missing send error object with what issue.
     */
    $body_data = $request->getParsedBody();

    if( ! (empty( $body_data ) ) ){
        $data = array(
            'emailAddress' => isset($body_data['emailAddress']) ? filter_var($body_data['emailAddress'], FILTER_SANITIZE_STRING) : false,
            'phoneNumber' => isset($body_data['phoneNumber']) ? filter_var($body_data['phoneNumber'], FILTER_SANITIZE_STRING) : false,
            'first_name' => isset($body_data['first_name']) ? filter_var($body_data['first_name'], FILTER_SANITIZE_STRING) : false,
            'last_name' => isset($body_data['last_name']) ? filter_var($body_data['last_name'], FILTER_SANITIZE_STRING) : false,
            'mailingAddress' => isset($body_data['mailingAddress']) ? filter_var($body_data['mailingAddress'], FILTER_SANITIZE_STRING) : false,
            'area' => isset($body_data['area']) ? filter_var($body_data['area'], FILTER_SANITIZE_STRING) : false,
            'route' => isset($body_data['route']) ? $body_data['route'] : false,
            'x' => isset($body_data['inputPoint']['x']) ? $body_data['inputPoint']['x'] : false,
            'y' => isset($body_data['inputPoint']['y']) ? $body_data['inputPoint']['y']: false,
            'license_id' => isset($body_data['license_id']) ? filter_var($body_data['license_id'], FILTER_SANITIZE_STRING) : false,
            'council_district' => isset($body_data['council_district']) ? filter_var($body_data['council_district'], FILTER_SANITIZE_STRING) : false,
        );

        $constraint  = array(
            'emailAddress' => array(
                'require' => true,
                'validate' => 'email',
            ),
            'phoneNumber' => array(
                'require' => false,
                'validate' => 'phone',
            ),
            'first_name' => array(
                'require' => true,
            ),
            'last_name' => array(
                'require' => true,
            ),
            'mailingAddress' => array(
                'require' => true
            ),
            'area' => array(
                'require' => true,
            ),
            'route' => array(
                'require' => true,
            ),
            'x' => array(
                'require' => true
            ),
            'y' => array(
                'require' => true
            ),
            'license_id' => array(
                'require' => false
            ),
            'council_district' => array(
                'require' => true
            )
        );

        $data_valid = $validator->validate($data, $constraint);

        if( ! $data_valid )
        {
            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/Json')
                ->write(json_encode([
                    'response' => 'fail',
                    'message' => $validator->error_messages,
                    'data' => $data
                ]));
        }else {
            //add data to database
            $notification = new \models\Notification();
            $gov_response = $notification->addNotification($data);

            if ($gov_response) {
                //store info into database and gov delivery!
                $data['user'] = $gov_response;
            }

            //check to make sure everything was added correctly...
            $status_code = 200;
            $res_message = '';
            $res_data = false;

            foreach ($gov_response as $res) {
                if ($res['status'] !== 200) {
                    $status_code = $res['status'];
                    $res_message = $res['message'];
                    $res_data = $res['data'];
                } else {
                    break;
                }
            }


            if ($status_code == 200) {

                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode([
                        'response' => 'success',
                        'message' => 'Notification added',
                        'data' => $data
                    ]));
            } else {
                return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode([
                        'response' => 'fail',
                        'message' => $res_message,
                        'data' => $res_data
                    ]));
            }
        }
    }else{
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'response' => 'fail',
                'message' => 'Data Not Found'
            ]));
    }
});

$app->get('/send/{topic}/{when}/{date}/{event}', function($request, $response, $app){
    $topic = 'KYLOUISVILLE_' . $request->getAttribute('topic');
    $street_sweeping_date = $request->getAttribute('date');
    $when = $request->getAttribute('when');
    $event = $request->getAttribute('event');


    $notification = new \models\Notification();
    $gov_response = $notification->sendNotification($when, $street_sweeping_date, $topic, $event);


    if( $gov_response->getStatus() == 200 ){
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array('status'=>'success', 'message' => 'Message sent out successfully')));

    } else {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array('status'=>'fail', 'message' => 'Send failed "' . $gov_response->getReasonPhrase() . '" http_status ' . $gov_response->getStatus())));


    }
});