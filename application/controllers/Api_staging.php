<?php
require APPPATH . 'libraries/TokenHandler.php';
require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

  	protected $token;
	public function __construct()
	{
		parent::__construct();

		date_default_timezone_set('America/New_York');
        // date_default_timezone_set('Asia/Karachi');
		$this->load->library('stripe_lib');
		// Enable CORS if configured to do so
		if ($this->config->item('enable_cors')) {
            require(APPPATH . 'config/cors.php');
        }

		// $this->output->set_header('Access-Control-Allow-Origin: *');
		// $this->output->set_header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
		// $this->output->set_header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
	
		// // Handle preflight requests
		// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		// 	header('Access-Control-Allow-Origin: *');
		// 	header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
		// 	header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
		// 	exit();
		// }
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

				$subscription = $this->common_model->select_where("*","user_subscriptions", array('user_id'=>$row['id'], 'status'=>'active'));
				$subscription_id = '';
				$customer_id = '';
				$plan_amount = '';
			if($row['is_premium'] == 'yes' && $subscription->num_rows()>0){
				$subscription = $subscription->row_array();
				$subscription_id = $subscription['stripe_subscription_id'];
				$customer_id = $subscription['stripe_customer_id'];
				$plan_amount = $subscription['plan_amount'];
			}

			if($row['status']=='inactive'){
				$response['error'] = 'inactive user';
				$this->set_response($response, REST_Controller::HTTP_OK);
			} 
			if(isset($_POST['device_token'])){
				$device_token	=	$_POST['device_token'];
				$this->common_model->update_array(array('id'=> $row['id']), 'users', array('device_token'=>$device_token));
			}

			$user_data = array(
				'user_logged_in'  =>  TRUE,
				'usertype' => $row['type'],
				'userid' => $row['id'],
				'username' => $row['name'],
				'useremail' => $row['email'],
				'allowemail' => $row['mail_resp'],
				'timezone' => $row['time_zone'],
				'devicetoken' => isset($_POST['device_token']) ? $_POST['device_token'] : '',
				'premium' => $row['is_premium'],
				'premium_type' => $row['premium_type'],
				'current_level' => $row['level'],
				'current_tree' => $row['seed'],
				'subscription_id' => $subscription_id,
				'customer_id' => $customer_id,
				'plan_amount' => $plan_amount

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
				'message' => 'your Password or email is Wrong',
				'user_login' => 'FALSE'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function questions_get(){
		if($this->input->get('type')){
			$type = $this->input->get('type');
		}else{
			$type = 'pire';
		}
		$questions = $this->common_model->select_where_ASC_DESC("*", "questions", array('type'=>$type), 'id', 'ASC')->result_array();
		if($questions){

			foreach ($questions as $key=>$question) {
				if(!empty($question['options'])){
					$options = explode(",", json_decode($question['options']));
					$questions[$key]['options'] = $options;
				}
			}
			if($type == 'naq'){
				$questions = array_chunk($questions, 3);
			}
			$response = [
				'status' => 200,
				'message' => 'success',
				'questions' => $questions
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
			
		} else {
			$response = [
				'status' => 200,
				'message' => 'no data found',
				'questions' => array()
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} 
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

	public function naq_response_post(){
		$user_id = $_POST['user_id'];
		$result = $this->common_model->select_where("*", "answers", array('user_id'=>$user_id, 'type'=>'naq'))->result_array();
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
				'status' => 200,
				'message' => 'no data',
				'single_answer' => array()
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
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

	// PIRE Submit
	public function response_submit_pire_post() {
		$name = $_POST['name'];
		$email = $_POST['email'];
		$user_id = $_POST['user_id'];
		$response_id = random_string('numeric', 8);
	
		$current_garden = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
		$level = $current_garden['level'];
		$seed = $current_garden['seed'];
	
		$answers = json_decode($_POST['answers'], true);
	
		if ($answers) {
			foreach ($answers as $key => $answer) {
				$data = array(
					'question_id' => $key,
					'user_id' => $user_id,
					'response_id' => $response_id,
					'level' => $level,
					'seed' => $seed,
					'type' => 'pire'
				);
	
				if ($answer['type'] == 'radio_btn' || $answer['type'] == 'check_box') {
					$data['options'] = implode(",", $answer['answer']);
					$data['text'] = '';
				} elseif ($answer['type'] == 'open_text') {
					$data['options'] = '';
					$data['text'] = trim(json_encode($answer['answer']), '[""]');
				}
	
				$insert = $this->common_model->insert_array('answers', $data);
			}
	
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'pire', 'response_date' => date('Y-m-d')));
			if ($count == 0) {
				$score_data = array(
					'type' => 'pire',
					'user_id' => $user_id,
					'response_id' => $response_id,
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d')
				);
				$this->common_model->insert_array('scores', $score_data);
			}
	
			$status = $this->common_model->select_single_field('mail_resp', 'users', array('id' => $user_id));
	
			if ($insert && $status == 'yes') {
				$response = $this->common_model->select_two_tab_join_where("a.*, q.title", 'answers a', 'questions q', 'a.question_id=q.id', array('a.response_id' => $response_id));
	
				if ($response->num_rows() > 0) {
					$data['answers'] = $response->result_array();
					$subject = 'Response Submit Confirmation';
					$message = "Dear <b>" . $name . " </b> <br>";
					$message .= "Your answers for Burgeon have been submitted successfully. <br> <hr>";
					$message .= '<table>';
	
					foreach ($data['answers'] as $key => $value) {
						$no = $key + 1;
						$message .= "<tr> <td> <b>Question " . $no . " : </b> " . strip_tags($value['title']) . " </td> </tr>";
						$message .= "<tr> <td> <b>Answer: </b> " . ($value['options'] ? strip_tags($value['options']) : strip_tags($value['text'])) . "</td> </tr>";
						$message .= "<tr><td><hr></td></tr>";
					}
	
					$message .= '</table>';
	
					$this->email->set_newline("\r\n");
					$this->email->set_mailtype('html');
					$this->email->from($this->smtp_user, 'Burgeon');
					$this->email->to($email);
					$this->email->subject($subject);
					$this->email->message($message);
	
					if ($this->email->send()) {
						$response = [
							'status' => 200,
							'message' => 'Mail sent successfully'
						];
						$this->set_response($response, REST_Controller::HTTP_OK);
					} else {
						$error = $this->email->print_debugger();
						$response = [
							'status' => 500,
							'message' => $error
						];
						$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					}
				} else {
					$response = [
						'status' => 200,
						'message' => 'Data inserted'
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
			} else {
				$response = [
					'status' => 200,
					'message' => 'Data inserted, mail not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Invalid JSON format'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	// NAQ Submit 
	public function response_submit_naq_post() {
		$name = $_POST['name'];
		$email = $_POST['email'];
		$user_id = $_POST['user_id'];
		$response_id = random_string('numeric', 8);
	
		$current_garden = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
		$level = $current_garden['level'];
		$seed = $current_garden['seed'];
	
		$total_score = 0;
	
		$answers = json_decode($_POST['answers'], true);
	
		if ($answers) {
			foreach ($answers as $key => $answer) {
				if ($answer['type'] == 'radio_btn') {
					$insert_ans = array(
						'question_id' => $key,
						'options' => strtolower($answer['answer'][0]),
						'text' => strtolower($answer['answer'][0]) == 'yes' ? $answer['res_text'] : '',
						'user_id' => $user_id,
						'response_id' => $response_id,
						'type' => 'naq',
						'level' => $level,
						'seed' => $seed
					);
					$this->common_model->insert_array('answers', $insert_ans);
				}
			}
	
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'naq', 'response_date' => date('Y-m-d')));
			if ($count < 1) {
				$score_data = array(
					'type' => 'naq',
					'user_id' => $user_id,
					'response_id' => $response_id,
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d')
				);
				$this->common_model->insert_array('scores', $score_data);
			}
	
			$response = $this->common_model->join_tab_where_left("a.*, q.title", 'answers a', 'questions q', 'a.question_id=q.id', array('a.response_id' => $response_id), 'q.id', 'ASC');
	
			if ($response->num_rows() > 0) {
				$answer_array = $response->result_array();
	
				$total_score = array_reduce($answer_array, function ($acc, $value) {
					$options = $value['options'];
					$score = ($options === 'never') ? 1 : (($options === 'rarely') ? 2 : (($options === 'often') ? 3 : (($options === 'always') ? 4 : 0)));
					return $acc + $score;
				}, 0);
	
				$naq_score = array(
					'user_id' => $user_id,
					'score' => $total_score,
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d H:i:s')
				);
				$this->common_model->insert_array('naq_scores', $naq_score);
	
				$status = $this->common_model->select_single_field('mail_resp', 'users', array('id' => $user_id));
	
				if ($status == 'yes') {
					$subject = 'Response Submit Confirmation';
					$message = "Dear <b>" . $name . " </b> <br>";
					$message .= "Your answers for Burgeon have been submitted successfully. <br>";
					$message .= "Your NAQ Score is <b>" . $total_score . "/100" . "</b> <br>  <hr>";
					$message .= '<table>';
	
					foreach ($answer_array as $key => $value) {
						$no = $key + 1;
						$question_title = preg_replace('/Q[0-9]+: /', ' ', strip_tags($value['title']));
						$message .= "<tr> <td> <b>Question " . $no . " : </b> " . $question_title . " </td> </tr>";
	
						if ($value['options'] == 'yes' && !empty($value['text'])) {
							$message .= "<tr> <td> <b>Why chosen yes?: </b> " . strip_tags($value['text']) . "</td> </tr>";
						} else {
							$message .= "<tr> <td> <b>Answer: </b> " . ($value['options'] ? ucfirst($value['options']) : strip_tags($value['text'])) . "</td> </tr>";
						}
						$message .= "<tr><td><hr></td></tr>";
					}
	
					$message .= '</table>';
	
					$this->email->set_newline("\r\n");
					$this->email->set_mailtype('html');
					$this->email->from($this->smtp_user, 'Burgeon');
					$this->email->to($email);
					$this->email->subject($subject);
					$this->email->message($message);
	
					if ($this->email->send()) {
						$response = [
							'status' => 200,
							'message' => 'Mail sent successfully',
							'total_score' => $total_score
						];
						$this->set_response($response, REST_Controller::HTTP_OK);
					} else {
						$error = $this->email->print_debugger();
						$response = [
							'status' => 500,
							'message' => 'Email Error:' . $error
						];
						$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					}
				} else {
					$response = [
						'status' => 200,
						'message' => 'Data inserted, mail not allowed',
						'total_score' => $total_score
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
			} else {
				$response = [
					'status' => 400,
					'message' => 'Response submit failed'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Invalid JSON format'
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
					'message' => 'Password changed Successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'Your Old password is incorrect'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		elseif($old_password){
			$result = $this->common_model->update_array(array('id'=> $user_id,'password'=>sha1($old_password)), 'users', $update);
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'Password changed Successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'Your Old password is incorrect'
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
		// "DELETE u, ul, gs, lh, s, sc, r
		// FROM users AS u
		// LEFT JOIN user_levels AS ul ON u.id = ul.user_id
		// LEFT JOIN garden_seeds AS gs ON u.id = gs.user_id
		// LEFT JOIN level_history AS lh ON u.id = lh.user_id
		// LEFT JOIN seeds AS s ON u.id = s.user_id
		// LEFT JOIN scores AS sc ON u.id = sc.user_id
		// LEFT JOIN other_related_table AS r ON u.id = r.user_id
		// WHERE u.id = <user_id>";

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

	public function growth_tree_post() {
		if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
			$user_id = $_POST['user_id'];
	
			$current_garden = $this->common_model->join_three_tables(
				"users.level, users.seed, seeds.seed_name, seeds.count", 'users',
				'garden_levels levels', 'levels.id = users.level',
				'garden_seeds seeds', 'seeds.id = users.seed',
				array('users.id' => $user_id)
			)->row_array();
	
			if (!empty($current_garden)) {
				$level = $current_garden['level'];
				$seed = $current_garden['seed'];
				$seed_name = $current_garden['seed_name'];
				$max_count = $current_garden['count'];
	
				$score = $this->common_model->select_where_groupby(
					'*', 'scores', array('user_id' => $user_id, 'level' => $level, 'seed' => $seed), 'response_date'
				)->num_rows();

				if (($user_id == '166' && $level == '1') || ($user_id == '286' && in_array($level, ['1', '2']))) {
					$score = $max_count;
				}
	
				$img = $score + 1;
				// $img = min(max($score + 1, 1), $max_count);
				$previous_img = max($img - 1, 1);

				$seed_count = $this->common_model->select_where('*', 'level_history', array('user_id' => $user_id, 'level' => $level))->num_rows();
				if($level == 1){
					$seed_count == 1 ? $level_complete = 1 : $level_complete = 0;
				}else{
					$seed_count == 3 ? $level_complete = 1 : $level_complete = 0;
				}
	
				$mobile_folder = $seed_name.'_tree/'.$seed_name. '_mobile';
				$ipad_folder = $seed_name.'_tree/'.$seed_name. '_ipad';
	
	
				$response = [
					'status' => 200,
					'response_count' => $score,
					'garden_type' => $seed_name,
					'max_count' => $max_count,
					'current_level' => $level,
					'level_status' => $level_complete == 1 ? 'complete' : 'incomplete',
					'seed_count' => $seed_count,
					'next_level' => $level + 1,
					'is_pop_up_for_new_tree_selection' => $score == $max_count ? true : false,
					'mobile_image_url' => base_url('uploads/' . $mobile_folder . '/') . $img . '.png',
					'ipad_image_url' => base_url('uploads/' . $ipad_folder . '/') . $img . '.png',
					'mobile_previous_image_url' => base_url('uploads/' . $mobile_folder . '/') . $previous_img . '.png',
					'ipad_previous_image_url' => base_url('uploads/' . $ipad_folder . '/') . $previous_img . '.png',
				];
	
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 400,
					'message' => 'invalid garden level'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}	

	public function change_garden_type_post()
    {
        $entity_id = $_POST['user_id'];
        if(!empty($entity_id)){
            if(isset($_POST['garden_type']) && !empty($_POST['garden_type'])){
                $garden_type = $_POST['garden_type'];
            }
            else{
                $garden_type = 'active';
            }
            $this->common_model->update_array(array('id'=>$entity_id), "users", array('garden_type'=>$garden_type));
            $updated_record = $this->common_model->select_where("*", "users", array('id ' => $entity_id))->row_array();
            if (!empty($updated_record)) {
                $response = [
                    'status' => 200,
                    'message' => 'success',
                    'data' => $updated_record
                ];
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response = [
                    'status' => 404,
                    'message' => 'Record not found'
                ];
                $this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
            }
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
			$result_array = $this->common_model->select_where_ASC_DESC_Group_by("DATE(created_at) as date", "answers", array('user_id'=>$user_id , 'type'=>'pire'),  'DATE(created_at)', 'ASC', 'DATE(created_at)' )->result_array();

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

	// Trellis API's Start
	public function ladder_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($data['login']->num_rows()>0) {
			$user_data = $data['login']->row_array();
			$type = $_POST['type'];
			
			$row_count = $this->common_model->select_where("*", "ladder", array('user_id'=>$user_id, 'type'=>$type))->num_rows();
			
			if($user_data['is_premium'] == 'no' && $row_count >= 2){ 
				
				$response = [
					'status' => 400,
					'message' => 'more responses not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
			else{
				$insert['user_id'] = $user_id;
				$insert['type'] = $_POST['type'];
				if(isset($_POST['option1']) && !empty($_POST['option1'])){
					$insert['option1'] = $_POST['option1'];
				}
				if(isset($_POST['option2']) && !empty($_POST['option2'])){
					$insert['option2'] = $_POST['option2'];
				}
				if(isset($_POST['date']) && !empty($_POST['date'])){
					$dateObj = DateTime::createFromFormat('m-d-y', $_POST['date']);
					$insert['date'] = $dateObj->format('Y-m-d');
					$_POST['date'] = $insert['date'];
				}
				if(isset($_POST['text']) && !empty($_POST['text'])){
					$insert['text'] = $_POST['text'];
				}
				if(isset($_POST['description']) && !empty($_POST['description'])){
					$insert['description'] = $_POST['description'];
				}
				
				$this->common_model->insert_array('ladder', $insert);
				$last_insert_id = $this->db->insert_id(); 
				$_POST['id'] = $last_insert_id;
				$_POST['favourite'] = 'no';
				$response = [
					'status' => 200,
					'message' => 'success',
					'post_data' => $_POST
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no user found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function ladder_update_post() {
		$id = $_POST['id']; 
		
		$ladder_entry = $this->common_model->select_where("*", "ladder", array('id' => $id))->row_array();
		
		if ($ladder_entry) {
			$type = $_POST['type'];
			
			$update = array('type' => $type);
			if (isset($_POST['option1']) && !empty($_POST['option1'])) {
				$update['option1'] = $_POST['option1'];
			}
			if (isset($_POST['option2']) && !empty($_POST['option2'])) {
				$update['option2'] = $_POST['option2'];
			}
			if (isset($_POST['date']) && !empty($_POST['date'])) {
				$dateObj = DateTime::createFromFormat('m-d-y', $_POST['date']);
				if ($dateObj !== false) {
					$update['date'] = $dateObj->format('Y-m-d');
				}
			}
			if (isset($_POST['text']) && !empty($_POST['text'])) {
				$update['text'] = $_POST['text'];
			}
			if (isset($_POST['description']) && !empty($_POST['description'])) {
				$update['description'] = $_POST['description'];
			}
			$update['updated_at'] = date('Y-m-d H:i:s');
			
			$this->common_model->update_array(array('id' => $id), 'ladder', $update);
			$update_row = $this->common_model->select_where("*", 'ladder', array('id' => $id))->row_array();
			
			$response = [
				'status' => 200,
				'message' => 'success',
				'updated_data' => $update_row
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 400,
				'message' => 'no ladder entry found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function add_fav_ladder_post(){
		$entity_id = $_POST['entity_id'];

		if(!empty($entity_id)){
			
			if(isset($_POST['status']) && !empty($_POST['status'])){

				$status = $_POST['status'];
			}
			else{
				$status = 'no';
			}
			$this->common_model->update_array(array('id'=>$entity_id), "ladder", array('favourite'=>$status));
		
			$updated_record = $this->common_model->select_where("*", "ladder", array('id' => $entity_id))->row_array();

			if (!empty($updated_record)) {
				$response = [
					'status' => 200,
					'message' => 'success',
					'data' => $updated_record
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 404,
					'message' => 'Record not found'
				];
				$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function tribe_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($data['login']->num_rows()>0) {
			$user_data = $data['login']->row_array();
			$row_count = $this->common_model->select_where("*", "tribe", array('user_id'=>$user_id))->num_rows();
			$tribe = $this->common_model->select_single_field("tribe", "settings", array('id'=>'1'));
			
			if($user_data['is_premium'] == 'no' && $row_count >= $tribe){ 
				
				$response = [
					'status' => 400,
					'message' => 'more responses not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
			else{
				$insert['user_id'] = $user_id;
				if(isset($_POST['mentor']) && !empty($_POST['mentor'])){
					$insert['mentor'] = $_POST['mentor'];
				}
				if(isset($_POST['peer']) && !empty($_POST['peer'])){
					$insert['peer'] = $_POST['peer'];
				}
				if(isset($_POST['mentee']) && !empty($_POST['mentee'])){
					$insert['mentee'] = $_POST['mentee'];
				}
				
				$this->common_model->insert_array('tribe', $insert);
				$last_insert_id = $this->db->insert_id(); 
				$_POST['id'] = $last_insert_id;
				$response = [
					'status' => 200,
					'message' => 'success',
					'post_data' => $_POST
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no user found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function principles_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($data['login']->num_rows()>0) {
			$user_data = $data['login']->row_array();
			$type = $_POST['type'];
			
			$row_count = $this->common_model->select_where("*", "principles", array('user_id'=>$user_id, 'type'=>$type))->num_rows();
			$principle = $this->common_model->select_single_field("principle", "settings", array('id'=>'1'));
			
			if($user_data['is_premium'] == 'no' && $row_count >= $principle){ 
				
				$response = [
					'status' => 400,
					'message' => 'more responses not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
			else{
				$insert['user_id'] = $user_id;
				$insert['type'] = $_POST['type'];
				if(isset($_POST['emp_truths'])){
					$insert['emp_truths'] = $_POST['emp_truths'];
				}
				if(isset($_POST['powerless_believes'])){
					$insert['powerless_believes'] = $_POST['powerless_believes'];
				}
				
				$this->common_model->insert_array('principles', $insert);
				$last_insert_id = $this->db->insert_id(); 
				$_POST['id'] = $last_insert_id;
				$response = [
					'status' => 200,
					'message' => 'success',
					'post_data' => $_POST
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no user found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function identity_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($data['login']->num_rows()>0) {
			$user_data = $data['login']->row_array();
			$type = $_POST['type'];
			
			$row_count = $this->common_model->select_where("*", "identity", array('user_id'=>$user_id, 'type'=>$type))->num_rows();
			$identity = $this->common_model->select_single_field("identity", "settings", array('id'=>'1'));
			
			if($user_data['is_premium'] == 'no' && $row_count >= $identity){ 
				
				$response = [
					'status' => 400,
					'message' => 'more responses not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
			else{
				
				$insert['user_id'] = $user_id;
				$insert['type'] = $_POST['type'];
				if(isset($_POST['text']) && !empty($_POST['text'])){
					$insert['text'] = $_POST['text'];
				}
				
				$this->common_model->insert_array('identity', $insert);
				$last_insert_id = $this->db->insert_id(); 
				$_POST['id'] = $last_insert_id;
				$response = [
					'status' => 200,
					'message' => 'success',
					'post_data' => $_POST
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no user found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	// Trellis Insert End

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
			// else if($table == 'ladder'){
			// 	$type = $_POST['type'];
			// 	$trellis = $this->common_model->select_where_ASC_DESC("*", "$table", array('user_id'=>$user_id, 'type'=>$type), 'date', 'desc' )->result_array();
			// }
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

			$current_garden = $this->common_model->select_where("level, seed","users", array('id'=>$_POST['user_id']))->row_array();
			$level = $current_garden['level'];
			$seed = $current_garden['seed'];

			$data['user_id'] = $_POST['user_id'];

			if(isset($_POST['entry_title'])){
				$data['entry_title'] = $_POST['entry_title'];
			}
			if(isset($_POST['entry_decs'])){
				$data['entry_decs'] = $_POST['entry_decs'];
			}
			if(isset($_POST['entry_date'])){
				$dateObj = DateTime::createFromFormat('m-d-y', $_POST['entry_date']);
				$data['entry_date'] = $dateObj->format('Y-m-d');
				$_POST['entry_date'] = $data['entry_date'];
			}
			if(isset($_POST['entry_type'])){
				$data['entry_type'] = $_POST['entry_type'];
				$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $data['user_id'], 'type'=>'column', 'response_date' => date('Y-m-d')));

				if($count < 1){

					$insert['type'] = 'column';
					$insert['user_id'] = $data['user_id'];
					$insert['response_date'] = date('Y-m-d');
					$insert['level'] = $level;
					$insert['seed'] = $seed;
					$this->db->insert('scores', $insert );

					$response = [
						'status' => 200,
						'message' => 'column score added successfully'
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}else{
					$response = [
						'status' => 200,
						'message' => 'Column already exists'
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
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

				$trellis = $this->common_model->select_where_ASC_DESC("*", "session_entry", array('user_id'=>$user_id), 'entry_date', 'desc')->result_array();
			
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

	public function app_version_get() {
		$result_array = $this->common_model->select_all("*", 'settings')->result_array();
		if (count($result_array) > 0) {
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $result_array
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 400,
				'message' => 'no data found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function test_query_get() {

		$scores =  $this->common_model->select_where_groupby('*', 'scores', array('user_id' => '166') , 'response_date')->result_array();

		// echo $this->db->last_query(); exit;
        // echo  $scores; exit;
        echo "<pre>"; print_r($scores); exit;

	}

	public function payment_settings_post() {
		$user_id	=	$_POST['user_id'];
			
		$valid_user = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($valid_user->num_rows()>0){
			$payment_keys = $this->common_model->select_all("test_public_key, live_public_key", 'payment_settings')->row_array();
		
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $payment_keys
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else{
			$response = [
				'status' => 400,
				'message' => 'in valid user'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function trellis_trigger_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
            $count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type'=>'trellis', 'response_date' => date('Y-m-d')));
		
			$current_garden = $this->common_model->select_where("level, seed","users", array('id'=>$user_id))->row_array();
			$level = $current_garden['level'];
			$seed = $current_garden['seed'];

			if($count < 1){

				$insert['type'] = 'trellis';
				$insert['user_id'] = $user_id;
				$insert['level'] = $level;
				$insert['seed'] = $seed;
				$insert['response_date'] = date('Y-m-d');
				$this->db->insert('scores', $insert );

				$response = [
					'status' => 200,
					'message' => 'score updated successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'message' => 'You have already received reward for the day'
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

	public function ladder_trigger_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
            $count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type'=>'ladder', 'response_date' => date('Y-m-d')));
			
			$current_garden = $this->common_model->select_where("level, seed","users", array('id'=>$user_id))->row_array();
			$level = $current_garden['level'];
			$seed = $current_garden['seed'];

			if($count < 1){

				$insert['type'] = 'ladder';
				$insert['user_id'] = $user_id;
				$insert['level'] = $level;
				$insert['seed'] = $seed;
				$insert['response_date'] = date('Y-m-d');
				$this->db->insert('scores', $insert );

				$response = [
					'status' => 200,
					'message' => 'score updated successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'message' => 'You have already received reward for the day'
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
	
	public function stripe_payment_post() {
		$token = json_decode($_POST['token'], true);
		$user_id = $_POST['user_id'];
		

		if (!empty($token) && !empty($user_id)) {
			
			$pkg_text = $_POST['pkg_text'];
			$pkg_amount = $_POST['pkg_amount'];
			$pkg_interval = $_POST['pkg_interval'];
			
			// Special User Package 08-12-2023
			if($user_id == '123' && $pkg_interval == 'month'){
				$pkg_amount = '1';
			}
			
			$user = $this->common_model->select_where("*", "users", array('id'=>$user_id))->row_array();
			if($user){
				$customer = $this->stripe_lib->addCustomer($user['name'], $user['email'], $token['id']); 
				
				if($customer !== null && isset($customer['id']) &&  !empty($customer['id'])){ 
					$plan = $this->stripe_lib->createPlan($pkg_text, $pkg_amount, $pkg_interval); 
				
					if($plan !== null && isset($plan['id']) && !empty($plan['id'])){ 

						$subscription = $this->stripe_lib->createSubscription($customer['id'], $plan['id']); 
						
						if($subscription !== null && isset($subscription['id']) && !empty($subscription['id'])){ 

							$start_date =  $subscription['start_date'];
							$sub_start_date = date('Y-m-d H:i:s', $start_date);
							
							if($plan['interval'] == 'month'){
								$sub_end_date = date('Y-m-d H:i:s', strtotime('+1 month',  $start_date));
							}else if($plan['interval'] == 'year'){
								$sub_end_date = date('Y-m-d H:i:s', strtotime('+1 year',  $start_date));
							}else if($plan['interval'] == 'day'){
								$sub_end_date = date('Y-m-d H:i:s', strtotime('+1 day',  $start_date));
							}
							$sub_data['user_id'] = $user_id;
							$sub_data['payment_method'] = $token['card']['brand'];
							$sub_data['stripe_subscription_id'] = $subscription['id'];
							$sub_data['stripe_customer_id'] = $customer['id'];
							$sub_data['plan_amount'] = $pkg_amount;
							$sub_data['plan_amount_currency'] = $plan['currency'];
							$sub_data['plan_interval'] = $plan['interval'];
							$sub_data['plan_interval_count'] = $plan['interval_count'];
							$sub_data['plan_period_start'] = $sub_start_date;
							$sub_data['plan_period_end'] = $sub_end_date;
							$sub_data['payer_email'] = $customer['email'];
							$sub_data['status'] = $subscription['status'];

							if($subscription['status'] == 'active'){
								$this->common_model->insert_array('user_subscriptions', $sub_data);

								$last_insert_id = $this->db->insert_id(); 
								$sub_data['id'] = $last_insert_id;
		
								if(!empty($last_insert_id)){
									$update['is_premium'] = 'yes';
									$update['premium_type'] = $pkg_interval;
									$this->common_model->update_array(array('id'=> $user_id), 'users', $update);
								}
								
								$response = [
									'status' => 200,
									'message' => 'subscription created successfully',
									'data' => $sub_data
								];
								$this->set_response($response, REST_Controller::HTTP_OK);
							}
							else{
								$response = [
									'status' => 200,
									'message' => 'subscription status is not active',
									'data' => $sub_data
								];
								$this->set_response($response, REST_Controller::HTTP_OK);
							}
						}else{
							$response = [
								'status' => 400,
								'error' => 'subscription not created',
								'message' => $subscription
							];
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
						}
					}else{
						$response = [
							'status' => 400,
							'error' => 'plan not created',
							'message' => $plan
						];
						$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
					}
				
				}else{
					$response = [
						'status' => 400,
						'error' => 'customer not created',
						'message' => $customer
					];
					$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				}
			}else{
				$response = [
					'status' => 400,
					'message' => 'user not found'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}	

	public function subscription_cancel_post(){
		$subscription_id = $_POST['subscription_id'];

		if(!empty($subscription_id)){

			$cancelSuscription = $this->stripe_lib->cancelSuscription($subscription_id);
			if(@$cancelSuscription['status'] == 'canceled'){
				$update_sub['status'] = 'canceled';
				$update_sub['canceled_at'] = date('Y-m-d H:i:s');

				$this->common_model->update_array(array('stripe_subscription_id'=> $subscription_id), 'user_subscriptions', $update_sub);
				$user_id = $this->common_model->select_single_field("user_id", "user_subscriptions", array('stripe_subscription_id'=> $subscription_id));
				if(!empty($user_id)){
					
					$update_user['is_premium'] = 'no';
					$update_user['premium_type'] = '';

					$this->common_model->update_array(array('id'=> $user_id), 'users', $update_user);

					$response = [
						'status' => 200,
						'message' => 'subscription cancelled successfully',
						'data' => $cancelSuscription
					];
					$this->set_response($response, REST_Controller::HTTP_OK); 
				}
			}else{
				$response = [
					'status' => 400,
					'error' => 'subscription not cancelled',
					'message' => $cancelSuscription
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

	public function subscription_details_post(){

		$user_id	=	$_POST['user_id'];
			
		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($data['login']->num_rows()>0){
			$row = $data['login']->row_array();

			$subscription = $this->common_model->select_where("*","user_subscriptions", array('user_id'=>$row['id'], 'status'=>'active'));
				$subscription_id = '';
				$customer_id = '';
				$plan_amount = '';
			if($row['is_premium'] == 'yes' && $subscription->num_rows()>0){
				$subscription = $subscription->row_array();
				$subscription_id = $subscription['stripe_subscription_id'];
				$customer_id = $subscription['stripe_customer_id'];
				$plan_amount = $subscription['plan_amount'];
			}

			if($row['status']=='inactive'){
				$response['error'] = 'inactive user';
				$this->set_response($response, REST_Controller::HTTP_OK);
			}

			$user_data = array(
				'usertype' => $row['type'],
				'userid' => $row['id'],
				'username' => $row['name'],
				'useremail' => $row['email'],
				'allowemail' => $row['mail_resp'],
				'premium' => $row['is_premium'],
				'premium_type' => $row['premium_type'],
				'subscription_id' => $subscription_id,
				'customer_id' => $customer_id,
				'plan_amount' => $plan_amount

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
				'message' => 'no user found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}

	public function subscription_update_post(){
		$subscription_id = $_POST['subscription_id'];

		if(!empty($subscription_id)){

			$cancelSuscription = $this->stripe_lib->updateSuscription($subscription_id);
			$response = [
				'status' => 200,
				'message' => 'subscription retrived successfully',
				'data' => $cancelSuscription
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

	// public function response_submit_garden_post(){

	// 	$name = $_POST['name'];
	// 	$email = $_POST['email'];
	// 	$user_id = $_POST['user_id'];
	// 	$type = $_POST['type'];

	// 	$column_type = isset($_POST['column_type']) ? $_POST['column_type'] : '';

	// 	$answers = json_decode($_POST['answers'], true);
 
	// 	if ($answers) {
	// 		$response_id = random_string('numeric', 8);
	// 		foreach ($answers as $key => $answer) {
	// 			$data = [];
	// 			$data['question_id'] = $key;
	// 			$data['options'] = '';
	// 			$data['text'] = '';

	// 			if ($answer['type'] == 'radio_btn') {
	// 				$options = implode(",", $answer['answer']);
	// 				$data['options'] = $options;
	// 				$data['text'] = strtolower($answer['answer'][0]) == 'yes' ? $answer['res_text'] : '';
	// 			} else if ($answer['type'] == 'check_box') {
	// 				$checks = implode(",", $answer['answer']);
	// 				$data['options'] = $checks;
	// 				$data['text'] = strtolower($answer['answer'][0]) == 'yes' ? $answer['res_text'] : '';
	// 			} else if ($answer['type'] == 'open_text') {
	// 				$data['text'] = trim(json_encode($answer['answer']), '[""]');
	// 			}

	// 			$data['user_id'] = $user_id;
	// 			$data['response_id'] = $response_id;
	// 			$data['type'] = $type;

	// 			if ($column_type == 'roses') {
	// 				$insert = $this->common_model->insert_array('rose_answers', $data);
	// 			} elseif ($column_type == 'tomatoes') {
	// 				$insert = $this->common_model->insert_array('tomatoes_answers', $data);
	// 			}
	// 		}
 
	// 		$count = $this->common_model->select_where_table_rows('*', 'secondary_scores', array('user_id' => $user_id, 'type' => $type, 'response_date' => date('Y-m-d')));
	// 		if ($count < 1) {
	// 			$insert = array();
	// 			$insert['type'] = $type;
	// 			$insert['user_id'] = $user_id;
	// 			$insert['response_date'] = date('Y-m-d');
	// 			$this->common_model->insert_array('secondary_scores', $insert);
	// 		}
	// 		if ($this->db->affected_rows() > 0) {
	// 			$response = [
	// 				'status' => 200,
	// 				'message' => 'Data Inserted Successfully'
	// 			];
	// 			$this->set_response($response, REST_Controller::HTTP_OK);
	// 		}
	// 	} else {
	// 		$response = [
	// 			'status' => 400,
	// 			'message' => 'Invalid JSON format'
	// 		];
	// 		$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
	// 	}
	// } 
 
	public function new_tribe_insert_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id, 'type'=>'user'));
		
		if($data['login']->num_rows()>0) {
			$user_data = $data['login']->row_array();

			$row_count = $this->common_model->select_where_groupby("*", "tribe_new", array('user_id'=>$user_id), "type")->num_rows();
			if($row_count >= 3){
				$tibe_rows = 1;
			}else{
				$tibe_rows = 0;
			}
			
			$tribe = $this->common_model->select_single_field("tribe", "settings", array('id'=>'1'));
			
			if($user_data['is_premium'] == 'no' && $tibe_rows == $tribe){ 
				
				$response = [
					'status' => 400,
					'message' => 'more responses not allowed'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
			else{
				$insert['user_id'] = $user_id;
				$insert['type'] = $_POST['type'];
				$insert['text'] = $_POST['text'];
				
				$this->common_model->insert_array('tribe_new', $insert);
				$last_insert_id = $this->db->insert_id(); 
				$_POST['id'] = $last_insert_id;
				$response = [
					'status' => 200,
					'message' => 'success',
					'post_data' => $_POST
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
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

	public function new_tribe_read_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			$trellis['tribe'] = $this->common_model->select_where("*", "tribe_new", array('user_id'=>$user_id))->result_array();

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

	public function new_tribe_delete_post(){
		$record_id = $_POST['record_id'];

		if(!empty($record_id)){

			$this->db->delete("tribe_new", array('id'=>$record_id));
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'deleted successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}	else{
				$response = [
					'status' => 400,
					'message' => 'data not found'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} 	else{
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function new_response_history_post() {
		if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
			$user_id = $_POST['user_id'];
			$score_data =  $this->common_model->select_where('*', 'scores', array('user_id' => $user_id))->result_array();
	
			$sorted_array = array();
			$score = 1;
			foreach ($score_data as $value) {
				$date_index = $value['response_date'];
	
				if (!isset($sorted_array[$date_index])) {
					$sorted_array[$date_index] = array(
						'date' => $date_index,
						'score' => $score,
						'trellis_count' => '',
						'ladder_count' => '',
						'column_count' => '',
						'pire_count' => array(),
						'naq_count' => array()
					);
					$score++;
				}
	
				if ($value['type'] == 'pire') {
						$sorted_array[$date_index]['pire_count'] = $this->common_model->select_where_groupby("response_id", "answers", array('user_id'=>$value['user_id'], 'type'=>'pire', 'DATE(created_at)'=>$date_index), 'response_id' )->result_array();
					} elseif ($value['type'] == 'naq') {
						$sorted_array[$date_index]['naq_count'] = $this->common_model->select_where_groupby("response_id", "answers", array('user_id'=>$value['user_id'], 'type'=>'naq', 'DATE(created_at)'=>$date_index), 'response_id' )->result_array();
					} elseif ($value['type'] == 'column') {
						$sorted_array[$date_index]['column_count'] = '1';
					} elseif ($value['type'] == 'trellis') {
						$sorted_array[$date_index]['trellis_count'] = '1';
					} elseif ($value['type'] == 'ladder') {
						$sorted_array[$date_index]['ladder_count'] = '1';
					}
			}
	
			ksort($sorted_array);
	
			$final_array = array_values($sorted_array);
			$response = [
				'status' => 200,
				'response_data' => $final_array
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	public function history_details_post(){
		$response_id = $_POST['response_id'];

		if(!empty($response_id)){
			$result = $this->common_model->join_tab_where_left("q.id, a.user_id, a.type, q.title as question, a.options, a.text, a.created_at",'answers a', 'questions q', 'a.question_id=q.id', array('a.response_id'=>$response_id), 'q.id', 'ASC')->result_array();

			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $result
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

	public function naq_data_exist_post(){

		$user_id	=	$_POST['user_id'];

		if(!empty($user_id)){
				
			$answers = $this->common_model->select_where("*","answers", array('user_id'=>$user_id , 'type'=>'naq'));
			
			if($answers->num_rows()>0){
				$response = [
					'status' => 200,
					'exist' => 'yes',
					'message' => 'yes, response exist'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'exist' => 'no',
					'message' => 'no, response not exist'
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

	public function insert_reminder_post(){
		$formated_date = '';
		if($_POST['reminder_type'] == 'once'){
			$days_list = '[]';
		}else{
			$input = $_POST['day_list'];
			$input = str_replace(['[', ']', '\''], '', $input);
			$weekdays = array_map('trim', explode(',', $input));
			
			$order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
			usort($weekdays, function ($a, $b) use ($order) {
				return array_search(strtolower($a), $order) - array_search(strtolower($b), $order);
			});
			
			$weekdays = array_map('ucfirst', $weekdays);
			$days_list = json_encode($weekdays);
			
		}

		if(isset($_POST['date'])){
			$dateObj = DateTime::createFromFormat('m-d-y', $_POST['date']);
			$formated_date = $dateObj->format('Y-m-d');

            $time = $_POST['time'].' '.$_POST['time_type'];
            $datetime = DateTime::createFromFormat('Y-m-d h:i A', $formated_date . ' ' . $time);
            $datetime_str = $datetime->format('Y-m-d H:i:s');
		}

		$end_date = NULL;
        if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
            $dateObj = DateTime::createFromFormat('m-d-y', $_POST['end_date']);
            $end_date = $dateObj->format('Y-m-d');
        }

        $insert_data = [
			'user_id' => $_POST['user_id'],
            'text' => $_POST['text'],
			'day_list' => $days_list,
			'status' => $_POST['status'],
			'date_time' => $datetime_str,
			'end_date' => $end_date,
			'reminder_type' => $_POST['reminder_type']
		];

        $reminder_id = $this->common_model->insert_array('reminders', $insert_data);

        if ($reminder_id) {
			$insert_id = $this->db->insert_id(); 

			$post_fields = [
				'id' => $insert_id,
				'user_id' => $_POST['user_id'],
				'text' => $_POST['text'],
				'day_list' => $days_list,
				'date' => $formated_date,
				'time' => $_POST['time'],
				'time_type' => $_POST['time_type'],
				'end_date' => $end_date,
				'status' => $_POST['status'],
				'reminder_type' => $_POST['reminder_type']
			];

			$response = [
				'status' => 200,
				'message' => 'Reminder successfully Created ', 
				'post_data' => $post_fields
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
        } else {
			$response = [
				'status' => 400,
				'message' => 'Reminder not created'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

	public function read_reminder_post(){
		$user_id = $_POST['user_id'];
		$result = $this->common_model->select_where("*", "reminders", array('user_id'=>$user_id))->result_array();
		if($result){
			foreach ($result as $key => $value) {
				$date_time = $value['date_time'];
				$date = date_create_from_format('Y-m-d H:i:s', $date_time);
				
				if ($date !== false) {
					$formatted_date = date_format($date, 'Y-m-d');
					$formatted_time = date_format($date, 'h:i');
					$formatted_time_type = date_format($date, 'A');
					
					$result[$key]['date'] = $formatted_date;
					$result[$key]['time'] = $formatted_time;
					$result[$key]['time_type'] = $formatted_time_type;
					$result[$key]['end_date'] = $value['end_date'];
					
					unset($result[$key]['date_time']);
				}
			}
			
			$response = [
				'status' => 200,
				'message' => 'success',
				'single_answer' => $result
			];
		}
		else{
			$response = [
				'status' => 200,
				'message' => 'no data',
				'single_answer' => array()
			];
		}
		
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function edit_reminder_post(){
		$formated_date = '';
		$id = $_POST['id'];

		if($_POST['reminder_type'] == 'once'){
			$days_list = '[]';
		}else{
			$input = $_POST['day_list'];
			$input = str_replace(['[', ']', '\''], '', $input);
			$weekdays = array_map('trim', explode(',', $input));
			
			$order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
			usort($weekdays, function ($a, $b) use ($order) {
				return array_search(strtolower($a), $order) - array_search(strtolower($b), $order);
			});
			
			$weekdays = array_map('ucfirst', $weekdays);
			$days_list = json_encode($weekdays);
		}

		if(isset($_POST['date'])){
			$dateObj = DateTime::createFromFormat('m-d-y', $_POST['date']);
			$formated_date = $dateObj->format('Y-m-d');

			$time = $_POST['time'].' '.$_POST['time_type'];
            $datetime = DateTime::createFromFormat('Y-m-d h:i A', $formated_date . ' ' . $time);
            $datetime_str = $datetime->format('Y-m-d H:i:s');
		}

		$end_date = NULL;
        if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
            $dateObj = DateTime::createFromFormat('m-d-y', $_POST['end_date']);
            $end_date = $dateObj->format('Y-m-d');
        }

		$update_data = [
			'text' => $_POST['text'],
			'day_list' => $days_list,
			'status' => $_POST['status'],
			'date_time' => $datetime_str,
			'end_date' => $end_date,
			'reminder_type' => $_POST['reminder_type']
		];

		$this->db->where('id', $id);
		$reminder_exists = $this->db->get('reminders')->num_rows() > 0;

		if ($reminder_exists) {
			$this->db->where('id', $id);
			$result = $this->db->update('reminders', $update_data);

			if ($result) {
				$update_data = [
					'id' => $id,
					'text' => $_POST['text'],
					'day_list' => $days_list,
					'date' => $formated_date,
					'time' => $_POST['time'],
					'time_type' => $_POST['time_type'],
					'end_date' => $end_date,
					'status' => $_POST['status'],
					'reminder_type' => $_POST['reminder_type']
				];

				$response = [
					'status' => 200,
					'message' => 'Reminder updated successfully',
					'updated_data' => $update_data
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 400,
					'message' => 'Failed to update reminder',
					'id' => $id
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 404,
				'message' => 'Reminder not found',
				'id' => $id
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function delete_reminder_post(){

		$result = $this->db->delete('reminders', array('id'=>$_POST['id']));
	
		if ($result) {
			$response = [
				'status' => 200,
				'message' => 'Reminder deleted successfully',
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 400,
				'message' => 'Failed to delete reminder',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	 
	public function update_reminder_status_post() {
		$entity_id = isset($_POST['entity_id']) ? $_POST['entity_id'] : null;
	
		if (!empty($entity_id)) {
			if (isset($_POST['status']) && !empty($_POST['status'])) {
				$status = $_POST['status'];
			} else {
				$status = 'active';
			}
	
			$this->common_model->update_array(array('id' => $entity_id), "reminders", array('status' => $status));
	
			$updated_record = $this->common_model->select_where("*", "reminders", array('id' => $entity_id))->row_array();
	
			if (!empty($updated_record)) {
				$date_time = $updated_record['date_time'];
				$date = date_create_from_format('Y-m-d H:i:s', $date_time);
	
				if ($date !== false) {
					$updated_record['date'] = date_format($date, 'Y-m-d');
					$updated_record['time'] = date_format($date, 'h:i');
					$updated_record['time_type'] = date_format($date, 'A');
	
					unset($updated_record['date_time']);
				}
	
				$response = [
					'status' => 200,
					'message' => 'success',
					'data' => $updated_record
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 404,
					'message' => 'Record not found'
				];
				$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function snooze_reminder_post() {
		
		if (isset($_POST['entity_id']) && !empty($_POST['entity_id'])) {
			$entity_id = $_POST['entity_id'];
	
			$this->common_model->update_array(array('id' => $entity_id), "reminders", array('snooze' => 'yes', 'updated_at' => date('Y-m-d H:i:s')));
	
			if($this->db->affected_rows() > 0){
				$response = [
					'status' => 200,
					'message' => 'reminder updated successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'reminder not updated'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function reminder_stop_post() {
	
		if (isset($_POST['entity_id']) && isset($_POST['reminder_stop']) && !empty($_POST['entity_id'] && $_POST['reminder_stop'])) {
	
			$entity_id = $_POST['entity_id'];
			$reminder_stop = $_POST['reminder_stop'];

			$this->common_model->update_array(array('id' => $entity_id), "reminders", array('reminder_stop' => $reminder_stop, 'updated_at' => date('Y-m-d H:i:s')));

			if($this->db->affected_rows() > 0){
				$response = [
					'status' => 200,
					'message' => 'reminder updated successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'reminder not updated'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	public function skip_reminders_post() {
		if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
			$user_id = $_POST['user_id'];
	
			// Get user's time zone from the database
			$userTimeZone = $this->common_model->select_single_field("time_zone", "users", array('id' => $user_id));
			if (isset(valid_timezone()[$userTimeZone])) {
				$validTimeZone = valid_timezone()[$userTimeZone];
		
				// Get current time in UTC and user's local time
				$currentTimeUTC = new DateTimeImmutable('now', new DateTimeZone('UTC'));
				$userTime = new DateTimeZone($validTimeZone);
				$currentTime = new DateTimeImmutable('now', $userTime);
		
				$skipped_reminders = $this->get_skipped_reminders($user_id, $currentTime);
		
				if (!empty($skipped_reminders)) {
					$response = [
						'status' => 200,
						'message' => 'Skipped reminders found',
						'result' => $skipped_reminders
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				} else {
					$response = [
						'status' => 200,
						'message' => 'No skipped reminders found',
						'result' => $skipped_reminders
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
			} else {
				$response = [
					'status' => 400,
					'message' => 'Invalid or undefined time zone'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	// Helper function to get skipped reminders
	private function get_skipped_reminders($user_id, $currentTime) {

		// echo 'get_skipped_reminders'. $currentTime->format('Y-m-d H:i:s'); exit;
		$skipped_reminders = [];
	
		$query = $this->common_model->select_where("*", "reminders", array(
			'status' => 'active',
			'user_id' => $user_id,
			'reminder_stop' => 'skip',
			'snooze' => 'no',
			'date_time <=' => $currentTime->format('Y-m-d H:i:s')
		));
	
		if ($query->num_rows() > 0) {
			$skipped_reminders = $query->result_array();
		}
	
		return $skipped_reminders;
	}


	// Garden Upgraded with new schema
	public function garden_levels_get(){
	
		$garden_levels = $this->common_model->select_where("*" , 'garden_levels', array('status' => 'active'))->result_array();
		$response = [
			'status' => 200,
			'message' => "active levels",
			'result' => $garden_levels,
		];
		$this->set_response($response, REST_Controller::HTTP_OK);
		
	}

	public function garden_seed_post(){
	
		if (isset($_POST['level']) && !empty($_POST['level'])) {
			$level  = $_POST['level'];
			$user_id  = $_POST['user_id'];

			$garden_seeds = $this->common_model->select_where("*" , 'garden_seeds', array('level'=>$level , 'status' => 'active'))->result_array();
			
			foreach ($garden_seeds as $key => $value) {
				$seed_exists = $this->common_model->select_where("*" , 'level_history', array('user_id' => $user_id, 'level' => $level, 'seed'=> $value['id']))->num_rows();
				$garden_seeds[$key]['seed_used'] = $seed_exists == 1 ? 'yes' : 'no';
			}

			$response = [
				'status' => 200,
				'message' => "active seeds",
				'result' => $garden_seeds,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 400,
				'message' => 'empty parameters',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function level_switch_post()
	{
		if (isset($_POST['user_id']) && isset($_POST['level']) && isset($_POST['seed']) && $_POST['level'] != 0 &&  $_POST['seed'] != 0) {
				
			$user_id = $_POST['user_id'];
			$level = $_POST['level'];
			$seed = $_POST['seed'];

			$current_garden = $this->common_model->join_two_tab_where_simple(
				"users.level, users.seed, seeds.count", 'users',
				'garden_seeds seeds', 'seeds.id = users.seed',
				array('users.id' => $user_id)
			)->row_array();

			$current_level = $current_garden['level'];
			$current_seed = $current_garden['seed'];
			$current_max_count = $current_garden['count'];

			$count = $this->common_model->select_where('*', 'level_history', array('user_id' => $user_id, 'level' => $current_level, 'seed' => $current_seed, 'score >=' => $current_max_count))->num_rows();

			if ($count == 1) {
				$exists = $this->common_model->select_where('*', 'level_history', array('user_id' => $user_id, 'level' => $level, 'seed' => $seed))->num_rows();

				if ($exists == 0) {
					$insert['user_id'] = $user_id;
					$insert['level'] = $level;
					$insert['seed'] = $seed;
					$insert['score'] = '0';
					$insert['status'] = 'active';
					$insert['start_date'] = date('Y-m-d');
					$result = $this->common_model->insert_array('level_history', $insert);

					if ($result) {
						$this->common_model->update_array(array('user_id'=> $user_id, 'level' => $current_level, 'seed' => $current_seed), 'level_history', array('end_date' => date('Y-m-d'), 'status' => 'complete'));
						$this->common_model->update_array(array('id'=> $user_id), 'users', array('level' => $level , 'seed' => $seed));
						
						$response = [
							'status' => 200,
							'message' => 'Level switched successfully',
							'new_level' => $insert
						];
						$this->set_response($response, REST_Controller::HTTP_OK);
					}
				} else {
					$response = [
						'status' => 400,
						'message' => 'Level already exists',
					];
					$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				}
			} else {
				$response = [
					'status' => 400,
					'message' => 'Current level incomplete',
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Empty parameters',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
}
