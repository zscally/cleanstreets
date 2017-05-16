<?php

namespace models;
use lib\Core;
use lib\Config;
use lib\DB;
use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;


class Notification
{

    protected $db;

    function __construct()
    {
        $this->db = \lib\DB::getInstance();
    }

    public function getAccount($account){
        $data = $this->db->select('AlertNotification', "NotificationValue = ':account'", array(':account' => $account));
        if( count($data) > 0 ){
            return $data[0];
        }
        return false;
    }


    public function getSignups()
    {
        $data = $this->db->select('AlertNotification');
        if( count($data) > 0 ){
            return $data;
        }
        return false;
    }


    public function getNotificationByEmail($userEmail, $address)
    {
        $data = $this->db->select('AlertNotification', 'NotificationValue = :email AND AlertAddress = :address', array(':email' => $userEmail, ':address' => $address));
        if (count($data) > 0) {
            return $data[0];
        }
        return false;
    }

    public function getNotificationByPhone($userPhone, $address)
    {
        $data = $this->db->select('AlertNotification', 'NotificationValue = :phone AND AlertAddress = :address', array(':phone' => $userPhone, ':address' => $address));
        if (count($data) > 0) {
            return $data[0];
        }
        return false;
    }

    public function enableNotification($id)
    {
        $data = $this->db->update('AlertNotification', array('AlertDisableReason' => false), 'AlertID = :AlertID', array(':AlertID' => $id));
        if ($data) {
            return true;
        }
        return false;
    }

    public function createMessageQueue($data)
    {
        $insert = $this->db->insert('message_queue', $data);
        if($insert){
            return true;
        }
        return false;
    }

    public function updateMessageQueue($id, $data)
    {
        $update = $this->db->update('message_queue', $data, 'id=:id', array(':id' => $id));
        if( $update )
        {
            return true;
        }
        return false;
    }

    public function deleteMessageQueue($id)
    {
        $update = $this->db->update('message_queue', array('status' => 'Deleted', 'date_finished' => datetimeformat()), 'id=:id', array(':id'=>$id));
        if( $update )
        {
            return true;
        }
        return false;
    }

    public function getNotificationQueue($id)
    {
        $select = $this->db->select('message_queue', 'id=:id', array(':id'=>$id));
        if( $select )
        {
            return $select[0];
        }
        return false;
    }

    public function getQueuedNotifications()
    {
        $select = $this->db->select('message_queue', 'status=:status', array(':status'=>'Queued'));
        if( $select )
        {
            return $select;
        }
        return false;
    }

    public function getAllNotifications()
    {
        $select = $this->db->select('message_queue');
        if($select)
        {
            return $select;
        }
        return false;
    }

    /*******
     * @param $notificationValue
     * @param array $topic_list
     * @return bool
     * @throws \Exception
     * @throws \HTTP_Request2_LogicException
     */
    public function addUserToGovDelivery($notificationValue, array $topic_list)
    {
        $govDeliveryURL = Config::read('govDelivery.URL');
        $govDeliveryAccountCode = Config::read('govDelivery.accountCode');
        $govDeliveryUsername = Config::read('govDelivery.username');
        $govDeliveryPassword = Config::read('govDelivery.password');

        $govDeliveryRequest = new \HTTP_Request2(
            $govDeliveryURL . $govDeliveryAccountCode . '/subscriptions',
            \HTTP_Request2::METHOD_POST,
            array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false
            )
        );
        $govDeliveryRequest->setAuth($govDeliveryUsername, $govDeliveryPassword, \HTTP_Request2::AUTH_BASIC);


        $request_body =
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
            "  <subscriber>";

        if(substr_count($notificationValue, "@") > 0)
        {
            $request_body .= "   <email>".$notificationValue."</email>";
        }
        else
        {
            $request_body .= "   <phone>".$notificationValue."</phone>";
            $request_body .= "   <country-code>1</country-code>";
        }

