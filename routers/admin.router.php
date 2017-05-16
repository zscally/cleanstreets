<?php

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;

$app->any('/admin/login', function($request, $response, $app){
    if( $request->isPost() ) {
        //validate required fields!
        $error = [];
        $required = array(
            'email' => 'email is required',
            'password' => 'Password is required'
        );

        foreach ($required as $field => $message) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $error[] = $message;
            }
        }

        if( ! $error )
        {
            $users = new models\Users();
            $user_data = $users->getUserByEmail( filter_var( $_POST['email'], FILTER_SANITIZE_STRING ) );
            if( $user_data )
            {
                //validate password
                if( $users->passwordVerifyHash($_POST['password'], $user_data['password'] ) )
                {
                    $this->session->user = $user_data;
                    return $response->withRedirect('/admin');
                } else {
                    $app['errors']  = array('Password or user do not match our records!');
                }
            } else {
                $app['errors']  = array('User is not in our records!');
            }
        } else {
            $app['errors'] = array($error);
        }
    }

    return $this->view->render($response, 'admin/login.html', $app);
});

$app->get('/admin/logout', function($request, $response, $app){
    $this->session->destroy();
    return $response->withRedirect('/admin/login');
})->add($checkLogin);

$app->get('/admin', function($request, $response, $app){
    $app['active_sidenav'] = 'index';
    return $this->view->render($response, 'admin/index.html', $app);
})->add($checkLogin);

$app->get('/admin/getsubscribersbyarearoute', function($request, $response, $app){
    $subscribers = new \models\Subscribers();
    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($subscribers->getSubscribersByAreaRoute()));
})->add($checkLogin);

$app->get('/admin/subscribers', function($request, $response, $app){
    $app['active_sidenav'] = 'subscribers';
    return $this->view->render($response, 'admin/subscribers.html', $app);
})->add($checkLogin);

$app->get('/admin/getsubscribertemplate/{subscriber_id}', function($request, $response, $app){
    $subscribers = new models\Subscribers();
    $subscriber = $subscribers->getSubscriberById($app['subscriber_id']);
    $app['subscriber'] = $subscriber;
    $app['canned_comments'] = $subscribers->getCannedComments();
    return $this->view->render($response, 'admin/modals/subscriber_info.html', $app);
})->add($checkLogin);

$app->get('/admin/getsubscribers[/{params:.*}]', function($request, $response, $app){
    $subscribers = new \models\Subscribers();
    $datatables = new models\Datatables;

    // DB table to use
    $table = 'AlertNotification';
    $primaryKey = 'AlertID';
    $columns = array(
        array(
            'db' => 'AlertID',
            'dt' => 0,
            'formatter' => function($d, $row) use ($subscribers){
                $comments = $subscribers->getSubscriberComments($row['AlertID']);
                if( $comments ) {
                    $count = count(array_filter($comments));
                } else {
                    $count = 0;
                }
                return '<button class="btn btn-primary showSubscriberModal" data-subscriber-id="'.$d.'"><i data-count="'.$count.'" class="fa fa-2x fa-comments comment-badge"></i></button>';
            }
        ),
        array( 'db' => 'PickupAreaID','dt' => 1),
        array( 'db' => 'AlertAddress',     'dt' => 2 ),
        array( 'db' => 'license_id',     'dt' => 3 ),
        array( 'db' => 'council_district',     'dt' => 4 ),
        array( 'db' => 'first_name','dt' => 5 ),
        array( 'db' => 'last_name','dt' => 6 ),
        array( 'db' => 'NotificationValue',     'dt' => 7 ),
        array( 'db' => 'DateAdded', 'dt' => 8 )
    );

    $results = $datatables->complex( $_GET, $table, $primaryKey, $columns, null, 'NotificationTypeID != 2');

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($results));


})->add($checkLogin);

$app->post('/admin/deletecomment', function($request, $response, $app){
    if( $request->isPost() )
    {
        $post_body = $request->getParsedBody();
        if( ! empty( $post_body['commit_id'] ) ){
            $comment_id = $post_body['commit_id'];
            $subscribers = new models\Subscribers();

            $delete = $subscribers->deleteComment($comment_id);

            if( $delete )
            {
                $json_payload = json_encode(
                    [
                        'status' => 'success'
                    ]
                );
            }
            else
            {
                $json_payload = json_encode(
                    [
                        'status' => 'fail'
                    ]
                );
            }
        } else {
            $json_payload = json_encode(
                [
                    'status' => 'fail'
                ]
            );
        }

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write($json_payload);
    }
})->add($checkLogin);

