<?php

use lib\Core;
use lib\Config;


$app->get('/admin/notifications', function($request, $response, $app){
    $app['active_sidenav'] = 'notifications';
    return $this->view->render($response, 'admin/notifications.html', $app);
})->add($checkLogin);


$app->get('/admin/notifications/notificationTemplate', function($request, $response, $app){
    $notifications = new models\Notification();
    $params = $request->getParams();
    $id = isset($params['id']) ? filter_var($params['id'], FILTER_SANITIZE_STRING) : false;
    $app['areas'] = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
    $app['routes'] = array('50', '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
    $app['types'] = array('Day', 'Week', 'Cancel');

    if( $id )
    {
        $notification_data = $notifications->getNotificationQueue($id);
        if( $notification_data )
        {
            $app['notification_data'] = $notification_data;
        }
    }
    return $this->view->render($response, 'admin/modals/notificationtemplate.html', $app);
})->add($checkLogin);

$app->get('/admin/notifications/getPendingNotifications[/{params:.*}]', function($request, $response, $app){
    $datatables = new models\Datatables;
    $users = new models\Users();
    // DB table to use
    $table = 'message_queue';
    $primaryKey = 'id';
    $columns = array(
        array( 'db' => 'status', 'dt' => 0,),
        array( 'db' => 'type', 'dt' => 1),
        array( 'db' => 'area', 'dt' => 2 ),
        array( 'db' => 'route', 'dt' => 3 ),
        array(
            'db' => 'created_by',
            'dt' => 4,
            'formatter' => function($d, $row) use ($users){
                $user = $users->getUser($row['created_by']);
                if( $user )
                {
                    return $user['first_name'] . ' ' . $user['last_name'];
                } else {
                    return 'No User Data';
                }

            }
        ),
        array( 'db' => 'date_created','dt' => 5 ),
        array( 'db' => 'cleaning_date', 'dt' => 6, 'formatter' => function($id, $row){
            return datetimeformat($row['cleaning_date'], false, 'Y-m-d');
        }),
        array( 'db' => 'send_date','dt' => 7 ),
        array( 'db' => 'id', 'dt' => 8, 'formatter' => function($d, $row){
            $html = '<ul class="list-inline list-unstyled">';
            $html .= '<li><button id="sendNotification" class="btn btn-sm btn-info" data-id="'.$row['id'].'"><i class="fa fa-arrow-circle-o-right"></i></button></li>';
            $html .= '<li><button id="editNotification" class="btn btn-sm btn-warning" data-id="'.$row['id'].'"><i class="fa fa-edit"></i></button></li>';
            $html .= '<li><button id="deleteNotification" class="btn btn-sm btn-danger" data-id="'.$row['id'].'"><i class="fa fa-trash"></i></button></li>';
            $html .= '<ul>';
            return $html;
        })
    );

    $results = $datatables->complex( $_GET, $table, $primaryKey, $columns, "status IN('Queued', 'Processing')");

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($results));
})->add($checkLogin);

$app->get('/admin/notifications/getNotificationsLog[/{params:.*}]', function($request, $response, $app){
    $datatables = new models\Datatables;
    $users = new models\Users();
    // DB table to use
    $table = 'message_queue';
    $primaryKey = 'id';
    $columns = array(
        array( 'db' => 'status', 'dt' => 0,),
        array( 'db' => 'type', 'dt' => 1),
        array( 'db' => 'area', 'dt' => 2 ),
        array( 'db' => 'route', 'dt' => 3 ),
        array(
            'db' => 'created_by',
            'dt' => 4,
            'formatter' => function($d, $row) use ($users){
                $user = $users->getUser($row['created_by']);
                if( $user )
                {
                    return $user['first_name'] . ' ' . $user['last_name'];
                } else {
                    return 'No User Data';
                }

            }
        ),
        array( 'db' => 'date_created','dt' => 5 ),
        array( 'db' => 'cleaning_date', 'dt' => 6, 'formatter' => function($id, $row){
            return datetimeformat($row['cleaning_date'], false, 'Y-m-d');
        }),
        array( 'db' => 'send_date','dt' => 7 ),
        array( 'db' => 'date_finished','dt' => 8 )
    );
    $results = $datatables->complex( $_GET, $table, $primaryKey, $columns);

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($results));
})->add($checkLogin);

