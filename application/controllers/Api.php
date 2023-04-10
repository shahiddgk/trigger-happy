<?php
require APPPATH . 'libraries/TokenHandler.php';
require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

  	protected $token;
	public function __construct()
	{
		parent::__construct();
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
					'message' => 'data instered, mail not allowed'
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
			if($count>0){
				if($count>37){
					$img = 37;
				}else{
					$img = $count;
				}
			}else{
				$img = 1;
			}

			$response = [
				'status' => 200,
				'type' => 'pire',
				'response_count'=> $count,
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
					'message' => 'Get email status udated'
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

}