$app->post('/admin/savecomment', function($request, $response, $app){
    if( $request->isPost() )
    {
        $post_body = $request->getParsedBody();
        if( ! empty( $post_body['comment'] ) && ! empty( $post_body['subscriber_id'] ) ){

            $data = array(
                'created_by' => $this->session->user['id'],
                'subscriber_id' => filter_var($post_body['subscriber_id'], FILTER_SANITIZE_NUMBER_INT),
                'comment' => filter_var( $post_body['comment'], FILTER_SANITIZE_STRING ),
                'date_created' => datetimeformat(),
                'date_modified' => datetimeformat(),
                'is_active' => 1
            );

            $subscribers = new models\Subscribers();

            $insert = $subscribers->createSubscriberComment($data);
            if( $insert )
            {
                //get the user for the inserted comment
                $users = new models\Users();
                $user_data = $users->getUser($data['created_by']);
                $data['first_name'] = $user_data['first_name'];
                $data['last_name'] = $user_data['last_name'];

                $json_payload = json_encode(
                    [
                        'status' => 'success',
                        'data' => $data
                    ]
                );
            }
            else
            {
                $json_payload = json_encode(
                    [
                        'status' => 'fail'
                    ]
                );
            }

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write($json_payload);
        }
    }
})->add($checkLogin);

$app->get('/admin/convertxy', function($request, $response, $app){
    $subscribers = new \models\Subscribers();

    $subscribers_data = $subscribers->getSubscribers("latitude = '' OR latitude IS NULL");

    $result = array();
    if( ! empty( $subscribers_data ) ) {
        foreach ($subscribers_data as $sub) {
            $x = $sub['AlertX'];
            $y = $sub['AlertY'];
            $proj4 = new Proj4php();
            $proj4->addDef("EPSG:2246", '+proj=lcc +lat_1=37.96666666666667 +lat_2=38.96666666666667 +lat_0=37.5 +lon_0=-84.25 +x_0=500000.0001016001 +y_0=0 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs');
            $proj2246 = new Proj('EPSG:2246', $proj4);
            $projWGS84 = new Proj('EPSG:4326', $proj4);
            $pointSrc = new Point($x, $y, $proj2246);
            $pointDest = $proj4->transform($projWGS84, $pointSrc);
            $lat = $pointDest->y;
            $lng = $pointDest->x;

            $result = [
                'latitude' => $lat,
                'longitude' => $lng
            ];

            $subscribers->updateSubscriber($sub['AlertID'], $result);
        }

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($result, true));
    }
    else{
        echo 'no conversion to be done.';
    }
})->add($checkLogin);

$app->get('/admin/getmapdata', function($request, $response, $app){
    $subscribers = new \models\Subscribers();

    $mapdata = $subscribers->getMapData();

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($mapdata, true));

})->add($checkLogin);

$app->get('/admin/chartdata/[{date}]', function($request, $response, $app){
    $date = (isset($app['date']) && ! empty($app['date']) ? $app['date'] : null);
    $subscribers = new models\Subscribers();
    $subbydate = $subscribers->getSubscribersByDate($date);

    $data = [
        'subbydate' => $subbydate
    ];

    $json_payload = json_encode($data);

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write($json_payload);

})->add($checkLogin);

$app->any('/admin/adduser', function($request, $response, $app){
    $users = new models\Users();

    if( $request->isPost() ) {
        //validate required fields!
        $app['user_data'] = $request->getParsedBody();
        $error = [];
        $required = array(
            'first_name' => 'First Name is required!',
            'last_name' => 'Last Name is required!',
            'email' => 'Email / Login is required!',
            'role' => 'User Role is required!',
            'password' => 'Password is required!'
        );

        foreach ($required as $field => $message) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $error[] = $message;
            }
        }

        if( $users->userExists($app['user_data']['email']) ) $error[] = 'Email already registered!';

        if( ! $error )
        {
            $created_user2 = $users->createUser(
                [
                    'first_name' => filter_var($app['user_data']['first_name'], FILTER_SANITIZE_STRING),
                    'last_name' => filter_var($app['user_data']['last_name'], FILTER_SANITIZE_STRING),
                    'email' => filter_var($app['user_data']['email'], FILTER_SANITIZE_STRING),
                    'password' => $users->passwordHash($app['user_data']['password']),
                    'role' => filter_var($app['user_data']['role'], FILTER_SANITIZE_STRING),
                    'created_at' => datetimeformat(),
                    'updated_at' => datetimeformat(),
                    'is_active' => 1
                ]
            );

            if( ! $created_user2 ) {
                $app['errors'] = array('unable to create user!');
            } else {
                $app['successes'] = array('User has been created!');
            }

        }else{
            $app['errors'] = $error;
        }
    }

    $app['active_sidenav'] = 'adduser';
    return $this->view->render($response, 'admin/adduser.html', $app);
})->add($checkLogin);

