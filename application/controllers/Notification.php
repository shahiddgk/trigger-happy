<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends CI_Controller {
    public function __construct()
	{
		parent::__construct();
        date_default_timezone_set('America/New_York');
        // date_default_timezone_set('Asia/Karachi');
	}

    public function timezone_list(){
            
        return  $time_zone_map = array(
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
    }

    public function allowed_users(){
        // Select users with a non-empty device token
        $this->db->select('id, name, device_token, time_zone');
        $this->db->where('type', 'user');
        // Kaleem & Abid
        // $this->db->where_in('id', array(166, 205, 182));
        $this->db->where_not_in('device_token','');
        $this->db->where_not_in('time_zone','');
        return  $this->db->get('users');
    }

	public function send_notifications() {
        // Get the current time in UTC
        $current_time = new DateTime('now', new DateTimeZone('UTC'));
    
        $allowed_users =  $this->allowed_users();

        // Define a mapping of time zone formats to their corresponding identifiers
        $time_zone_map = $this->timezone_list();

        foreach ($allowed_users->result() as $user) {
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
                    'type' => 'welcome_notification',
                    'message' => 'What do you need to process today?',
                    'entity_id' => '',
                    'device_token' => $user->device_token
                );
                
                $this->push_notification($notification_data);
            }
        }
        exit;
    }
    
    public function reminder_notification()
    {
        $activeReminders = $this->db
            ->select('reminders.*, users.name, users.device_token, users.time_zone')
            ->from('reminders')
            ->join('users', 'users.id = reminders.user_id')
            ->where('reminders.status', 'active')
            // ->where_in('users.id', array(221))
            ->where('DATE(reminders.date_time) <=', date('Y-m-d'))
            ->where_not_in('users.device_token', '')
            ->where_not_in('users.time_zone', '')
            ->get()
            ->result_array();
    
        // echo "Active Reminders:<pre> "; print_r($activeReminders); exit;
        $timeZoneMap = $this->timezone_list();
    
        if (!empty($activeReminders)) {
            foreach ($activeReminders as $reminder) {
                $userTimeZone = $reminder['time_zone'];

                if (!isset($timeZoneMap[$userTimeZone])) {
                    continue;
                }
    
                $validTimeZone = $timeZoneMap[$userTimeZone];
                $currentTime = new DateTimeImmutable('now', new DateTimeZone('UTC'));
                $timeZone = new DateTimeZone($validTimeZone);
                $currentTime = $currentTime->setTimezone($timeZone);
    
                if ($reminder['reminder_type'] === 'repeat') {
                    $daysArray = json_decode($reminder['day_list'], true);
                    $daysList = array_map('ucfirst', $daysArray);
                    $currentDay = $currentTime->format('D');
    
                    if (!in_array($currentDay, $daysList)) {
                        continue;
                    }

                    if(!empty($reminder['end_date'])){
                        if($currentTime->format('Y-m-d') > $reminder['end_date']){
                            continue;
                        }
                    }
                }
    
                $reminderTime = new DateTimeImmutable($reminder['date_time'], $timeZone);
                
                if ($currentTime->format('H:i') === $reminderTime->format('H:i')) {
                    $notificationData = $this->prepareNotificationData($reminder['name'], $reminder['text'], $reminder['id'], $reminder['device_token'], $reminderTime->format('Y-m-d H:i:s'));
                    $this->push_notification($notificationData);
                }

                if ($reminder['snooze'] === 'yes' && $currentTime->format('Y-m-d H:i') > $reminderTime->format('Y-m-d H:i')) {
                    $notificationData = $this->prepareNotificationData($reminder['name'], $reminder['text'], $reminder['id'], $reminder['device_token'], $reminderTime->format('Y-m-d H:i:s'));
                    $this->push_notification($notificationData);
                    $this->common_model->update_array(['id' => $reminder['id']], 'reminders', ['snooze' => 'no']);
                }
            }
        }
    }
    
    private function prepareNotificationData($name, $text, $id, $deviceToken, $date_time)
    {
        return [
            'title' => 'Hi ' . $name. ' Did you....',
            'type' => 'reminder',
            'message' => $text,
            'date_time' => $date_time,
            'entity_id' => $id,
            'device_token' => $deviceToken
        ];
    }
    
    
    public function push_notification($data){
    //   echo "<pre>"; print_r($data);
    //   return;

        $fields = [
            'to' => $data['device_token'],
            'data' => [
                'type' => $data['type'],
                'click_action' => 'FLUTTER_POPUP_ACTION',
                'date_time' => @$data['date_time'],
                'entity_id' => @$data['entity_id'],
            ],
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

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return 'cURL request failed: ' . $error;
        }
        curl_close($ch);
        $response = json_decode($result, true);
    
        // echo "<pre>"; print_r($response); exit();
        return $response;
    }

    public function growth_tree() {
        $yesterday = date('Y-m-d', strtotime("-1 days"));
    
        $pire_array = $this->common_model->select_where_groupby('user_id, type, response_id, DATE(created_at) as response_date, level, seed', 'answers', array('type' => 'pire', 'DATE(created_at) <=' => $yesterday), 'user_id')->result_array();
        
        $naq_array = $this->common_model->select_where_groupby('user_id, type, response_id, DATE(created_at) as response_date, level, seed', 'answers', array('type' => 'naq', 'DATE(created_at) <=' => $yesterday), 'user_id')->result_array();
    
        $data_to_insert = array();
    
        foreach (array_merge($pire_array, $naq_array) as $response) {
            $count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $response['user_id'], 'type' => $response['type'], 'response_date' => $response['response_date']));
            $dead_user = $this->common_model->select_where("*", "users", array('id' => $response['user_id']))->num_rows();
           
            if($dead_user == 0){
                continue;
            }
            if ($count == 0) {
                $data_to_insert[] = array(
                    'type' => $response['type'],
                    'user_id' => $response['user_id'],
                    'level' => $response['level'],
                    'seed' => $response['seed'],
                    'response_id' => $response['response_id'],
                    'response_date' => $response['response_date']
                );
            }
        }
    
        if (!empty($data_to_insert)) {
            $this->db->insert_batch('scores', $data_to_insert);
        }

        // Score update is level_history Table 
        $users_list = $this->common_model->select_groupby('user_id , MIN(response_date) as start_date', 'scores', 'user_id')->result_array();

        // echo "<pre>"; print_r($users_list); exit;
        foreach($users_list as $single) {

            $user_id = $single['user_id']; 
            $start_date = $single['start_date']; 

            $count =  $this->common_model->select_where_groupby('*', 'scores', array('user_id' => $user_id) , 'response_date')->num_rows();

            $garden_level = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
           
            if(empty($garden_level)){
                continue;
            }
            $current_level =  $garden_level['level'];
            $current_tree =  $garden_level['seed'];

            $user_exist =  $this->common_model->select_where('*', 'level_history', array('user_id' => $user_id , 'level' => $current_level ))->num_rows();
            $seeds_count =  $this->common_model->select_single_field('count', 'garden_seeds', array('level' => $current_level, 'id' => $current_tree ));
            // $seeds_count = $seeds_count - 1;

            if($user_exist == 1){
                  
                if ($count == $seeds_count) {
                    $this->common_model->update_array(['user_id' => $user_id, 'level' => $current_level], 'level_history', ['status' => 'complete', 'score' => $count , 'updated_at' => date('Y-m-d H:i:s')]);
                }else{
                    $this->common_model->update_array(['user_id' => $user_id, 'level' => $current_level], 'level_history', ['status' => 'active', 'score' => $count , 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }else{
                $insert['user_id'] = $user_id;
                $insert['level'] = $current_level;
                $insert['seed'] = $current_tree;
                $insert['score'] = $count;
                $insert['status'] = $count == $seeds_count ? 'complete' : 'active';
                $insert['start_date'] = $start_date;
                $insert['end_date'] = $count == $seeds_count ? date('Y-m-d') : '';
                $this->common_model->insert_array('level_history', $insert);
            }
        }    
    }    
}
