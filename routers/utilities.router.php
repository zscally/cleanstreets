<?php

use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

use lib\Config;

$app->get('/utilities', function($request, $response, $app){
    $app['active_sidenav'] = 'utilities';
    return $this->view->render($response, 'admin/utilities.html', $app);
})->add($checkLogin);

$app->get('/utilities/listtopics', function($request, $response, $app){
    $govDeliveryURL = Config::read('govDelivery.URL');
    $govDeliveryAccountCode = Config::read('govDelivery.accountCode');
    $govDeliveryUsername = Config::read('govDelivery.username');
    $govDeliveryPassword = Config::read('govDelivery.password');



    //send to gov delivery
    $request = new \HTTP_Request2 ( $govDeliveryURL . $govDeliveryAccountCode . '/topics.xml',
        \HTTP_Request2::METHOD_GET,
        array (
            'ssl_verify_peer'   => false,
            'ssl_verify_host'   => false
        )
    );
    $request->setAuth($govDeliveryUsername, $govDeliveryPassword, \HTTP_Request2::AUTH_BASIC)
        ->setHeader('Content-type: text/xml; charset=utf-8');

    $gov_response = $request->send(); //send off to gov delivery!
    if( 200 == $gov_response->getStatus() )
    {
        $topics = [];
        $service = new Sabre\Xml\Service();
        $result = $service->parse($gov_response->getBody());
        foreach( $result as $topic_array )
        {
            //var_dump($topic_array['value']);
            if( strstr($topic_array['value'][0]['value'], 'Street Sweeping') ) {
                $topics[] = [
                    'topic' => $topic_array['value'][2]['value'],
                    'topic_url' => $topic_array['value'][7]['value']
                ];
            }
        }


        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($topics, true));

    }

})->add($checkLogin);

$app->get('/utilities/syncgovdelivery', function($request, $response, $app){
    $subscribers = new models\Subscribers();

    $subscribers->dropgovtemp();
    $subscribers->creategovtemp();


    $subscribers_arr = array();
    $scan_dir_path = __DIR__ . '/../sql/govd/';
    $files = scandir($scan_dir_path);

    foreach( $files as $file )
    {
        if( $file != '.' && $file != '..' )
        {
            $config = new LexerConfig();
            $lexer = new Lexer($config);
            $interpreter = new Interpreter();
            $interpreter->addObserver(function(array $row) use (&$subscribers_arr, $file) {
                $subscribers_arr[] = array(
                    'area_route' => str_replace('.csv', '', $file),
                    'contact' => $row[0],
                    'origin'  => $row[1],
                    'created'  => $row[2],
                );

            });
            $lexer->parse($scan_dir_path. $file, $interpreter);
        }
    }

    //insert to DB
    foreach( $subscribers_arr as $sub )
    {
        if( $sub['created'] != 'Subscription Created' ) {
            $subscribers->createGovDeliverySubscriber($sub);
        }
    }


    // now get all the subscribers that we have and fine a match..
    $AlertNotifications = $subscribers->getSubscribers();
    foreach( $AlertNotifications as $sub )
    {
        $subscribs = $subscribers->getSubscriberFromGovDeliveryTable($sub['NotificationValue'], $sub['PickupAreaID']);
        foreach(  $subscribs as $subb )
        {
            $data = array('found' => 1);
            $subscribers->updateGovDeliveryTable($subb['id'], $data);
        }
    }
})->add($checkLogin);

$app->get('/utilities/checkgovdeliverytable', function($request, $response, $app){
    // now get all the subscribers that we have and fine a match..
    $subscribers = new models\Subscribers();
    $AlertNotifications = $subscribers->getSubscribers();
    foreach( $AlertNotifications as $subv )
    {
        if( $subv ) {
            $subscribs = $subscribers->getSubscriberFromGovDeliveryTable($subv['NotificationValue'], $subv['PickupAreaID']);
            if( $subscribs ) {
                foreach ($subscribs as $subb) {
                    $data = array('found' => 1);
                    $subscribers->updateGovDeliveryTable($subb['id'], $data);
                }
            }
        }
    }
})->add($checkLogin);

$app->get('/utilities/testapi', function($request, $response, $app){
    $app['active_sidenav'] = 'utilities';
    return $this->view->render($response, 'admin/testapi.html', $app);
})->add($checkLogin);

$app->get('/utilities/buildlojictable', function(){
    $subscribers = new models\Subscribers();
    $curl = new Curl\Curl();
    //first build new stage table...
    $subscribers->buildAlertNotificationStagingTable();
    $token = '';
    //grab all subs
    $AlertNotifications = $subscribers->getDistinctSubAddresses();
    foreach ($AlertNotifications as $sub)
    {
        //make call to lojic
        $x = $sub['AlertX'];
        $y = $sub['AlertY'];
        $url = "https://ags1.lojic.org/arcgis/rest/services/LOJIC/MetroServices/MapServer/exts/MetroServicesRestSoe/GetReport?token=$token&InputPoint=%7B%22x%22%3A$x%2C%20%22y%22%3A$y%7D&f=json";
        //$url = 'https://google.com';
        $curl->setHeader('Referer', 'https://louisvilleky.gov');
        $curl->get($url);

        if( $curl->response ) {
            $json_payload = json_decode($curl->response, true);


            if ($json_payload['ErrorMessage'] == 'None') {
                $sss = $json_payload['StreetSweeping'];

                foreach ($sss['Routes'] as $route) {
                    $arearoute = $route['AreaRoute'];
                    unset($sub['AlertID']);
                    $subscriber = $sub;
                    $subscriber['lojic_arearoute'] = $arearoute;
                    $subscribers->createStageSubscriber($subscriber);
                z}
            }
        }
    }
})->add($checkLogin);

function createBulletin($message, $subject, $topic, $sms_message = false)
{
    $xml = new \SimpleXMLElement('<bulletin />');
    $xml->addChild('body', "<![CDATA['".trim($message)."']]>");
    if( $sms_message )
    {
        $sms = $xml->addChild('sms_body', $sms_message);
        $sms->addAttribute('nil', 'true');
    }
    else
    {
        $sms = $xml->addChild('sms_body', '');
        $sms->addAttribute('nil', 'false');
    }
    $xml->addChild('subject', $subject);
    $topics = $xml->addChild('topics');
    $topics->addAttribute('type', 'array');
    $topic_xml = $topics->addChild('topic');
    $topic_xml->addChild('code', $topic);
    $open_tracking = $xml->addChild('open_tracking', 'false');
    $open_tracking->addAttribute('type', 'boolean');
    $click_tracking = $xml->addChild('click_tracking', 'false');
    $click_tracking->addAttribute('type', 'boolean');
    return $xml->asXML();
}

function createSubscriber($email, $topic){
    $xml = new \SimpleXMLElement('<subscriber/>');
    $xml->addChild('email', $email);
    $notification = $xml->addChild('send-notifications', 'true');
    $notification->addAttribute('type', 'boolean');
    $topics = $xml->addChild('topics');
    $topics->addAttribute('type', 'array');
    $topic_xml = $topics->addChild('topic');
    $topic_xml->addChild('code', $topic);
    return $xml->asXML();
}