$app->any('/admin/resetpassword', function($request, $response, $app){
    if( $request->isPost() ) {
        $users = new models\Users();
        //validate required fields!
        $error = [];
        $app['user_data'] = $request->getParsedBody();
        $required = array(
            'password' => 'Password is required!',
            'password2' => 'Repeat is required!'
        );

        foreach ($required as $field => $message) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $error[] = $message;
            }
        }

        if( $app['user_data']['password'] != $app['user_data']['password2'] ) $error[] = 'Passwords do not match!';

        if( ! $error )
        {
            $update_user = $users->updateUser(
                [
                    'password' => $users->passwordHash($app['user_data']['password']),
                    'updated_at' => datetimeformat()
                ],
                $this->session->user['id']
            );


            if( ! $update_user ) {
                $app['errors'] = array('unable to update user!');
            } else {
                $app['successes'] = array('Password has been reset!');
            }

        }else{
            $app['errors'] = $error;
        }
    }

    $app['active_sidenav'] = 'resetpassword';
    return $this->view->render($response, 'admin/resetpassword.html', $app);
})->add($checkLogin);

$app->get('/admin/heartbeat', function($request, $response, $app){

  $json_payload = json_encode(['status'=>'success']);

  return $response->withStatus(200)
    ->withHeader('Content-Type', 'application/json')
    ->write($json_payload);

})->add($checkLogin);

$app->get('/admin/manageusers', function($request, $response, $app){
    $users = new models\Users();

    $app['active_sidenav'] = 'manageusers';
    return $this->view->render($response, 'admin/manageusers.html', $app);
})->add($checkLogin);

$app->get('/admin/usertemplate', function($request, $response, $app){
    $user = $this->session->user;
    $params = $request->getParams();
    $id = isset($params['id']) ? filter_var($params['id'], FILTER_SANITIZE_STRING) : false;
    if( $id )
    {
        $users = new models\Users();
        $app['userdata'] = $users->getUser($id);
    }

    if( $user['role'] == "1" ) {
        $app['roles'] = array('1' => 'Admin', '3' => 'Supervisor', '2' => 'Member');
    } else {
        $app['roles'] = array('2' => 'Member', '3' => 'Supervisor');
    }
    return $this->view->render($response, 'admin/modals/usertemplate.html', $app);
})->add($checkLogin);

$app->get('/admin/getUsers[/{params:.*}]', function($request, $response, $app){
    $datatables = new models\Datatables();
    $users = new models\Users();
    $user = $this->session->user;

    // DB table to use
    $table = 'users';
    $primaryKey = 'id';
    $columns = array(
        array( 'db' => 'first_name', 'dt' => 0),
        array( 'db' => 'last_name', 'dt' => 1),
        array(
            'db' => 'email',
            'dt' => 2,
            'formatter' => function($d, $row){
                return '<a href="mailto:'.$row['email'].'">'.$row['email'].'</a>';
            }),
        array(
            'db' => 'role',
            'dt' => 3,
            'formatter' => function($d, $row){

                switch( $row['role'] )
                {
                    case "1":
                        $role = 'Admin';
                        break;
                    case "2":
                        $role = 'Member';
                        break;
                    case "3":
                        $role = 'Supervisor';
                        break;
                }

                return $role;
            }
        ),
        array( 'db' => 'created_at', 'dt' => 4 ),
        array(
            'db' => 'is_active',
            'dt' => 5,
            'formatter' => function($d, $row){
                return ( $row['is_active'] == 1 ? 'Yes' : 'No' );
            }
        ),
        array(
            'db' => 'id',
            'dt' => 6,
            'formatter' => function($d, $row) use($user){
                $html = '';
                if( $user['role'] == "1" || $row['role'] != "1" ) {
                    $html = '
                        <ul class="list-inline list-unstyled">
                            <li><button class="btn btn-warning" id="edituser" data-user-id="' . $row['id'] . '"><i class="fa fa-pencil"></i></button></li>
                    ';
                    if( $user['id'] != $row['id'] ) {
                        $html .='<li><button class="btn btn-danger" id="deleteuser" data-user-id="' . $row['id'] . '"><i class="fa fa-trash"></i></button></li>';
                    }
                    $html .='</ul>';

                }
                return $html;
            }
        )
    );

    $results = $datatables->complex( $_GET, $table, $primaryKey, $columns);

    return $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($results));
})->add($checkLogin);

