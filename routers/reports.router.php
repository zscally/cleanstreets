<?php

use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;


$app->get('/admin/reports', function($request, $response, $app){
    $app['active_sidenav'] = 'reports';
    return $this->view->render($response, 'admin/reports.html', $app);
})->add($checkLogin);

$app->get('/admin/reports/exportsubscribers', function($request, $response, $app){
    $file = 'subscribers_' . time() . '.csv';
    $subscribers = new \models\Subscribers();
    $config = new ExporterConfig();
    $config->setColumnHeaders([
        'AlertID',
        'AlertTypeID',
        'PickupAreaID',
        'NotificationTypeID',
        'AlertX',
        'AlertY',
        'AlertAddressID',
        'AlertAddress',
        'license_id',
        'council_district',
        'NotificationValue',
        'first_name',
        'last_name',
        'NumberMissedNotifications',
        'DateAdded',
        'DateUpdated',
        'AlertDisableReason',
        'latitude',
        'longitude',
        'lojic_arearoute'
    ]);
    $exporter = new Exporter($config);
    $res = $response->withHeader('Content-Description', 'File Transfer')
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withHeader('Content-Disposition', 'attachment;filename="'.basename($file).'"')
        ->withHeader('Expires', '0')
        ->withHeader('Cache-Control', 'must-revalidate')
        ->withHeader('Pragma', 'public');


    $exporter->export('php://output', $subscribers->getSubscribers());
    return $res;

})->add($checkLogin);


$app->get('/admin/reports/exportnotificationlog', function($request, $response, $app){
    $file = 'notification_log_' . time() . '.csv';
    $notifications = new models\Notification();
    $config = new ExporterConfig();
    $config->setColumnHeaders([
        'id',
        'created_by',
        'status',
        'type',
        'area',
        'route',
        'cleaning_date',
        'send_date',
        'date_finished',
        'date_created'
    ]);
    $exporter = new Exporter($config);
    $res = $response->withHeader('Content-Description', 'File Transfer')
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withHeader('Content-Disposition', 'attachment;filename="'.basename($file).'"')
        ->withHeader('Expires', '0')
        ->withHeader('Cache-Control', 'must-revalidate')
        ->withHeader('Pragma', 'public');


    $exporter->export('php://output', $notifications->getAllNotifications());
    return $res;

})->add($checkLogin);