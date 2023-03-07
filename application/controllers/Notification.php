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
        // $this->db->where_in('id', array(205));
        $this->db->where_not_in('device_token','');
        $this->db->where_not_in('time_zone','');
        $users_query = $this->db->get('users');

        // Define a mapping of time zone formats to their corresponding identifiers
        $time_zone_map = array(
            "European Central Time (GMT+1:00)" => "Europe/Amsterdam",
            "Eastern European Time (GMT+2:00)" => "Europe/Athens",
            "Egypt Standard Time (GMT+2:00)" => "Africa/Cairo",
            "Eastern African Time (GMT+3:00)" => "Africa/Nairobi",
            "Middle East Time (GMT+3:30)" => "Asia/Tehran",
            "Near East Time (GMT+4:00)" => "Asia/Dubai",
            "Pakistan Lahore Time (GMT+5:00)" => "Asia/Karachi",
            "India Standard Time (GMT+5:30)" => "Asia/Kolkata",
            "Bangladesh Standard Time (GMT+6:00)" => "Asia/Dhaka",
            "Vietnam Standard Time (GMT+7:00)" => "Asia/Bangkok",
            "China Taiwan Time (GMT+8:00)" => "Asia/Taipei",
            "Japan Standard Time (GMT+9:00)" => "Asia/Tokyo",
            "Australia Central Time (GMT+9:30)" => "Australia/Darwin",
            "Australia Eastern Time (GMT+10:00)" => "Australia/Sydney",
            "Solomon Standard Time (GMT+11:00)" => "Pacific/Guadalcanal",
            "New Zealand Standard Time (GMT+12:00)" => "Pacific/Auckland",
            "Midway Islands Time (GMT-11:00)" => "Pacific/Midway",
            "Hawaii Standard Time (GMT-10:00)" => "Pacific/Honolulu",
            "Alaska Standard Time (GMT-9:00)" => "America/Anchorage",
            "Yukon Standard Time (GMT-8:00)" => "America/Whitehorse",
            "Alaska-Hawaii Standard Time (GMT-9:00)" => "America/Adak",
            "Pacific Standard Time (GMT-8:00)" => "America/Los_Angeles",
            "Phoenix Standard Time (GMT-7:00)" => "America/Phoenix",
            "Central Standard Time (GMT-6:00)" => "America/Chicago",
            "Mountain Standard Time (GMT-7:00)" => "America/Denver",
            "Eastern Standard Time (GMT-5:00)" => "America/New_York",
            "Indiana Eastern Standard Time (GMT-5:00)" => "America/Indiana/Indianapolis",
            "Puerto Rico and US Virgin Islands Time (GMT-4:00)" => "America/Puerto_Rico",
            "Canada Newfoundland Time (GMT-3:30)" => "America/St_Johns",
            "Argentina Standard Time (GMT-3:00)" => "America/Argentina/Buenos_Aires",
            "Brazil Eastern Time (GMT-3:00)" => "America/Sao_Paulo",
            "Central African Time (GMT-1:00)" => "Africa/Luanda"
        );


        foreach ($users_query->result() as $user) {
            // Validate the time zone string
            if (!isset($time_zone_map[$user->time_zone])) {
                continue;
            }
            $timezone_name = $time_zone_map[$user->time_zone];
            // Convert the user's time zone to a DateTimeZone object
            $timezone = new DateTimeZone($timezone_name);
            
            // // Convert the current time to the user's time zone
            $current_time->setTimezone($timezone);

            // // Check if it is currently 12:00 PM in the user's time zone
            if ($current_time->format('H:i') === '12:00') {

                $notification_data = array(
                    'title' => 'Hello, ' . $user->name,
                    'message' => 'What do you need to process today?',
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