$app->post('/admin/createuser', function($request, $response, $app){
    $users = new models\Users();
    $validator = new lib\Validator();
    $error = false;
    if( $request->isPost() ) {
        $post_body = $request->getParsedBody();
        $data = array(
            'first_name' => isset($post_body['first_name']) ? filter_var($post_body['first_name'], FILTER_SANITIZE_STRING) : false,
            'last_name' => isset($post_body['last_name']) ? filter_var($post_body['last_name'], FILTER_SANITIZE_STRING) : false,
            'email' => isset($post_body['email']) ? filter_var($post_body['email'], FILTER_SANITIZE_STRING) : false,
            'password' => isset($post_body['password']) ? filter_var($post_body['password'], FILTER_SANITIZE_STRING) : false,
            'password2' => isset($post_body['password2']) ? filter_var($post_body['password2'], FILTER_SANITIZE_STRING) : false,
            'role' => isset($post_body['role']) ? filter_var($post_body['role'], FILTER_SANITIZE_STRING) : false,
            'is_active' => isset($post_body['is_active']) ? filter_var($post_body['is_active'], FILTER_SANITIZE_STRING) : false,
        );

        $constraint = array(
            'first_name' => array(
                'require' => true,
            ),
            'last_name' => array(
                'require' => true,
            ),
            'email' => array(
                'require' => true,
                'validate' => 'email',
            ),
            'role' => array(
                'require' => true,
            ),
            'password' => array(
                'require' => true
            ),
            'password2' => array(
                'require' => true
            ),
            'is_active' => array(
                'require' => true
            )
        );

        $data_valid = $validator->validate($data, $constraint);

        if( $data_valid ) {
            if ( ! $users->userExists($data['email'])) {
                if( $data['password'] == $data['password2'] ) {

                    $data['created_at'] = datetimeformat();
                    $data['updated_at'] = datetimeformat();
                    $data['password'] = $users->passwordHash($data['password']);

                    $createuser = $users->createUser($data);

                    if( $createuser )
                    {
                        return $response->withStatus(200)
                            ->withHeader('Content-Type', 'application/Json')
                            ->write(json_encode([
                                'status' => 'success',
                                'message' => 'User has been created!'
                            ]));
                    } else {
                        return $response->withStatus(200)
                            ->withHeader('Content-Type', 'application/Json')
                            ->write(json_encode([
                                'status' => 'fail',
                                'message' => 'Unable to created user!'
                            ]));
                    }
                }else{
                    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/Json')
                        ->write(json_encode([
                            'status' => 'fail',
                            'message' => 'Passwords do not match!'
                        ]));
                }
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'User email already exists in our system!'
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
    }
})->add($checkLogin);

$app->post('/admin/edituser', function($request, $response, $app) {
    $users = new models\Users();
    $validator = new lib\Validator();
    $error = false;
    if ($request->isPost()) {
        $post_body = $request->getParsedBody();
        $data = array(
            'id' => isset($post_body['id']) ? filter_var($post_body['id'], FILTER_SANITIZE_STRING) : false,
            'first_name' => isset($post_body['first_name']) ? filter_var($post_body['first_name'], FILTER_SANITIZE_STRING) : false,
            'last_name' => isset($post_body['last_name']) ? filter_var($post_body['last_name'], FILTER_SANITIZE_STRING) : false,
            'email' => isset($post_body['email']) ? filter_var($post_body['email'], FILTER_SANITIZE_STRING) : false,
            'password' => isset($post_body['password']) ? filter_var($post_body['password'], FILTER_SANITIZE_STRING) : false,
            'password2' => isset($post_body['password2']) ? filter_var($post_body['password2'], FILTER_SANITIZE_STRING) : false,
            'role' => isset($post_body['role']) ? filter_var($post_body['role'], FILTER_SANITIZE_STRING) : false,
            'is_active' => isset($post_body['is_active']) ? filter_var($post_body['is_active'], FILTER_SANITIZE_STRING) : false,
        );

        $constraint = array(
            'id' => array(
                'require' => true,
            ),
            'first_name' => array(
                'require' => true,
            ),
            'last_name' => array(
                'require' => true,
            ),
            'email' => array(
                'require' => true,
                'validate' => 'email',
            ),
            'role' => array(
                'require' => true,
            ),
            'is_active' => array(
                'require' => true
            )
        );

        $data_valid = $validator->validate($data, $constraint);

        if ($data_valid) {


            if (!empty($data['password'])) {
                if ($data['password'] == $data['password2']) {

                    $data['password'] = $users->passwordHash($data['password']);

                } else {

                    return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/Json')
                        ->write(json_encode([
                            'status' => 'fail',
                            'message' => 'Passwords do not match!'
                        ]));

                }
            }

            $data['updated_at'] = datetimeformat();
            $saveuser = $users->updateUser($data, $data['id']);

            if ($saveuser) {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'success',
                        'message' => 'User has been created!'
                    ]));
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'Unable to created user!'
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
    }
})->add($checkLogin);

$app->post('/admin/deleteuser', function($request, $response, $app){
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
            $users = new models\Users();
            $delete = $users->deleteUser($data['id']);
            if( $delete )
            {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'success',
                        'message' => 'User has been deleted'
                    ]));
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/Json')
                    ->write(json_encode([
                        'status' => 'fail',
                        'message' => 'Unable to delete user from database'
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