$app->post('/admin/notifications/savenotification', function($request, $response, $app){
    $validator = new lib\Validator();
    $error = false;
    if( $request->isPost() ) {
        $post_body = $request->getParsedBody();

        $data = array(
            'cleaning_date' => isset($post_body['cleaning_date']) ? filter_var($post_body['cleaning_date'], FILTER_SANITIZE_STRING) : false,
            'send_date' => isset($post_body['send_date']) ? filter_var($post_body['send_date'], FILTER_SANITIZE_STRING) : false,
            'area' => isset($post_body['area']) ? filter_var($post_body['area'], FILTER_SANITIZE_STRING) : false,
            'route' => isset($post_body['route']) ? filter_var($post_body['route'], FILTER_SANITIZE_STRING) : false,
            'type' => isset($post_body['type']) ? filter_var($post_body['type'], FILTER_SANITIZE_STRING) : false,
        );

        $constraint = array(
            'cleaning_date' => array(
                'require' => true,
            ),
            'send_date' => array(
                'require' => true,
            ),
            'area' => array(
                'require' => true,
            ),
            'route' => array(
                'require' => true,
            ),
            'type' => array(
                'require' => true
            )
        );

        $data_valid = $validator->validate($data, $constraint);


        if( $data_valid )
        {
            $notifications = new models\Notification();

            $data['created_by'] = $this->session->user['id'];
            $data['status'] = 'Queued';
            $data['date_created'] = datetimeformat();

            $createMessageQueue = $notifications->createMessageQueue($data);
            if( $createMessageQueue )
            {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'success',
                        'message' => 'Message added to queue!'
                    ]));
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'unable to add message to queue!'
                    ]));
            }
        } else {
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/Json')
                ->write(json_encode([
                    'status' => 'fail',
                    'message' => $validator->error_messages
                ]));
        }
    } else {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/Json')
            ->write(json_encode([
                'status' => 'fail',
                'message' => 'cannot read post body'
            ]));
    }

})->add($checkLogin);

$app->post('/admin/notifications/editnotification', function($request, $response, $app){
    $validator = new lib\Validator();
    $error = false;
    if( $request->isPost() ) {
        $post_body = $request->getParsedBody();

        $data = array(
            'id' => isset($post_body['id']) ? filter_var($post_body['id'], FILTER_SANITIZE_STRING) : false,
            'cleaning_date' => isset($post_body['cleaning_date']) ? filter_var($post_body['cleaning_date'], FILTER_SANITIZE_STRING) : false,
            'send_date' => isset($post_body['send_date']) ? filter_var($post_body['send_date'], FILTER_SANITIZE_STRING) : false,
            'area' => isset($post_body['area']) ? filter_var($post_body['area'], FILTER_SANITIZE_STRING) : false,
            'route' => isset($post_body['route']) ? filter_var($post_body['route'], FILTER_SANITIZE_STRING) : false,
            'type' => isset($post_body['type']) ? filter_var($post_body['type'], FILTER_SANITIZE_STRING) : false,
        );

        $constraint = array(
            'id' => array(
                'require' => true,
            ),
            'cleaning_date' => array(
                'require' => true,
            ),
            'send_date' => array(
                'require' => true,
            ),
            'area' => array(
                'require' => true,
            ),
            'route' => array(
                'require' => true,
            ),
            'type' => array(
                'require' => true
            )
        );

        $data_valid = $validator->validate($data, $constraint);


        if( $data_valid )
        {
            $notifications = new models\Notification();
            $id = $data['id'];
            $updateMessageQueue = $notifications->updateMessageQueue($id, $data);
            if( $updateMessageQueue  )
            {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'success',
                        'message' => 'Message updated!'
                    ]));
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'unable to update message!'
                    ]));
            }
        } else {
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/Json')
                ->write(json_encode([
                    'status' => 'fail',
                    'message' => $validator->error_messages
                ]));
        }
    } else {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/Json')
            ->write(json_encode([
                'status' => 'fail',
                'message' => 'cannot read post body'
            ]));
    }

})->add($checkLogin);

$app->post('/admin/notifications/deletenotification', function($request, $response, $app){
    $validator = new lib\Validator();
    if( $request->isPost() ) {
        $post_body = $request->getParsedBody();

        $data = array(
            'id' => isset($post_body['id']) ? filter_var($post_body['id'], FILTER_SANITIZE_STRING) : false
        );

        $constraint = array(
            'id' => array(
                'require' => true,
            )
        );

        $data_valid = $validator->validate($data, $constraint);

        if( $data_valid ) {
            $notifications = new models\Notification();

            $delete = $notifications->deleteMessageQueue($data['id']);
            if( $delete )
            {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'success',
                        'message' => 'Notification has been deleted'
                    ]));
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'Unable to delete notification from database'
                    ]));
            }
        } else {
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/Json')
                ->write(json_encode([
                    'status' => 'fail',
                    'message' => $validator->error_messages
                ]));
        }
    } else {
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/Json')
            ->write(json_encode([
                'status' => 'fail',
                'message' => 'cannot read post body'
            ]));
    }
})->add($checkLogin);

