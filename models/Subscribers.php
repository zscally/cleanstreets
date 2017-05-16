<?php

namespace models;
use lib\Core;
use lib\Config;
use lib\DB;



class Subscribers
{

    protected $db;

    function __construct()
    {
        $this->db = \lib\DB::getInstance();
    }

    public function getMapData()
    {
        $select = $this->db->select('AlertNotification', 'NotificationTypeID != :notificationtype', array(':notificationtype' => 2), 'PickupAreaID, AlertAddress, council_district, NotificationValue, DateAdded, latitude, longitude');
        if( $select )
        {
            return $select;
        }
        return false;
    }

    public function updateSubscriber($id, $data)
    {
        $update = $this->db->update('AlertNotification', $data, 'AlertID = :id', array(':id' => $id));
        if( $update )
        {
            return true;
        }
        return false;
    }

    public function createGovDeliverySubscriber($data)
    {
        $create = $this->db->insert('gov_delivery_subscribers', $data);
        if( $create )
        {
            return true;
        }
        return false;
    }


    public function dropgovtemp()
    {
        $action = $this->db->run('DROP TABLE IF EXISTS `gov_delivery_subscribers`');
        if( $action )
        {
            return true;
        }
        return false;
    }

    public function creategovtemp()
    {
        $create_statment = "
          CREATE TABLE `gov_delivery_subscribers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `area_route` varchar(255) NOT NULL,
            `contact` varchar(255) NOT NULL,
            `origin` varchar(255) NOT NULL,
            `created` varchar(255) NOT NULL,
            `found` int(11) DEFAULT '0',
            PRIMARY KEY (`id`)
          )
        ";
        $action = $this->db->run($create_statment);
        if( $action )
        {
            return true;
        }
        return false;
    }

    public function getSubscribersByDate($date_spec = null)
    {
        $now = ($date_spec ? $date_spec : datetimeformat());
        $date = date('Y-m-d h:i:s', strtotime('-31 day', strtotime($now)));
        $data = $this->db->run("SELECT date(DateAdded) as date, count(DISTINCT NotificationValue) as total_subscribers FROM AlertNotification WHERE date(DateAdded) BETWEEN :than AND :now GROUP BY date(DateAdded) ORDER BY date", array(':now'=>$now, ':than'=>$date));
        if($data )
        {
            return $data;
        }
        return false;
    }

    public function getSubscribersByAreaRoute()
    {
        $data = $this->db->run("SELECT count(DISTINCT AlertAddress) as total_subscribers, SUBSTRING(PickupAreaID, 1, 2) as area, SUBSTRING(PickupAreaID, 3, 2) as route FROM `AlertNotification` WHERE NotificationTypeID !=2 GROUP BY `PickupAreaID`");
        if( $data )
        {
            $rdata = [];
            foreach( $data as $d )
            {
                $rdata['area' . $d['area']]['route' . $d['route']] = $d['total_subscribers'];
            }
            return $rdata;
        }
        return false;
    }

    public function getDistinctSubAddresses()
    {
        $data = $this->db->run("SELECT * FROM AlertNotification GROUP BY AlertAddress, NotificationValue");
        if( $data )
        {
            return $data;
        }
        return false;
    }

    public function getSubscribers($where = false)
    {
        $data = $this->db->select('AlertNotification', $where);
        if( count($data) > 0 ){
            return $data;
        }
        return false;
    }

    public function getSubscriberComments($sub_id)
    {
        $data = $this->db->select('comments', 'subscriber_id = :subscriber_id', array(':subscriber_id' => $sub_id));
        if( $data )
        {
            return $data;
        }
        return false;
    }

    public function getSubscribersLast24Hour(){
        $now = datetimeformat();
        $date = date('Y-m-d h:i:s', strtotime('-1 day', strtotime($now)));
        $data = $this->db->select('AlertNotification', 'DateAdded between :than and :now', array(':now'=>$now, ':than'=>$date));
        if( count($data) > 0 )
        {
            return $data;
        }
        return false;
    }


    public function getSubscriber($account_value)
    {
        $data = $this->db->select('AlertNotification', 'NotificationValue = :NotificationValue', array(':NotificationValue' => $account_value));
        if( count($data) > 0 )
        {
            return $data[0];
        }
        return false;
    }


    public function getSubscriberById($subscriber_id)
    {
        $data = $this->db->select('AlertNotification', 'AlertID = :subscriber_id', array(':subscriber_id' => $subscriber_id));
        if( count($data) > 0 )
        {
            $comments = $this->db->run("SELECT *, c.id as comment_id FROM comments c LEFT JOIN users u ON c.created_by = u.id WHERE c.subscriber_id = '".filter_var($subscriber_id, FILTER_SANITIZE_NUMBER_INT)."' AND c.is_active = 1 ORDER BY c.date_created DESC");
            $data[0]['comments'] = $comments;
            return $data[0];
        }
        return false;
    }

    public function getSubscriberFromGovDeliveryTable($notification_value, $area_route)
    {
        $data = $this->db->select('gov_delivery_subscribers', 'contact = :NotificationValue AND area_route = :PickupAreaID', array(':NotificationValue' => $notification_value, ':PickupAreaID' => $area_route));
        if( $data )
        {
            return $data;
        }
        return false;
    }

    public function updateGovDeliveryTable($id, $data)
    {
        $update = $this->db->update('gov_delivery_subscribers', $data, 'id = :id', array(':id' => $id));
        if( $update )
        {
            return true;
        }
        return false;
    }

    public function getChangedAddresses()
    {
        $logic = '';
        $subscribers = $this->getSubscribers();
        $found_changes = array();
        foreach( $subscribers as $subscriber )
        {
          $logic_info = $this->getLojicInfo($subscriber['address']);
          if( $subscriber['area_route'] != $logic['area_route'] )
          {
            $found_changes[] = $subscriber;
          }
          else
          {
              return false;
          }
        }
        return $found_changes;
    }


    public function getLojicInfo($address)
    {
        $lojic_url = '';
        $info = file_get_contents($lojic_url . $address);
        if( $info )
        {
          return $info;
        }
        return false;
    }

    public function createSubscriberComment($data)
    {
        $insert = $this->db->insert('comments', $data);
        if( $insert )
        {
            return true;
        }
        return false;
    }

    public function deleteComment($comment_id)
    {
        $delete = $this->db->delete('comments', 'id=:comment_id', array(':comment_id' => $comment_id));
        if( $delete ) {
            return true;
        }
        return false;

    }

    public function getCannedComments()
    {
        $data = $this->db->select('canned_comments', 'is_active=1');
        if( $data )
        {
            return $data;
        }
        return false;
    }

    public function createStageSubscriber($data)
    {
        $insert = $this->db->insert('AlertNotification_stage', $data);
        if($insert)
        {
            return $insert;
        }
        return false;
    }

    public function buildAlertNotificationStagingTable()
    {
        $action = $this->db->run('DROP TABLE IF EXISTS `AlertNotification_stage`');
        $data = $this->db->run("
            CREATE TABLE `AlertNotification_stage` (
              `AlertID` int(11) NOT NULL AUTO_INCREMENT,
              `AlertTypeID` int(11) NOT NULL,
              `PickupAreaID` varchar(255) NOT NULL,
              `NotificationTypeID` int(11) NOT NULL,
              `AlertX` varchar(45) NOT NULL,
              `AlertY` varchar(45) NOT NULL,
              `AlertAddressID` int(11) NOT NULL,
              `AlertAddress` varchar(255) NOT NULL,
              `license_id` varchar(255) DEFAULT NULL,
              `council_district` varchar(255) DEFAULT NULL,
              `NotificationValue` varchar(255) NOT NULL,
              `first_name` varchar(255) NOT NULL,
              `last_name` varchar(255) NOT NULL,
              `NumberMissedNotifications` int(11) NOT NULL,
              `DateAdded` datetime NOT NULL,
              `DateUpdated` datetime NOT NULL,
              `AlertDisableReason` varchar(255) NOT NULL,
              `latitude` varchar(255) NOT NULL,
              `longitude` varchar(255) DEFAULT NULL,
              `lojic_arearoute` varchar(255) DEFAULT '0',
              PRIMARY KEY (`AlertID`)
            )
        ");

        if( $data )
        {
            return true;
        }
        return false;
    }

    public function subscribersSignups($date_from, $date_to){

    }
}
