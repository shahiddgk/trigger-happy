<?php
require APPPATH . 'libraries/TokenHandler.php';
require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

  	protected $token;
	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('Access-Control-Allow-Origin: *');
        $this->output->set_header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
        $this->output->set_header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
		// Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
            exit();
        }
		// creating object of TokenHandler class at first
		$this->tokenHandler = new TokenHandler();
		header('Content-Type: application/json');
		
		$this->load->library('email');
		$this->config->load('email', TRUE);
		$this->smtp_user =  $this->config->item('smtp_user', 'email');

	}

	public function signup_post(){

		$name	=	$_POST['name'];
		$email	=	$_POST['email'];
		$password	=	$_POST['password'];
		$result = $this->common_model->select_where("*", "users", array('email'=>$email , 'type'=>'user'))->result_array();
		if($result){
			$response = [
				'status' => 400,
				'message' => 'Already signed up'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
		else{
				$data['name'] = $name;
				$data['email'] = $email;
				
				if(isset($_POST['time_zone'])){
					$data['time_zone'] = $_POST['time_zone'];
				}
				if(isset($_POST['device_token'])){
					$data['device_token'] = $_POST['device_token'];
				}
					
				$data['password'] = sha1($password);
				$data['type'] = 'user';
				$data['status'] = 'active';
				$result = $this->common_model->insert_array('users', $data);
			if($result){
				
				$response = [
					'status' => 200,
					'message' => 'success',
					'user_signup' => 'TRUE'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
			else{
				$response = [
					'status' => 400,
					'message' => 'failed to sign up',
					'user_signup' => 'FALSE'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}
	}

	public function update_profile_post(){
		$user_id	=	$_POST['user_id'];
		$result = $this->common_model->select_where("*", "users", array('id'=>$user_id , 'type'=>'user'))->result_array();
		if($result){
			if(!empty($_POST['name'])){
				$update['name'] = $_POST['name'];
				$this->common_model->update_array(array('id'=> $user_id), 'users', $update);
			}if(!empty($_POST['email'])){
				$update['email'] = $_POST['email'];
				$this->common_model->update_array(array('id'=> $user_id), 'users', $update);
			}if(!empty($_POST['time_zone'])){
				$update['time_zone'] = $_POST['time_zone'];
				$this->common_model->update_array(array('id'=> $user_id), 'users', $update);
			}if(!empty($_POST['device_token'])){
				$update['device_token'] = $_POST['device_token'];
				$this->common_model->update_array(array('id'=> $user_id), 'users', $update);
			}
			// if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'profile updated successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			// }
		}
		else{
				$response = [
					'status' => 200,
					'message' => 'user not found'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

   	public function login_post(){

		$email	=	$_POST['email'];
		$password	=	$_POST['password'];
			
		$data['login'] = $this->common_model->select_where("*","users", array('email'=>$email,'password'=>sha1($password), 'type'=>'user'));
		
		if($data['login']->num_rows()>0){
			$row = $data['login']->row_array();
			$valid_token  =  $row['device_token'];

			if($row['status']=='inactive'){
				$response['error'] = 'inactive user';
				$this->set_response($response, REST_Controller::HTTP_OK);
			} 
			if(!empty($_POST['device_token'])){
				$device_token	=	$_POST['device_token'];
				$this->common_model->update_array(array('id'=> $row['id']), 'users', array('device_token'=>$device_token));

				if($this->db->affected_rows()> 0){
					$valid_token  =  $device_token;
				}
			}else{
				$device_token	=	'HHHKHKHKLHIOY88657656545454343543';
				$this->common_model->update_array(array('id'=> $row['id']), 'users', array('device_token'=>$device_token));
				$valid_token  =  $device_token;
			}

			$user_data = array(
				'user_logged_in'  =>  TRUE,
				'usertype' => $row['type'],
				'username' => $row['name'],
				'useremail' => $row['email'],
				'allowemail' => $row['mail_resp'],
				'timezone' => $row['time_zone'],
				'devicetoken' => $valid_token,
				'userid' => $row['id']
			);

			$response = [
				'status' => 200,
				'message' => 'success',
				'user_session' => $user_data
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'failed to login',
				'user_login' => 'FALSE'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function questions_get(){
		$questions = $this->common_model->select_all_order_by('*', 'questions','id','ASC')->result_array();
		if($questions){

			foreach ($questions as $key=>$question) {
				if(!empty($question['options'])){
					$options = explode(",", json_decode($question['options']));
					$questions[$key]['options'] = $options;
				}
			}
			$response = [
				'status' => 200,
				'message' => 'success',
				'questions' => $questions
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
			
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no data available'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function social_login_post(){

		$name	    =	$_POST['name'];
		$email	    =	$_POST['email'];
		$auth_id	=	$_POST['auth_id'];
		$time_zone	=	$_POST['time_zone'];
		$device_token	=	$_POST['device_token'];

		if(!empty($auth_id)){
			$result = $this->common_model->select_where("*", "users", array('email'=>$email, 'type'=>'user'));
			if($result->num_rows()>0){
				$valid_user = $result->row_array();
				
				if($valid_user['social_auth_id'] == $auth_id){
					$user_data = array(
						'user_logged_in'  =>  TRUE,
						'usertype' => $valid_user['type'],
						'username' => $valid_user['name'],
						'useremail' => $valid_user['email'],
						'allowemail' => $valid_user['mail_resp'],
						'timezone' => $valid_user['time_zone'],
						'devicetoken' => $valid_user['device_token'],
						'authID' => $valid_user['social_auth_id'],
						'userid' => $valid_user['id']
					);
					$response = [
						'status' => 200,
						'message' => 'User already exists',
						'user_login' => 'TRUE',
						'user_session' => $user_data
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
				elseif(empty($valid_user['social_auth_id'])){
					$data['social_auth_id'] = $auth_id;
					$this->common_model->update_array(array('id'=> $valid_user['id']), 'users', $data);
					$data = array(
						'user_logged_in'  =>  TRUE,
						'usertype' => $valid_user['type'],
						'username' => $valid_user['name'],
						'useremail' => $valid_user['email'],
						'allowemail' => $valid_user['mail_resp'],
						'timezone' => $valid_user['time_zone'],
						'devicetoken' => $valid_user['device_token'],
						'authID' => $auth_id,
						'userid' => $valid_user['id']
					);
					$response = [
						'status' => 200,
						'message' => 'New social user created',
						'user_login' => 'TRUE',
						'user_session' => $data
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
				else{
					$response = [
						'status' => 400,
						'message' => 'Invalid Auth id'
					];
					$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				}
			}
			else{
				$data['name'] = $name;
				$data['email'] = $email;
				$data['social_auth_id'] = $auth_id;
				$data['type'] = 'user';
				$data['status'] = 'active';
				$insert = $this->common_model->insert_array('users', $data);
				if($insert){
					$result = $this->common_model->select_where("*", "users", array('social_auth_id'=>$auth_id));
					if($result->num_rows()>0){
						$row = $result->row();
						$user_data = array(
							'user_logged_in'  =>  TRUE,
							'usertype' => $row->type,
							'username' => $row->name,
							'useremail' => $row->email,
							'authID' => $row->social_auth_id,
							'userid' => $row->id
						);
						$response = [
							'status' => 200,
							'message' => 'New social user created',
							'user_login' => 'TRUE',
							'user_session' => $user_data
						];
						$this->set_response($response, REST_Controller::HTTP_OK);
					}
				}
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'invalid user'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}


	public function single_answer_post(){
		$question_id = $_POST['question_id'];
		$user_id = $_POST['user_id'];
		$created_at = $_POST['created_at'];
		$result = $this->common_model->select_where("*", "answers", array('user_id'=>$user_id,'question_id'=>$question_id,'created_at'=>$created_at))->row_array();
		if($result){
			$response = [
				'status' => 200,
				'message' => 'success',
				'single_answer' => $result
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'user not found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function user_response_post(){
		$data['question_id'] = $_POST['question_id'];
		$data['user_id'] = $_POST['user_id'];
		$data['options'] =  $_POST['options'];
		$data['text'] = $_POST['text'];
		$insert = $this->common_model->insert_array('answers', $data);
		if($insert){
			$response = [
				'status' => 200,
				'message' => 'data inserted successfully'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'Error inserting'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function response_submit_mail_post(){

		$name = $_POST['name'];
		$email = $_POST['email'];
		$user_id = $_POST['user_id'];
		$answers = json_decode($_POST['answers'], true);
	
		if($answers){
			$response_id =  random_string('numeric',8);     
			foreach ($answers as $key =>  $answer)
			{
				if($answer['type'] == 'radio_btn'){
					$optins = implode(",",$answer['answer']);
					$data['question_id'] = $key;
					$data['options'] =  $optins;
					$data['text'] = '';
					$data['user_id'] = $user_id;
					$data['response_id'] = $response_id;
				}
				else if($answer['type'] == 'check_box'){
					$checks = implode(",",$answer['answer']);
					$data['question_id'] = $key;
					$data['options'] = $checks ;
					$data['text'] = '';
					$data['user_id'] = $user_id;
					$data['response_id'] = $response_id;
				}
				else if($answer['type'] == 'open_text'){
					$data['question_id'] = $key;
					$data['options'] = '';
					$data['text'] = trim(json_encode($answer['answer']), '[""]');
					$data['user_id'] = $user_id;
					$data['response_id'] = $response_id;
				}

				$insert = $this->common_model->insert_array('answers', $data);
			}

			$status = $this->common_model->select_single_field('mail_resp', 'users', array('id'=>$user_id));
			
			if($insert && $status == 'yes'){
				$response = $this->common_model->select_two_tab_join_where("a.* , q.title",'answers a', 'questions q', 'a.question_id=q.id', array('a.response_id'=>$response_id)); 
			
				if($response->num_rows()>0) {

					$data['answers'] = $response->result_array();
					$subject = 'Response Submit Confirmation';
					$message = "Dear <b>" .$name. " </b> <br>";
					$message .= "Your answers for Burgeon have been submitted successfully. <br> <hr>";
					$message .= '<table>';
					foreach ( $data['answers'] as $key => $value ){
						$no = $key+1 ;
						$message .= "<tr> <td> <b>Question ".$no." : </b> " . strip_tags($value['title']). " </td> </tr>";
						$message .= "<tr> <td> <b>Answer: </b> ". $text = $value['options'] ? strip_tags($value['options']) : strip_tags($value['text']). "</td> </tr>";
						$message .= "<tr><td><hr></td></tr>";
					}
					$message .= '</table>';
						$this->email->set_newline("\r\n");
						$this->email->set_mailtype('html');
						$this->email->from($this->smtp_user, 'Burgeon');
						$this->email->to($email);
						$this->email->subject($subject);
						$this->email->message($message);
					if($this->email->send())
					{
						$response = [
							'status' => 200,
							'message' => 'mail sent successfully'
						];
						$this->set_response($response, REST_Controller::HTTP_OK);
					}
					else
					{
						$error = $this->email->print_debugger();
						$response = [
							'status' => 500,
							'message' => $error
						];
						$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					}
				}
				else{
					$response = [
						'status' => 200,
						'message' => 'data inserted'
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
			}
			else{
				$response = [
					'status' => 200,
					'message' => 'data inserted, mail not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'Invalid json format'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function forgot_password_post()
	{
		$email = $_POST['email'];
		$response = $this->common_model->select_where("id", "users", array('email'=>$email , 'type'=>'user')); 
	
		if($response->num_rows()>0) {
			$row = $response->row();
			$new_pass =  random_string('alpha',8);
			$data['password'] = sha1($new_pass);
			$this->common_model->update_array(array('id'=>$row->id), 'users', $data);
		
			
			$this->email->set_newline("\r\n");
			$this->email->set_mailtype('html');
			$this->email->from($this->smtp_user, 'Burgeon');
			$this->email->to($email);
			$this->email->subject('Password Reset');
			$this->email->message('Here is your new password: <b>'. $new_pass . '</b>');

			if($this->email->send())
			{
				$response = [
					'status' => 200,
					'message' => 'please check your email'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
			else
			{
				$response = [
					'status' => 500,
					'message' => 'password reset failed'
				];
				$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'invalid user'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
    
	}

	public function change_password_post(){
		$user_id = $_POST['user_id'];
		$old_password = $_POST['old_password'];
		$auth_id = $_POST['auth_id'];
		$update['password'] = sha1($_POST['new_password']);
		if($auth_id){
			$result = $this->common_model->update_array(array('id'=> $user_id,'social_auth_id'=>$auth_id), 'users', $update);
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'Password changed'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'No change in password'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		elseif($old_password){
			$result = $this->common_model->update_array(array('id'=> $user_id,'password'=>sha1($old_password)), 'users', $update);
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'Password changed'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'No change in password'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'Invalid parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function delete_user_get()
	{
		$user_id = $_GET['user_id'];
		if(!empty($user_id)){
			$this->db->delete('users', array('id'=>$user_id));
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'user deleted successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'user not found'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
    
	}

	public function logout_post()
	{
		$user_id = $_POST['user_id'];
		if(!empty($user_id)){
			$this->common_model->update_array(array('id'=> $user_id), 'users', array('device_token'=>''));
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'logged out successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'user not found'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
    
	}

	public function growth_tree_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			$count = $this->common_model->select_where("*","scores", array('user_id'=>$user_id, 'type'=>'pire'))->num_rows();
			if($count >= 0){
				if($count>36){
					$img = 37;
				}else{
					$img = $count+1;
				}
			}

			$response = [
				'status' => 200,
				'type' => 'pire',
				'response_count'=> $img,
				'mobile_image_url'=> base_url('uploads/mobile_tree/').$img.'.png',
				'ipad_image_url'=> base_url('uploads/ipad_tree/').$img.'.png',
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function response_history_post(){
		
		if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
			$user_id = $_POST['user_id'];
			$result_array = $this->common_model->select_where_ASC_DESC_Group_by("response_date
			 date", "scores", array('user_id'=>$user_id , 'type'=>'pire'), 'response_date', 'ASC', 'response_date' )->result_array();

			foreach($result_array as $key => $value){
				$count = $key+1;
				$result_array[$key]['score'] = $count;
			}
			if(isset($_POST['date']) && !empty($_POST['date'])){
				$date_to_find = $_POST['date'];

				foreach ($result_array as $item) {
					if ($item["date"] === $date_to_find) {
						$result_array =  $item;
						break;
					}
				}
			}
			$response = [
				'status' => 200,
				'response_history'=> $result_array,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function get_email_post(){
		$user_id = $_POST['user_id'];
		$update['mail_resp'] = $_POST['status'];

		if(!empty($user_id)){
			$this->common_model->update_array(array('id'=> $user_id), 'users', $update);
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'email status updated'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'message' => 'no status change'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function trellis_post(){
		$user_id = $_POST['user_id'];
		
		if(isset($_POST['name'])){
			$data['name'] = $_POST['name'];
		}
		if(isset($_POST['name_desc'])){
			$data['name_desc'] = $_POST['name_desc'];
		}
		if(isset($_POST['purpose'])){
			$data['purpose'] = $_POST['purpose'];
		}

		$row_count = $this->common_model->select_where("*", "trellis", array('user_id'=>$user_id))->num_rows();

		if($row_count == 1){
					
			$this->common_model->update_array(array('user_id'=> $user_id), 'trellis', $data);
			$response = [
				'status' => 200,
				'message' => 'data updated'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$data['user_id'] = $user_id;
			$this->common_model->insert_array('trellis', $data);
			$response = [
				'status' => 200,
				'message' => 'data inserted'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	
		
	}

	public function ladder_post(){
		$user_id = $_POST['user_id'];
		$type = $_POST['type'];
		
		$row_count = $this->common_model->select_where("*", "ladder", array('user_id'=>$user_id, 'type'=>$type))->num_rows();

		if($row_count < 2){
			$data['user_id'] = $user_id;
			$data['type'] = $_POST['type'];
			if(isset($_POST['option1']) && !empty($_POST['option1'])){
				$data['option1'] = $_POST['option1'];
			}
			if(isset($_POST['option2']) && !empty($_POST['option2'])){
				$data['option2'] = $_POST['option2'];
			}
			if(isset($_POST['date']) && !empty($_POST['date'])){
				$dateObj = DateTime::createFromFormat('m-d-y', $_POST['date']);
				$data['date'] = $dateObj->format('Y-m-d');
				$_POST['date'] = $data['date'];
			}
			if(isset($_POST['text']) && !empty($_POST['text'])){
				$data['text'] = $_POST['text'];
			}
			if(isset($_POST['description']) && !empty($_POST['description'])){
				$data['description'] = $_POST['description'];
			}
			
			$this->common_model->insert_array('ladder', $data);
			$last_insert_id = $this->db->insert_id(); 
			$_POST['id'] = $last_insert_id;
			$response = [
				'status' => 200,
				'message' => 'success',
				'post_data' => $_POST
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'more responses not allowed'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}


	public function tribe_post(){
		$user_id = $_POST['user_id'];
		$row_count = $this->common_model->select_where("*", "tribe", array('user_id'=>$user_id))->num_rows();

		if($row_count < 1){
			$data['user_id'] = $user_id;
			if(isset($_POST['mentor']) && !empty($_POST['mentor'])){
				$data['mentor'] = $_POST['mentor'];
			}
			if(isset($_POST['peer']) && !empty($_POST['peer'])){
				$data['peer'] = $_POST['peer'];
			}
			if(isset($_POST['mentee']) && !empty($_POST['mentee'])){
				$data['mentee'] = $_POST['mentee'];
			}
			
			$this->common_model->insert_array('tribe', $data);
			$last_insert_id = $this->db->insert_id(); 
			$_POST['id'] = $last_insert_id;
			$response = [
				'status' => 200,
				'message' => 'success',
				'post_data' => $_POST
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'more responses not allowed'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function principles_post(){
		$user_id = $_POST['user_id'];

		$type = $_POST['type'];

		$row_count = $this->common_model->select_where("*", "principles", array('user_id'=>$user_id, 'type'=>$type))->num_rows();
		
		if($row_count < 2){
			$data['user_id'] = $user_id;
			$data['type'] = $_POST['type'];
			if(isset($_POST['emp_truths'])){
				$data['emp_truths'] = $_POST['emp_truths'];
			}
			if(isset($_POST['powerless_believes'])){
				$data['powerless_believes'] = $_POST['powerless_believes'];
			}
			
			$this->common_model->insert_array('principles', $data);
			$last_insert_id = $this->db->insert_id(); 
			$_POST['id'] = $last_insert_id;
			$response = [
				'status' => 200,
				'message' => 'success',
				'post_data' => $_POST
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'more responses not allowed'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function identity_post(){
		$user_id = $_POST['user_id'];

		$type = $_POST['type'];

		$row_count = $this->common_model->select_where("*", "identity", array('user_id'=>$user_id, 'type'=>$type))->num_rows();
		
		if($row_count < 2){
			$data['user_id'] = $user_id;
			$data['type'] = $_POST['type'];
			if(isset($_POST['text']) && !empty($_POST['text'])){
				$data['text'] = $_POST['text'];
			}
			
			$this->common_model->insert_array('identity', $data);
			$last_insert_id = $this->db->insert_id(); 
			$_POST['id'] = $last_insert_id;
			$response = [
				'status' => 200,
				'message' => 'success',
				'post_data' => $_POST
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'more responses not allowed'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function all_trellis_read_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			$trellis['trellis'] = $this->common_model->select_where("*", "trellis", array('user_id'=>$user_id))->result_array();
			$trellis['tribe'] = $this->common_model->select_where("*", "tribe", array('user_id'=>$user_id))->result_array();
			$trellis['ladder'] = $this->common_model->select_where("*", "ladder", array('user_id'=>$user_id))->result_array();
			$trellis['identity'] = $this->common_model->select_where("*", "identity", array('user_id'=>$user_id))->result_array();
			$trellis['principles'] = $this->common_model->select_where("*", "principles", array('user_id'=>$user_id))->result_array();

			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $trellis
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function trellis_read_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			$table = $_POST['table'];
			if(isset($_POST['type']) && !empty($_POST['type'])){
				$type = $_POST['type'];
				$trellis = $this->common_model->select_where("*", "$table", array('user_id'=>$user_id, 'type'=>$type))->result_array();
			}
			else{
				$trellis = $this->common_model->select_where("*", "$table", array('user_id'=>$user_id))->result_array();
			}
			$response = [
				'status' => 200,
				'message' => 'success',
				'table_name' =>  $table,
				'data' => $trellis
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function trellis_delete_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			$type = $_POST['type'];
			$record_id = $_POST['record_id'];

			if($type == 'goal' || $type == 'achievements'){
				$table = 'ladder';
			}else if($type == 'needs' || $type == 'identity'){
				$table = 'identity';
			}else if($type == 'rhythms' || $type == 'principles'){
				$table = 'principles';
			}else if($type == 'tribe'){
				$table = 'tribe';
			}

			$this->db->delete("$table", array('id'=>$record_id, 'user_id'=>$user_id));
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'deleted successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'message' => 'data not deleted'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function session_entry_post(){

		if(isset($_POST['user_id']) && !empty($_POST['user_id'])){

			$data['user_id'] = $_POST['user_id'];

			if(isset($_POST['entry_title'])){
				$data['entry_title'] = $_POST['entry_title'];
			}
			if(isset($_POST['entry_decs'])){
				$data['entry_decs'] = $_POST['entry_decs'];
			}
			if(isset($_POST['entry_date'])){
				$data['entry_date'] = $_POST['entry_date'];
			}
			if(isset($_POST['entry_takeaway'])){
				$data['entry_takeaway'] = $_POST['entry_takeaway'];
			}

			$insert = $this->common_model->insert_array('session_entry', $data);
			$last_insert_id = $this->db->insert_id(); 
			$_POST['id'] = $last_insert_id;
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $_POST
			];
			$this->set_response($response, REST_Controller::HTTP_OK);		
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
  }


	public function session_read_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){

				$trellis = $this->common_model->select_where("*", "session_entry", array('user_id'=>$user_id))->result_array();
			
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $trellis
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function session_delete_post(){
		$record_id = $_POST['record_id'];

		if(!empty($record_id)){
			$this->db->delete("session_entry", array('id'=>$record_id));
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'deleted successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'message' => 'data not deleted'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

}
