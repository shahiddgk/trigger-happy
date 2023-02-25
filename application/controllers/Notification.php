<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {
    public function __construct()
	{
		parent::__construct();
        date_default_timezone_set('America/New_York');
	}

	public function send_notifications() {

        // Get the current time in UTC
        $current_time = new DateTime('now', new DateTimeZone('UTC'));
    
        // Select users with a non-empty device token
        $this->db->select('id, name, device_token, time_zone');
        $this->db->where('type', 'user');
        // $this->db->where_in('id', array(182, 166));
        $this->db->where_not_in('device_token','');
        $this->db->where_not_in('time_zone','');
        $users_query = $this->db->get('users');

        foreach ($users_query->result() as $user) {
            // Validate the time zone string
            if (!in_array($user->time_zone, DateTimeZone::listIdentifiers())) {
                continue;
            }
            
            // Convert the user's time zone to a DateTimeZone object
            $timezone = new DateTimeZone($user->time_zone);
            
            // // Convert the current time to the user's time zone
            $current_time->setTimezone($timezone);
            // echo "<pre>" ; print_r($timezone);

            // // Check if it is currently 12:00 PM in the user's time zone
            if ($current_time->format('H:i') === '12:00') {
                $notification_data = array(
                    'title' => 'Hello, ' . $user->name,
                    'message' => 'This is a sample push notification',
                    'time' => $current_time->format('Y-m-d H:i:s'),
                    'user_id' => $user->id,
                    'device_token' => $user->device_token
                );
                
                $this->push_notification($notification_data);
            }
        }
        exit;
    }

 
    
    public function push_notification($data){
        $fields = [
            'to' => $data['device_token'],
            'notification' => [
                'title' => $data['title'],
                'body' => $data['message'],
                'sound' => 'default'
            ]
        ];
        
        $headers = [
            'Authorization: key=AAAALvzpZaA:APA91bF7hDg0qtpfCAxn8W4tERonX1Kkw9osdcuDkB6eYgp43rUY-2QhTzvgbsUb8ghnrxBNEjvWd0aTxe0KhRA6CGk6Yk7PA1rduUcdByQlMIQl6S8-9B96mcj3ty9YQsPA31wU4jPU',
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
        // $response = json_decode($result, true);
        //  echo "<pre>"; print_r($response); exit();
        
    }
}