$app->get('/admin/notifications/sendnotification', function($request, $response, $app){
    $params = $request->getParams();
    $notifications = new models\Notification();
    $client = new \GuzzleHttp\Client();
    $domain = Config::read('system.domain');
    $id = isset($params['id']) ? filter_var($params['id'], FILTER_SANITIZE_STRING) : false;
    if( $id )
    {
        $notification_data = $notifications->getNotificationQueue($id);
        if( $notification_data )
        {
            $notifications->updateMessageQueue($id, array('status' => 'Processing'));
            ///send/{topic}/{when}/{date}/{event}
            //topic = area + route eg: 0104
            //when = type in the database
            //date is formatted like 05-10-2017
            //event = Signing (currently only event type)

            //datetime = false, $unix_format = false, $format = 'Y-m-d h:i:s'
            $request_string = $domain . '/send/' . $notification_data['area'] . $notification_data['route'] . '/'
                . strtolower($notification_data['type']) . '/'
                . datetimeformat($notification_data['cleaning_date'], false, 'm-d-Y')
                . '/signing';

            $res = $client->request('GET',$request_string);

            if( $res->getStatusCode() == 200 ) {
                $body = $res->getBody()->getContents();
                $return_data = json_decode($body, true);
                if( $return_data['status'] == 'success' ){
                    $notifications->updateMessageQueue($id, array('status' => 'Sent', 'date_finished' => datetimeformat()));
                    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/Json')
                        ->write(json_encode([
                            'status' => 'success',
                            'message' => 'Notification Processed'
                        ]));
                }else{
                    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/Json')
                        ->write(json_encode([
                            'status' => 'fail',
                            'message' => 'cannot process this entry'
                        ]));
                }
            }else{
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'cannot process this entry'
                    ]));
            }
        }
    }
})->add($checkLogin);

$app->get('/process_notifications/{hash_key}', function($request, $response, $app){
    $params = $request->getParams();
    $hash = $request->getAttribute('hash_key');
    $notifications = new models\Notification();
    $client = new \GuzzleHttp\Client();
    $domain = Config::read('system.domain');
    $system_api_key = Config::read('system.api_key');


    if( $system_api_key == $hash )
    {
        $notifications_data = $notifications->getQueuedNotifications();
        if( $notifications_data )
        {
            foreach( $notifications_data as $queue )
            {
                if( strtotime($queue['send_date']) <= strtotime(datetimeformat()) ) {
                    $notifications->updateMessageQueue($queue['id'], array('status' => 'Processing'));
                    ///send/{topic}/{when}/{date}/{event}
                    //topic = area + route eg: 0104
                    //when = type in the database
                    //date is formatted like 05-10-2017
                    //event = Signing (currently only event type)

                    //datetime = false, $unix_format = false, $format = 'Y-m-d h:i:s'
                    $request_string = $domain . '/send/' . $queue['area'] . $queue['route'] . '/'
                        . strtolower($queue['type']) . '/'
                        . datetimeformat($queue['cleaning_date'], false, 'm-d-Y')
                        . '/signing';

                    $res = $client->request('GET', $request_string);

                    if ($res->getStatusCode() == 200) {
                        $body = $res->getBody()->getContents();
                        $return_data = json_decode($body, true);
                        if ($return_data['status'] == 'success') {
                            $notifications->updateMessageQueue($queue['id'], array('status' => 'Sent', 'date_finished' => datetimeformat()));
                            echo 'Queue Process for: ' . $request_string . "\n";
                        } else {
                            echo 'Unable to Process Gov-delivery: ' . $request_string . "\n";
                            $notifications->updateMessageQueue($queue['id'], array('status' => 'Failed'));
                        }
                    } else {
                        echo 'Cannot load URL: ' . $request_string . "\n";
                        $notifications->updateMessageQueue($queue['id'], array('status' => 'Failed'));
                    }
                } else {
                    var_dump(datetimeformat(), strtotime($queue['send_date']), strtotime(datetimeformat()) );
                    echo 'Not ready to send...' . "\n";
                }
            }
        } else {
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/Json')
                ->write(json_encode([
                    'status' => 'success',
                    'message' => 'No Data'
                ]));
        }
    }
});