        $request_body .= "   <send-notifications type='boolean'>true</send-notifications>";
        $request_body .= "  <topics type='array'>";

        foreach($topic_list as $topic)
        {
            $request_body .= "    <topic>";
            $request_body .= "     <code>$topic</code>";
            $request_body .= "    </topic>";
        }
        $request_body .= "  </topics>";
        $request_body .= "  </subscriber>";

        $govDeliveryRequest->setHeader('Content-type: text/xml; charset=utf-8')->setBody($request_body);
        $response = $govDeliveryRequest->send();

        return array('status' => $response->getStatus(), 'message' => $response->getReasonPhrase(), 'data' => $response);
    }


    /*******
     * @param $notificationValue
     * @param array $topic_list
     * @return bool
     * @throws \Exception
     * @throws \HTTP_Request2_LogicException
     */
    public function deleteUserFromTopic($notificationValue, array $topic_list)
    {
        $govDeliveryURL = Config::read('govDelivery.URL');
        $govDeliveryAccountCode = Config::read('govDelivery.accountCode');
        $govDeliveryUsername = Config::read('govDelivery.username');
        $govDeliveryPassword = Config::read('govDelivery.password');

        $govDeliveryRequest = new \HTTP_Request2(
            $govDeliveryURL . $govDeliveryAccountCode . '/subscriptions',
            \HTTP_Request2::METHOD_,
            array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false
            )
        );

        $govDeliveryRequest->setAuth($govDeliveryUsername, $govDeliveryPassword, \HTTP_Request2::AUTH_BASIC);


        $request_body =
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
            "  <subscriber>";

        if(substr_count($notificationValue, "@") > 0)
        {
            $request_body .= "   <email>".$notificationValue."</email>";
        }
        else
        {
            $request_body .= "   <phone>".$notificationValue."</phone>";
            $request_body .= "   <country-code>1</country-code>";
        }

        $request_body .= "   <send-notifications type='boolean'>true</send-notifications>";
        $request_body .= "  <topics type='array'>";

        foreach($topic_list as $topic)
        {
            $request_body .= "    <topic>";
            $request_body .= "     <code>$topic</code>";
            $request_body .= "    </topic>";
        }
        $request_body .= "  </topics>";
        $request_body .= "  </subscriber>";

        $govDeliveryRequest->setHeader('Content-type: text/xml; charset=utf-8')->setBody($request_body);
        $response = $govDeliveryRequest->send();

        return array('status' => $response->getStatus(), 'message' => $response->getReasonPhrase(), 'data' => $response);
    }

    /***
     * @param $data
     * @param $notificationValue
     * @param $type 1 = Email, 2 = phone
     * @param $topic
     * @throws \Exception
     * @throws \HTTP_Request2_LogicException
     */
    public function addUserNotification($data, $notificationValue, $type = 1)
    {
        $data['NotificationValue'] = $notificationValue;
        $data['NotificationTypeID'] = $type;
        $insert = $this->db->insert('AlertNotification', $data);
        if( $insert )
        {
            return true;
        }

        return false;
    }

    /********
     * @param $data
     * @return array
     */
    public function addNotification($data)
    {
        $status_msg = '';
        $notificationEmailData = $this->getNotificationByEmail($data['emailAddress'], $data['mailingAddress']);
        $notificationPhoneData = $this->getNotificationByPhone($data['phoneNumber'], $data['mailingAddress']);

        if (! empty( $notificationEmailData ) && $notificationEmailData['AlertDisableReason'] == true) {
            $this->enableNotification($notificationEmailData['AlertID']);
        }

        if (! empty( $notificationPhoneData ) && $notificationPhoneData['AlertDisableReason'] == true) {
            $this->enableNotification($notificationPhoneData['AlertID']);
        }

        //create topics string
        $topics = array();
        $insert_sub = false;
        $error = false;
        foreach( $data['route'] as $route ){

            //converts the X / Y back to lat long
            $x = $data['x'];
            $y = $data['y'];
            $proj4 = new Proj4php();
            $proj4->addDef("EPSG:2246", '+proj=lcc +lat_1=37.96666666666667 +lat_2=38.96666666666667 +lat_0=37.5 +lon_0=-84.25 +x_0=500000.0001016001 +y_0=0 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs');
            $proj2246 = new Proj('EPSG:2246', $proj4);
            $projWGS84 = new Proj('EPSG:4326', $proj4);
            $pointSrc = new Point($x, $y, $proj2246);
            $pointDest = $proj4->transform($projWGS84, $pointSrc);
            $lat = $pointDest->y;
            $lng = $pointDest->x;

            $newUserData = array( //prep for new data
                'PickupAreaID' => "" . $data['area'] . filter_var($route, FILTER_SANITIZE_STRING) . "",
                'AlertTypeID' => 1,//ID 1 == Street Sweeping Alerts
                'AlertAddressID' => 0,
                'AlertAddress' => $data['mailingAddress'],
                'license_id' => $data['license_id'],
                'council_district' => $data['council_district'],
                'AlertX' => $data['x'],
                'AlertY' => $data['y'],
                'NumberMissedNotifications' => 0,
                'DateAdded' => date("Y-m-d H:i:s"),
                'DateUpdated' => date("Y-m-d H:i:s"),
                'AlertDisableReason' => false,
                'latitude' => $lat,
                'longitude' => $lng,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name']
            );

            if (!$notificationEmailData && !$notificationPhoneData) {
                //add notifcation email data
                $insert_sub = $this->addUserNotification($newUserData, $data['emailAddress'], 1);
                if( !empty( $data['phoneNumber'] ) ) {
                    $insert_sub = $this->addUserNotification($newUserData, formatPhone($data['phoneNumber']), 2);
                }

                if( $insert_sub )
                {
                    $status_msg = 'Notification(s) added';
                }

            } elseif (!$notificationEmailData) {
                $insert_sub = $this->addUserNotification($newUserData, $data['emailAddress'], 1);
                if( $insert_sub ) {
                    $status_msg = 'Email notification added';
                }
            } elseif (!$notificationPhoneData && !empty( $data['phoneNumber'] )) {
                $insert_sub = $this->addUserNotification($newUserData, formatPhone($data['phoneNumber']), 2);
                if( $insert_sub ) {
                    $status_msg = 'Phone notification added';
                }
            } else {
                $insert_sub = true;
            }
            $topics[] = "KYLOUISVILLE_" . $data['area'].$route;
        }

        if( ! $insert_sub )
        {
            $error[] = 'Unable to subscribe at this time!';
        }



        if( ! $error )
        {
            //add them to the global list;
            $topics[] = 'KYLOUISVILLE_' . $data['area'] . '50';

            //blast off user to gov delivery email
            $addusertogovresponse = array();
            $addusertogovresponse[] = $this->addUserToGovDelivery($data['emailAddress'], $topics);

            if (!empty($data['phoneNumber'])) {
                $addusertogovresponse[] = $this->addUserToGovDelivery($data['phoneNumber'], $topics);

            }
            return $addusertogovresponse;
        } else {
            $response[] = array(
                'status' => 500,
                'message' => $status_msg . ' - ' . $error,
                'data' => $data
            );
            return $response;
        }
    }

    public function sendNotification( $when, $street_sweeping_date, $topic, $event )
    {
        $govDeliveryURL = Config::read('govDelivery.URL');
        $govDeliveryAccountCode = Config::read('govDelivery.accountCode');
        $govDeliveryUsername = Config::read('govDelivery.username');
        $govDeliveryPassword = Config::read('govDelivery.password');
        $bsubject = "Street Sweeper Notification:";

        if( $when == 'week' ) //week or day
        {

            $bbody  = "<p><img src='https://louisvilleky.gov/sites/default/files/serviceimages/streetsweeper_original.jpg'></p><p>Your street will be swept next week. Watch for NO PARKING signs that will be posted the day before sweeping occurs and be prepared to find alternate parking. You will receive a separate email on the day that the NO PARKING signs are posted. Vehicles parked in the NO PARKING area may be ticketed or towed. Thanks in advance for helping to keep our city clean.</p>";
            $bbody .= "<p>Metro Public Works Team</p>";
            $bsms   = "Your street will be swept next week. Watch for NO PARKING signs that will be posted the day before sweeping occurs.";

        }
        elseif( $when == 'day' )
        {
            switch( strtolower( $event ) )
            {

                case 'signing':
                    $bbody  = "<p><img src='https://louisvilleky.gov/sites/default/files/serviceimages/streetsweeper_original.jpg'></p>";
                    $bbody .= "<p>Metro Public Works will be sweeping your street <b>".$street_sweeping_date."</b></p>";
                    $bbody .= "<p>Your street will be a <b>NO PARKING ZONE</b> between the hours of 7 a.m. and 5 p.m. <b>".$street_sweeping_date."</b>.</p>";
                    $bbody .= "<p>'No Parking Signs' will be posted <b>TODAY</b> and will cover <b>BOTH SIDES</b> of the Street, though signs may only be posted on one side.</p>";
                    $bbody .= "<p>Vehicles parked in <b>NO PARKING ZONE</b> will be subject to <b>TICKETING</b> and/or <b>TOWING</b>.";
                    $bbody .= "Sweeping will be conducted on an East/West or North/South street only to allow residents to find alternate parking.</p>";
                    $bbody .= "<p>Thank you for helping us, keep our city clean!</p>";
                    $bbody .= "<p>Metro Public Works Team</p>";
                    $bsms   = "Make way for street sweeping. Vehicles parked on your street between 7 a.m. and 5 p.m. on ".$street_sweeping_date." may be ticketed or towed.";
                    break;
            }
        }
        elseif( $when == 'cancel' )
        {
            $bbody  = "<p><img src='https://louisvilleky.gov/sites/default/files/serviceimages/streetsweeper_original.jpg'></p><p>Street sweeping previously scheduled for ".$street_sweeping_date." has been postponed. We will alert you when a new date is set. In the meantime, you may park as you normally would.</p>";
            $bbody .= "<p>Metro Public Works Team</p>";
            $bsms   = "Street sweeping previously scheduled for ".$street_sweeping_date." your street has been postponed. We will alert you when a new date is set.";
        }
        else
        {
            return false;
        }



        try {
            // Second step: upload a file to the available server
            $uploader =
                new \HTTP_Request2
                (
                    $govDeliveryURL . $govDeliveryAccountCode . '/bulletins/send_now.xml',
                    \HTTP_Request2::METHOD_POST,
                    array ('ssl_verify_peer'   => false,
                        'ssl_verify_host'   => false)
                );

            $uploader->setAuth($govDeliveryUsername, $govDeliveryPassword, \HTTP_Request2::AUTH_BASIC);

            $request_body_fmt  = '<?xml version="1.0" encoding="UTF-8"?>
          <bulletin>
              <body><![CDATA[%s]]></body>
              <sms_body nil="true">%s</sms_body>
              <subject>%s</subject>
              <topics type="array">
                  <topic>
                      <code>%s</code>
                  </topic>
              </topics>
              <open_tracking type="boolean">false</open_tracking>
              <click_tracking type="boolean">false</click_tracking>
              <categories type="array" />
          </bulletin>
          ';

            $request_body = sprintf($request_body_fmt, $bbody, $bsms, $bsubject, $topic);
            $uploader->setHeader('Content-type: text/xml; charset=utf-8')->setBody($request_body);
            $send = $uploader->send();

            return $send;

        } catch (Exception $e) {
            print_r($e->getMessage());
        }

    }

}
