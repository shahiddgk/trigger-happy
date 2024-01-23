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
		$this->load->library('firestore');
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
		$result = $this->common_model->select_where("*", "users", array('email'=>$email))->result_array();
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
				$insert_id = $this->db->insert_id();
			if($result){
				
				$invitations = $this->common_model->select_where("*", "sage_invitations", array('approver_email' => $email))->result_array();
				if (!empty($invitations)) {
					foreach ($invitations as $invitation) {
						$connectionData = [
							'requester_id' => $invitation['requester_id'],
							'approver_id' => $insert_id,
							'role' => $invitation['approver_role'],
						];
						$this->common_model->insert_array('connection', $connectionData);
					}
				}
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
	public function update_profile_post() {
		$user_id = $_POST['user_id'];
		$result = $this->common_model->select_where("*", "users", array('id' => $user_id))->result_array();
	
		if ($result) {
			$update = array();
	
			if (!empty($_POST['name'])) {
				$update['name'] = $_POST['name'];
			}
	
			if (!empty($_POST['email'])) {
				$update['email'] = $_POST['email'];
			}
	
			if (!empty($_POST['time_zone'])) {
				$update['time_zone'] = $_POST['time_zone'];
			}
	
			if (!empty($_POST['device_token'])) {
				$update['device_token'] = $_POST['device_token'];
			}
	
			if (isset($_FILES['profile_img']) && !empty($_FILES['profile_img'])) {
				$temp = $_FILES['profile_img']['tmp_name'];
				$image_filename = $user_id . '.png';
				$path = './uploads/app_users/' . $image_filename;
	
				if (isset($result[0]['image'])) {
					$old_image_path = './uploads/app_users/' . $result[0]['image'];
	
					if ($result[0]['image'] != 'default.png' && file_exists($old_image_path) && is_file($old_image_path)) {
						unlink($old_image_path);
					}
				}
	
				move_uploaded_file($temp, $path);
				$update['image'] = $image_filename;
				$img_url = base_url() . 'uploads/app_users/' . $image_filename;
			}
	
			if (empty($update) || (count($update) === 1 && isset($update['image']))) {
				$update['image'] = 'default.png';
				$img_url = base_url() . 'uploads/app_users/default.png';
			}
	
			if (!empty($update)) {
				$this->common_model->update_array(array('id' => $user_id), 'users', $update);
			}
	
			$img_url = isset($img_url) ? $img_url : base_url() . 'uploads/app_users/default.png';
	
			$response = [
				'status' => 200,
				'image_url' => $img_url,
				'message' => 'Profile is updated successfully.'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 200,
				'message' => 'User details are not found.'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}
	
   	public function login_post(){

		$email	=	$_POST['email'];
		$password	=	$_POST['password'];
			
		$data['login'] = $this->common_model->select_where("*","users", array('email'=>$email,'password'=>sha1($password), 'status' => 'active'));
		
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
			$defaultImagePath = base_url('uploads/app_users/default.png');
			$user_data = array(
				'user_logged_in'  =>  TRUE,
				'usertype' => $row['type'],
				'userid' => $row['id'],
				'username' => $row['name'],
				'useremail' => $row['email'],
				'image_url' => isset($row['image']) ? base_url('uploads/app_users/') . $row['image'] : $defaultImagePath,
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
				'message' => 'Your password or email is wrong.',
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
				'message' => 'Data is not found.',
				'questions' => array()
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} 
	}

	public function questions_post(){
		if($this->input->post('type')){
			$type = $this->input->post('type');
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
				'message' => 'Data is not found.',
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
			$result = $this->common_model->select_where("*", "users", array('email'=>$email));
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
							'response_id' => $response_id,
							'response_id' => $response_id,
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
						'response_id' => $response_id,
						'response_id' => $response_id,
						'message' => 'Data inserted'
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				}
			} else {
				$response = [
					'status' => 200,
                    'response_id' => $response_id,
                    'response_id' => $response_id,
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
	
		$data['login'] = $this->common_model->select_where("*", "users", array('id' => $user_id));
	
		if ($data['login']->num_rows() > 0) {
			$user_data = $data['login']->row_array();
	
			$row_count = $this->common_model->select_where_groupby("*", "answers", array('user_id' => $user_id, 'type' => 'naq'), "response_id")->num_rows();
	
			if ($user_data['is_premium'] == 'no' && $row_count >= 2) {
				$response = [
					'status' => 400,
					'message' => 'More responses not allowed.'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				return;
			}
	
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
						'response_id' => $response_id,
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
								'response_id' => $response_id,
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
							'response_id' => $response_id,
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
		else{
			$response = [
				'status' => 400,
				'message' => 'User Not Found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	// pire positive submit
	public function response_submit_pire_positive_post() {
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
					'type' => 'pire_pos'
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
	
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'pire_pos', 'response_date' => date('Y-m-d')));
			if ($count == 0) {
				$score_data = array(
					'type' => 'pire_pos',
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

	public function forgot_password_post()
	{
		$email = $_POST['email'];
		$response = $this->common_model->select_where("id", "users", array('email'=>$email)); 
	
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
public function delete_user_get(){
    $user_id = $_GET['user_id'];

    if (!empty($user_id)) {
        // Update the status column to 'inactive'
        $data = array('status' => 'inactive');
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);

        if ($this->db->affected_rows() > 0) {
            $response = [
                'status' => 200,
                'message' => 'Your account has been deleted successfully.'
            ];
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $response = [
                'status' => 400,
                'message' => 'User details are not found.'
            ];
            $this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
        }
    } else {
        $response = [
            'status' => 400,
            'message' => 'Empty parameters.'
        ];
        $this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
    }
}

	public function delete_user_post(){
		$user_id = $_POST['user_id'];
		if(!empty($user_id)){
			$this->db->delete('users', array('id'=>$user_id));
			if($this->db->affected_rows()> 0){
				$response = [
					'status' => 200,
					'message' => 'User is deleted successfully.'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 400,
					'message' => 'User details are not found.'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		}else{
			$response = [
				'status' => 400,
				'message' => 'Empty parameters.'
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

				// To qualify for the next level
				// if (($user_id == '166' && $level == '1') || ($user_id == '182' && in_array($level, ['1', '2']))) {
				// 	$score = $max_count;
				// }
	
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

		$current_garden = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
		$level = $current_garden['level'];
		$seed = $current_garden['seed'];
		$response_id = random_string('numeric', 8);

		$data['response_id'] = $response_id;
		$data['level'] = $level;
		$data['seed'] = $seed;
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

			$trellis_entry = $this->common_model->select_where("*", "trellis", array('user_id' => $user_id))->row_array();
	
			$this->common_model->update_array(array('user_id' => $user_id), 'trellis', $data);
	
			$history_data = array(
				'user_id' => $trellis_entry['user_id'],
				'response_id' => $trellis_entry['response_id'],
				'name' => $trellis_entry['name'],
				'name_desc' => $trellis_entry['name_desc'],
				'purpose' => $trellis_entry['purpose'],
			);
			$this->common_model->insert_array('trellis_history', $history_data);
	
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'trellis', 'response_date' => date('Y-m-d')));
			if ($count < 1) {
				$score_data = array(
					'type' => 'trellis',
					'user_id' => $user_id,
					'response_id' => $trellis_entry['response_id'],
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d')
				);
				$this->common_model->insert_array('scores', $score_data);
			}
	
			$response = [
				'status' => 200,
				'message' => 'data updated'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$data['user_id'] = $user_id;
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'trellis', 'response_date' => date('Y-m-d')));
			if ($count < 1) {
				$score_data = array(
					'type' => 'trellis',
					'user_id' => $user_id,
					'response_id' => $response_id,
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d')
				);
				$this->common_model->insert_array('scores', $score_data);
			}
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
		$insert_from = $_POST['insert_from'];

		$response_id = random_string('numeric', 8);
		
		$current_garden = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
		$level = $current_garden['level'];
		$seed = $current_garden['seed'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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
				$insert['response_id'] = $response_id;
				$insert['type'] = $_POST['type'];
				$insert['level'] = $level;
		        $insert['seed'] = $seed;

				if ($insert_from == 'ladder') {
					$insert['favourite'] = 'no';
				} elseif ($insert_from == 'trellis') {
					$insert['favourite'] = 'yes';
				}				 
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
				
				$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'ladder', 'response_date' => date('Y-m-d')));
				if ($count < 1) {
					$score_data = array(
						'type' => 'ladder',
						'user_id' => $user_id,
						'response_id' => $response_id,
						'level' => $level,
						'seed' => $seed,
						'response_date' => date('Y-m-d')
					);
					$this->common_model->insert_array('scores', $score_data);
				}
				$_POST['id'] = $last_insert_id;
				$_POST['insert_from'] = $insert_from;
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
				'message' => 'User details are not found.'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		} 
	}


	public function ladder_update_post() {
		$id = $_POST['user_id']; 
		$response_id = random_string('numeric', 8);

		$ladder_entry = $this->common_model->select_where("*", "ladder", array('id' => $id))->row_array();
		
		if ($ladder_entry) {
			
			$update['response_id'] = $response_id;
			$update['type'] = $_POST['type'];
			$update['updated_at'] = date('Y-m-d H:i:s');
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
			
			$this->common_model->update_array(array('id' => $id), 'ladder', $update);
			
			$history_data = array(
				'user_id' => $ladder_entry['user_id'],
				'response_id' => $ladder_entry['response_id'],
				'type' => $ladder_entry['type'],
				'option1' => $ladder_entry['option1'],
				'option2' => $ladder_entry['option2'],
				'date' => $ladder_entry['date'],
				'text' => $ladder_entry['text'],
				'description' => $ladder_entry['description']
			);
			$this->common_model->insert_array('ladder_history', $history_data);
			
			$user_id = $ladder_entry['user_id'];
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'ladder', 'response_date' => date('Y-m-d')));
	
			if ($count < 1) {
				$current_garden = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
				$level = $current_garden['level'];
				$seed = $current_garden['seed'];
				$score_data = array(
					'type' => 'ladder',
					'user_id' => $user_id,
					'response_id' => $ladder_entry['response_id'],
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d')
				);
				$this->common_model->insert_array('scores', $score_data);
			}
			
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

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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

	public function tribe_update_post() {
		$id = $_POST['id'];

		$tribe = $this->common_model->select_where("*", "tribe_new", array('id' => $id))->row_array();

		if ($tribe) {
			if(isset($_POST['type']) && !empty($_POST['type'])){
				$update['type'] = $_POST['type'];
			}
			if(isset($_POST['text']) && !empty($_POST['text'])){
				$update['text'] = $_POST['text'];
			}
			$this->common_model->update_array(array('id' => $id), 'tribe_new', $update);
			$response = [
				'status' => 200,
				'message' => 'success',
				'updated_data' => $update
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else {
			$response = [
				'status' => 400,
				'message' => 'no tribe entry found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function principles_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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

	public function principles_update_post(){
		$id = $_POST['id'];
		$principle = $this->common_model->select_where("*", "principles", array('id' => $id))->row_array();
		if($principle){
			$update = array();
			if(isset($_POST['type'])){
				$update['type'] = $_POST['type'];
			}
			if(isset($_POST['emp_truths'])){
				$update['emp_truths'] = $_POST['emp_truths'];
			}
			if(isset($_POST['powerless_believes'])){
				$update['powerless_believes'] = $_POST['powerless_believes'];
			}
			$this->common_model->update_array(array('id' => $id), 'principles', $update);
			$response = [
				'status' => 200,
				'message' => 'success',
				'updated_data' => $update
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no principle entry found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function identity_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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

	public function identity_update_post(){
		$id = $_POST['id'];
		$identity = $this->common_model->select_where("*", "identity", array('id' => $id))->row_array();
		if($identity){
			$update = array();
			if(isset($_POST['type'])){
				$update['type'] = $_POST['type'];
			}
			if(isset($_POST['text'])){
				$update['text'] = $_POST['text'];
			}
			$this->common_model->update_array(array('id' => $id), 'identity', $update);
			$response = [
				'status' => 200,
				'message' => 'success',
				'updated_data' => $update
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
			$response = [
				'status' => 400,
				'message' => 'no identity entry found'
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
					'message' => 'Deleted successfully'
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			}else{
				$response = [
					'status' => 200,
					'message' => 'Data is
					 not deleted'
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
		$response_id = random_string('numeric', 8);

		if(isset($_POST['user_id']) && !empty($_POST['user_id'])){

			$current_garden = $this->common_model->select_where("level, seed","users", array('id'=>$_POST['user_id']))->row_array();
			$level = $current_garden['level'];
			$seed = $current_garden['seed'];

			$data['user_id'] = $_POST['user_id'];
			$data['response_id'] = $response_id;
			$data['level'] = $level;
			$data['seed'] = $seed;
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
					$insert['response_id'] = $response_id;
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

	public function session_update_post() {
		$id = $_POST['id']; 
		$response_id = random_string('numeric', 8);

		$session_entry = $this->common_model->select_where("*", "session_entry", array('id' => $id))->row_array();
		
		if ($session_entry) {
			
			$update['response_id'] = $response_id;
			$update['defined_by'] = 'user';
			if (isset($_POST['entry_date']) && !empty($_POST['entry_date'])) {
				$dateObj = DateTime::createFromFormat('m-d-y', $_POST['entry_date']);
				if ($dateObj !== false) {
					$update['entry_date'] = $dateObj->format('Y-m-d');
				}
			}
			if (isset($_POST['entry_type']) && !empty($_POST['entry_type'])) {
				$update['entry_type'] = $_POST['entry_type'];
			}
			if (isset($_POST['entry_title']) && !empty($_POST['entry_title'])) {
				$update['entry_title'] = $_POST['entry_title'];
			}
			if (isset($_POST['entry_decs']) && !empty($_POST['entry_decs'])) {
				$update['entry_decs'] = $_POST['entry_decs'];
			}
			if (isset($_POST['entry_takeaway']) && !empty($_POST['entry_takeaway'])) {
				$update['entry_takeaway'] = $_POST['entry_takeaway'];
			}
			
			$this->common_model->update_array(array('id' => $id), 'session_entry', $update);
			
			$history_data = array(
				'session_id' => $session_entry['id'],
				'user_id' => $session_entry['user_id'],
				'response_id' => $session_entry['response_id'],
				'entry_date' => $session_entry['entry_date'],
				'entry_type' => $session_entry['entry_type'],
				'entry_title' => $session_entry['entry_title'],
				'entry_decs' => $session_entry['entry_decs'],
				'entry_takeaway' => $session_entry['entry_takeaway'],
				'defined_by' => $session_entry['defined_by']
			);			
			$this->common_model->insert_array('session_entry_history', $history_data);
			
			$user_id = $session_entry['user_id'];
			$count = $this->common_model->select_where_table_rows('*', 'scores', array('user_id' => $user_id, 'type' => 'column', 'response_date' => date('Y-m-d')));
	
			if ($count < 1) {
				$current_garden = $this->common_model->select_where("level, seed", "users", array('id' => $user_id))->row_array();
				$level = $current_garden['level'];
				$seed = $current_garden['seed'];
				$score_data = array(
					'type' => 'column',
					'user_id' => $user_id,
					'response_id' => $session_entry['response_id'],
					'level' => $level,
					'seed' => $seed,
					'response_date' => date('Y-m-d')
				);
				$this->common_model->insert_array('scores', $score_data);
			}
			
			$update_row = $this->common_model->select_where("*", 'session_entry', array('id' => $id))->row_array();
			
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

	public function app_version_post() {
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

	public function payment_settings_post() {
		$user_id	=	$_POST['user_id'];
			
		$valid_user = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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
			
		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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
				'admin_access' => $row['admin_access'],
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
 
	public function new_tribe_insert_post(){
		$user_id = $_POST['user_id'];

		$data['login'] = $this->common_model->select_where("*","users", array('id'=>$user_id));
		
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
						'mobile_image_url' => base_url('uploads/apple_tree/apple_mobile/') . ($score + 1) . '.png',
                        'ipad_image_url' => base_url('uploads/apple_tree/apple_ipad/') . ($score + 1) . '.png',
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

			$score = $this->common_model->select_where('*', 'naq_scores', ['response_id' => $response_id])->row_array();
			$score = $score['score'];

			$response = [
				'status' => 200,
				'message' => 'success',
				'score' => $score,
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

	public function insert_reminder_post() {
		$user_id = $_POST['user_id'];
	
		$data['login'] = $this->common_model->select_where("*", "users", array('id' => $user_id));
	
		if ($data['login']->num_rows() > 0) {
			$user_data = $data['login']->row_array();
	
			$row_count = $this->common_model->select_where("*", "reminders", array('user_id' => $user_id))->num_rows();
	
			if ($user_data['is_premium'] == 'no' && $row_count >= 2) {
				$response = [
					'status' => 400,
					'message' => 'More responses not allowed.'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				return; // Exit the function if the limit is reached.
			}
	
			$formatted_date = '';
	
			if ($_POST['reminder_type'] == 'once') {
				$days_list = '[]';
			} else {
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
	
			if (isset($_POST['date'])) {
				$dateObj = DateTime::createFromFormat('m-d-y', $_POST['date']);
				$formatted_date = $dateObj->format('Y-m-d');
	
				$time = $_POST['time'] . ' ' . $_POST['time_type'];
				$datetime = DateTime::createFromFormat('Y-m-d h:i A', $formatted_date . ' ' . $time);
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
					'date' => $formatted_date,
					'time' => $_POST['time'],
					'time_type' => $_POST['time_type'],
					'end_date' => $end_date,
					'status' => $_POST['status'],
					'reminder_type' => $_POST['reminder_type']
				];
	
				$response = [
					'status' => 200,
					'message' => 'Reminder successfully created',
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
	   	} else {
			$response = [
				'status' => 400,
				'message' => 'User not found'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function read_reminder_post(){
		$user_id = $_POST['user_id'];
		$result = $this->common_model->select_where("*", "reminders", array('user_id' => $user_id, 'orignal_id' => 0))->result_array();
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
        $entity_id = $_POST['entity_id'];
		$update_id = $_POST['update_id'];

		$this->common_model->update_array(array('id' => $update_id), "reminder_history", array('reminder_stop' => 'no'));
		
        $reminder = $this->common_model->select_where("*", "reminders", array('id' => $entity_id))->row_array();
		if (empty($reminder)) {
			$response = [
				'status' => 404,
				'message' => 'Reminder not found.'
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		
		$user_id = $reminder['user_id'];
		$userTt = $this->common_model->select_where("time_zone", "users", array('id' => $user_id))->row_array();
		$userTimeZone = $userTimeZone = $userTt['time_zone'];

		$firstIndex = $userTimeZone;
		$secondIndex = $this->getTimeZoneSecondIndex($firstIndex);
		
		date_default_timezone_set($secondIndex);
		 
		$currentTime = new DateTime('now');
		$roundedTime = ceil($currentTime->format('i') / 15) * 15;
		$currentTime->setTime($currentTime->format('H'), $roundedTime);
		
		$adjustedDueTimeFormatted = $currentTime->format('Y-m-d H:i:s');
		

		$snooze_reminder = [
			'user_id' => $reminder['user_id'],
			'text' => $reminder['text'],
			'day_list' => $reminder['day_list'],
			'status' => $reminder['status'],
			'reminder_stop' => $reminder['reminder_stop'],
			'reminder_type' => $reminder['reminder_type'],
			'end_date' => $reminder['end_date'],
			'orignal_id' => '1',
			'date_time' => $adjustedDueTimeFormatted
		];
		$update = $this->db->insert('reminders', $snooze_reminder);
	
        if ($update) {
            $response = [
                'status' => 200,
                'message' => 'Reminder snoozed successfully.'
            ];
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $response = [
                'status' => 400,
                'message' => 'Reminder is not snoozed.'
            ];
            $this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

	public function reminder_stop_post() {
	
		$entity_id = $_POST['entity_id'];
		$reminder_stop = $_POST['reminder_stop'];

		$this->db->where('id', $entity_id);
		$updated =  $this->db->update('reminder_history', ['reminder_stop' => $reminder_stop]);
		if($updated){
			$response = [
				'status' => 200,
				'message' => 'Reminder updated successfully.'
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'Reminder is not updated.'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	public function waitings_reminders_post() {
		if (!empty($_POST['user_id'])) {
			$user_id = $_POST['user_id'];
	
			$skipped_reminders = $this->db
				->select('reminder_history.*, reminders.text, DATE(reminder_history.created_at) as created_date')
				->join('reminders', 'reminder_history.entity_id = reminders.id')
				->where(['reminder_history.user_id' => $user_id, 'reminder_history.reminder_stop' => 'waiting'])
				->get('reminder_history')
				->result_array();
	
			$waiting_reminders = $this->db
				->select('snooze_reminder.*, reminders.text, DATE(snooze_reminder.created_at) as created_date')
				->join('reminders', 'snooze_reminder.entity_id = reminders.id')
				->where(['snooze_reminder.user_id' => $user_id, 'snooze_reminder.snooze' => 'waiting'])
				->get('snooze_reminder')
				->result_array();
	
			// Merge the two arrays
			$all_reminders = array_merge($skipped_reminders, $waiting_reminders);
	
			foreach ($all_reminders as &$reminder) {
				$due_time = $reminder['due_time'];
				$created_date = $reminder['created_date'];
				$reminder['date_time'] = $created_date . ' ' . $due_time;
			}
	
			$response = [
				'status' => REST_Controller::HTTP_OK,
				'result' => $all_reminders
			];
	
			if (!empty($all_reminders)) {
				$response['message'] = 'Due reminders found';
			} else {
				$response['message'] = 'No due reminders found';
			}
	
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => REST_Controller::HTTP_BAD_REQUEST,
				'message' => 'Empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	public function garden_levels_get(){
	
		$garden_levels = $this->common_model->select_where("*" , 'garden_levels', array('status' => 'active'))->result_array();
		$response = [
			'status' => 200,
			'message' => "active levels",
			'result' => $garden_levels,
		];
		$this->set_response($response, REST_Controller::HTTP_OK);
		
	}

	public function new_garden_levels_post(){
	
		$garden_levels = $this->common_model->select_where("*" , 'garden_levels', array('status' => 'active'))->result_array();
		$response = [
			'status' => 200,
			'message' => "Active levels",
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
				'message' => 'Empty parameters',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function level_switch_post(){
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

    public function user_activity_post(){
	
		$user_id = $this->input->post('user_id');

		if (isset($user_id) && !empty($user_id)) {
			$user_activity = $this->common_model->api_user_activity_report($user_id);
			$response = [
				'status' => 200,
				'message' => 'Success',
				'user_id' => $user_id,
				'name' => $user_activity['name'],
                'date' => $user_activity['created_at'],
				'min_naq_response' => $user_activity['naq_score']->min_naq_response,
				'max_naq_response' => $user_activity['naq_score']->max_naq_response,
				'level' => $user_activity['level'],
				'delta' => $user_activity['naq_score']->delta,
				'count_pire' => $user_activity['count_pire'],
				'count_trellis' => $user_activity['count_trellis'],
				'count_column' => $user_activity['count_column'],
				'count_ladder' => $user_activity['count_ladder'],
				'total_count' => $user_activity['total_count'],
				'sum_active_reminders' => $user_activity['additional_data']->sum_active_reminders,
				'sum_yes_reminders' => $user_activity['additional_data']->sum_yes_reminders,
				'sum_reminders' => $user_activity['additional_data']->sum_reminders
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 400,
				'message' => 'Empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}	
    }

	public function level_history_post() {
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
						'mobile_image_url' => base_url('uploads/apple_tree/apple_mobile/') . ($score + 1) . '.png',
                        'ipad_image_url' => base_url('uploads/apple_tree/apple_ipad/') . ($score + 1) . '.png',
						'trellis_count' => array(),
						'ladder_count' => array(),
						'column_count' => array(),
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
						$sorted_array[$date_index]['column_count'] = $this->common_model->select_where("response_id", "session_entry_history", array('user_id'=>$value['user_id'], 'response_id != '=>'0', 'DATE(created_at)'=>$date_index) )->result_array();
					} elseif ($value['type'] == 'trellis') {
						$sorted_array[$date_index]['trellis_count'] = $this->common_model->select_where("response_id", "trellis_history", array('user_id'=>$value['user_id'], 'response_id != '=>'0', 'DATE(created_at)'=>$date_index))->result_array();
					} elseif ($value['type'] == 'ladder') {
						$sorted_array[$date_index]['ladder_count'] = $this->common_model->select_where("response_id", "ladder_history", array('user_id'=>$value['user_id'], 'response_id != '=>'0', 'DATE(created_at)'=>$date_index))->result_array();
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
				'message' => 'Empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function level_history_details_post(){
		$response_id = $_POST['response_id'];
		$table_name = $_POST['table_name'];

		if($table_name == 'ladder'){
			$table_name = 'ladder_history' ;
		}elseif($table_name == 'trellis'){
			$table_name = 'trellis_history' ;
		}elseif($table_name == 'column') {
			$table_name = 'session_entry_history' ;
		}

         
		if(!empty($response_id)){
			$result = $this->common_model->select_where("*", $table_name, array('response_id' => $response_id))->row_array();

			$response = [
				'status' => 200,
				'message' => 'Success',
				'data' => $result
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}else{
			$response = [
				'status' => 400,
				'message' => 'Empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function search_user_post() {
		$name = isset($_POST['name']) ? $_POST['name'] : '';
		$requester_id = $_POST['requester_id'];

		
		if (empty($name)) {
			$condition = "type = 'user' OR type = 'admin'";
			$results = $this->common_model->select_where("id, name, email, time_zone, image", 'users', $condition)->result_array();
		} else {
			$where_condition = "name LIKE '%" . $name . "%' AND (type = 'user' OR type = 'admin')";
			$results = $this->common_model->select_where("id, name, email, time_zone, image", 'users', $where_condition)->result_array();
		}
	
		if (!empty($results)) {

			$response_data = [];
			$defaultImagePath = base_url('uploads/app_users/default.png');
			foreach ($results as $user) {
				$user['image_url'] = isset($user['image']) ? base_url('uploads/app_users/') . $user['image'] : $defaultImagePath;
		
				$connection_query = $this->common_model->select_where('*', 'connection', ['requester_id' => $requester_id, 'approver_id' => $user['id']]);
		
				if ($connection_query->num_rows() > 0) {
					$connection_data = $connection_query->row_array();
		
					$user['connection_exist'] = 'yes';
		
					if ($connection_data['accept'] == 'yes') {
						$user['connection_status'] = 'approved';
					} else {
						$user['connection_status'] = 'pending';
					}
				} else {
					$user['connection_exist'] = 'no';
					$user['connection_status'] = 'not applicable';
				}
		
				$response_data[] = $user;
			}			
			$response = [
				'status' => 200,
				'message' => 'success',
				'users_data' => $response_data,
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
	
	public function connection_request_post() {
		
		$requester_id = $_POST['requester_id'];
		$approver_email = $_POST['approver_email'];
		$approver_role = $_POST['role']; 
		$requester = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
		
		if (empty($requester)) {
			$response = [
				'status' => 404,
				'message' => 'Sender not found.',
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
			return;
		}
	
		$approver = $this->common_model->select_where('*', 'users', ['email' => $approver_email])->row_array();
		
		if (empty($approver)) {
			$url = base_url();
			$message = "<p>Hi " . $approver_email . ",</p>";
			
			$message .= "<p>" . $requester['name'] . " has sent you the invitation for " . $approver_role . "</p>";
		
			$message .= "<p>If you are interested, please click on the link below</p>";
			$message .= "<p><a href='" . $url . "'>Invitation Link</a></p>";
			
			$this->email->set_newline("\r\n");
			$this->email->set_mailtype('html');
			$this->email->from($this->smtp_user, 'Burgeon');
			$this->email->to($approver_email);
			$this->email->subject('Burgeon Invitation');
			$this->email->message($message);

			if ($this->email->send()) {	
				$data_array = [
					'requester_id' => $requester_id,
					'approver_email' => $approver_email,
					'approver_role' => $approver_role,
				];
				$this->common_model->insert_array('sage_invitations', $data_array);

				$response = [
					'status' => 200,
					'message' => 'Invitation email sent successfully',
					'data' => [],
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 500,
					'message' => 'Failed to send invitation email',
					'data' => [],
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
			return;
		}

		$existing_notification = $this->common_model->select_where('*', 'connection', [
			'approver_id' => $approver['id'],
			'requester_id' => $requester_id,
			'role' => $approver_role,
		])->row_array();

		if(empty($existing_notification)) {
		
			$notification_data = [
				'requester_id' => $requester_id,
				'approver_id' => $approver['id'],
				'role' => $approver_role,
				'message' => $requester['name'] . ' has sent you the invitation for ' . $approver_role,
			];
		
			$this->common_model->insert_array('connection', $notification_data);
			$get_id = $this->common_model->select_where('id', 'connection', [
				'requester_id' => $requester_id,
				'approver_id' => $approver['id'],
				'role' => $approver_role,
				])->row_array(); 
				
				
				$this->firestore->addData($approver['id'] , 'con_request');
				$data_array = [
					'approver_name' => $approver['name'],
					'receiver_role' => $approver_role,
					'requester_name' => $requester['name'],
					'approver_id' => $approver['id'],
					'requester_id' => $requester_id
				];
				
				$data_json = json_encode($data_array);

				$data_encoded = urlencode($data_json);
				$url = base_url() . "welcome/invitation_email?data=" . $data_encoded;
				
				$message = "<p>Hi " . $approver['name'] . ",</p>"; 
				$message .= "<p>" . $requester['name'] . " has sent you the invitation for " . $approver_role . "</p>";
				$message .= "<p>Click the link below to accept or reject the invitation</p>";
				$message .= "<p><a href='" . $url . "'>Invitation Link</a></p>";
				
				$this->email->set_newline("\r\n");
				$this->email->set_mailtype('html');
				$this->email->from($this->smtp_user, 'Burgeon');
				$this->email->to($approver_email);
				$this->email->subject('Burgeon Invitation');
				$this->email->message($message);
				if($this->email->send()){
					if (isset($_POST['module'])) {
					$modules = explode(',', $_POST['module']);
					foreach ($modules as $module) {
						$module_request = [
							'requester_id' => $requester_id,
							'approver_id'  => $approver['id'],
							'connection_id' => $get_id['id'],
							'module' => trim($module),
							'permission' => 'accept'
						];
					
						
						// $this->common_model->insert_array('shared_module', $shared_module);
						$this->common_model->insert_array('module_requested', $module_request);
					}
				}

				$requested_module = $this->common_model->select_where('*', 'module_requested', ['connection_id' => $get_id['id']])->result_array();
				// print_r($requested_module);exit;
				$module_values = array_column($requested_module, 'module');
				$defaultImagePath = base_url('uploads/app_users/default.png');
				$connection_data = [
					'connectionid' => $approver['id'],
					'requester_id' => $requester_id,
					'accept' => 'no',
					'approver_id' => $approver['id'],
					'role' => $approver_role,
					'sharing_modules' => implode(',', $module_values),
					'accepted_modules' => "",
					'message' => $requester['name'] . ' has sent you the invitation for ' . $approver_role,
					'requester_name' => $requester['name'],
					'approver_name' => $approver['name'],

				];
				
				$first_user_detail = [
					'id' => $requester['id'],
					'name' => $requester['name'],
					'email' => $requester['email'],
					'image' => isset($requester['image']) ? base_url('uploads/app_users/') . $requester['image'] : $defaultImagePath,
				];
				$second_user_detail = [
					'id' => $approver['id'],
					'name' => $approver['name'],
					'email' => $approver['email'],
					'image' => isset($approver['image']) ? base_url('uploads/app_users/') . $approver['image'] : $defaultImagePath,
				];
				$response = [
					'status' => 200,
					'data' => [
						'connection_info' => $connection_data,
						'first_user_detail' => $first_user_detail,
						'second_user_detail' => $second_user_detail,
					],
				];
			}
		 	else {
				$response = [
					'status' => 500,
					'message' => 'Failed to send invitation email',
				];
			}
			
		} else {
			$response = [
				'status' => 400,
				'message' => 'You have already sent an invitation',

			];
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function accept_invite_get() {
		$approver_id = $this->input->get('approver_id');
		$requester_id = $this->input->get('requester_id');
		$approver_role = $this->input->get('approver_role');
		
		$update_data = ['accept' => 'yes'];
	
		$conditions = [
			'approver_id' => $approver_id,
			'requester_id' => $requester_id,
		];
	
		$tribe_new = $this->common_model->select_where("*", "connection", $conditions)->row_array();
	
		if ($tribe_new) {
			$this->common_model->update_array($conditions, 'connection', $update_data);
			redirect('welcome/thank_you');
		} else {
			return 'error';
		}
	}
	
	public function reject_invite_get() {
		$requester_id = $_GET['requester_id'];
		$approver_id = $_GET['approver_id'];

		$this->db->where(['requester_id' => $requester_id, 'approver_id' => $approver_id])->delete('connection');

		$this->db->where(['requester_id' => $requester_id, 'approver_id' => $approver_id])->delete('connection');
		$requester = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
		$approver = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array();	

		$message = 'Hi ' . $requester['name'] . ',<br /><br />';
		$message .= $approver['name']. ' has rejected your invitation.<br />';

		$this->email->set_newline("\r\n");
		$this->email->set_mailtype('html');
		$this->email->from($this->smtp_user, 'Burgeon');
		echo $this->email->to($requester['email']);
		$this->email->subject('Burgeon Invitation Rejected');
		echo $this->email->message($message);

		if ($this->email->send()) {
			redirect('welcome/thank_you');
		} else {
			$response = [
				'status' => 500,
				'message' => 'Failed to send rejection email to sender',
			];
			$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

	}

	public function invite_notification_post() {
		$user_id = $_POST['user_id'];
		$defaultImagePath = base_url('uploads/app_users/default.png');
		$connection_data = $this->common_model->select_where('*', 'connection', ['approver_id' => $user_id])->result_array();
	
		if (empty($connection_data)) {
			$response = [
				'status' => 200,
				'data' => [],
			];
		} else {
			$response = [
				'status' => 200,
				'data' => [],
			];
	
			foreach ($connection_data as $connection) {
				$requester_id = $connection['requester_id'];
	
				$user_data = $this->common_model->select_where('*', 'users', ['id' => $user_id])->row_array();
				$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
	
				$connection_info = [
					'id' => $connection['id'],
					'requester_id' => $requester_id,
					'approver_id' => $connection['approver_id'],
					'role' => $connection['role'],
					'message' => $connection['message'],
					'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
					'requester_name' => $requester_data['name'],
					'approver_name' => $user_data['name'],
				];
	
				$response['data'][] = $connection_info;
			}
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function pending_connection_post() {
		$user_id = $_POST['user_id'];
		$defaultImagePath = base_url('uploads/app_users/default.png');
		$this->firestore->resetCount($user_id , 'con_request');

		$condition = [
			'approver_id' => $user_id,
			'accept' => 'no',
		];
	
		$connection_data = $this->common_model->select_where('*', 'connection', $condition)->result_array();
        	
		if (empty($connection_data)) {
			$response = [
				'status' => 200,
                'data' => [], 
			];
		} else {
			$response = [
				'status' => 200,
				'data' => [],
			];
	
			foreach ($connection_data as $connection) {
				$requester_id = $connection['requester_id'];
				$connection_id = $connection['id'];
	
				$user_data = $this->common_model->select_where('*', 'users', ['id' => $user_id])->row_array();
				$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
				$module = $this->common_model->select_where('*', 'shared_module', ['connection_id' => $connection_id])->result_array();
				$sharing_modules = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id,'requester_id' => $user_id])->result_array();
				$accepted_modules = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id, 'permission' => 'accept', 'approver_id' => $user_id])->result_array();
				$role = ($requester_id == $user_id) ? $connection['role'] : ($connection['role'] === 'mentor' ? 'mentee' : ($connection['role'] === 'mentee' ? 'mentor' : 'peer'));

				// $module_values = array_column($module, 'module');
				$module_values_sharing = array_column($sharing_modules, 'module');
				$module_values_accepted = array_column($accepted_modules, 'module');

	
				$connection_info = [
					'id' => $connection['id'],
					'accept' => $connection['accept'],
					'requester_id' => $requester_id,
					'approver_id' => $connection['approver_id'],
					'role' => $role,
					'sharing_modules' => implode(',', array_filter($module_values_sharing)),
					'accepted_modules' => implode(',', array_filter($module_values_accepted)),
					'message' => $connection['message'],
					'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
					'requester_name' => $requester_data['name'],
					'approver_name' => $user_data['name'],
				];

				$first_user_detail = [
					'id' => $user_data['id'],
					'name' => $user_data['name'],
					'email' => $user_data['email'],
					'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
				];

				$second_user_detail = [
					'id' => $requester_data['id'],
					'name' => $requester_data['name'],
					'email' => $requester_data['email'],
					'image' => isset($requester_data['image']) ? base_url('uploads/app_users/') . $requester_data['image'] : $defaultImagePath,
				];
	
				$combined_data = [
					'connection_info' => $connection_info,
					'first_user_detail' => $first_user_detail,
					'second_user_detail' => $second_user_detail,
				];
				
				$response['data'][] = $combined_data;
			}
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);

	}

	public function pending_connection_count_post() {
		$user_id = $_POST['user_id'];
	
		$condition = [
			'approver_id' => $user_id,
			'accept' => 'no',
		];
	
		$connection_data = $this->common_model->select_where('*', 'connection', $condition)->result_array();
	
		$pending_count = count($connection_data);
	
		$response = [
			'status' => 200,
			'data' => [
				'pending_count' => $pending_count,
			],
		];
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function accept_connection_post() {
   	 	$user_id = $_POST['user_id'];
		$defaultImagePath = base_url('uploads/app_users/default.png');

		$condition = "accept = 'yes' AND (approver_id = $user_id OR requester_id = $user_id) AND role IN ('peer', 'mentor', 'mentee')";
		$connection_data = $this->common_model->select_where('*', 'connection', $condition)->result_array();
		
		if (empty($connection_data)) {
			$response = [
				'status' => 200,
				'data' => [], 
			];
			} else {
			$response = [
				'status' => 200,
				'data' => [],
			];

			foreach ($connection_data as $connection) {
				$requester_id = $connection['requester_id'];
				$approver_id = $connection['approver_id'];
				$connection_id = $connection['id'];
				$user_data = $this->common_model->select_where('*', 'users', ['id' => $user_id])->row_array();
				$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
				$approver_data = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array();
				$sharing_modules = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id,'permission' => 'accept','requester_id' => $user_id])->result_array();
				$accepted_modules = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id, 'permission' => 'accept', 'approver_id' => $user_id])->result_array();
				$role = ($requester_id == $user_id) ? $connection['role'] : ($connection['role'] === 'mentor' ? 'mentee' : ($connection['role'] === 'mentee' ? 'mentor' : 'peer'));
				
				$module_values_sharing = array_column($sharing_modules, 'module');
				$module_values_accepted = array_column($accepted_modules, 'module');


				$connection_info = [
					'id' => $connection['id'],
					'accept' => $connection['accept'],
					'requester_id' => $requester_id,
					'approver_id' => $approver_id,
					'role' => $role,
					'sharing_modules' => implode(',', array_filter($module_values_sharing)),
					'accepted_modules' => implode(',', array_filter($module_values_accepted)),
					'message' => $connection['message'],
					'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
					'requester_name' => $requester_data['name'],
					'approver_name' => $approver_data['name'],
				];

				$first_user_detail = [
					'id' => $requester_data['id'],
					'name' => $requester_data['name'],
					'email' => $requester_data['email'],
					'role' => $connection['role'],
					'image' => isset($requester_data['image']) ? base_url('uploads/app_users/') . $requester_data['image'] : $defaultImagePath,
				];

				$second_user_detail = [
					'id' => $approver_data['id'],
					'name' => $approver_data['name'],
					'email' => $approver_data['email'],
					'role' => $connection['role'] === 'peer' ? 'peer' : ($connection['role'] === 'mentor' ? 'mentee' : 'mentor'),
					'image' => isset($approver_data['image']) ? base_url('uploads/app_users/') . $approver_data['image'] : $defaultImagePath,
				];
			
				$combined_data = [
					'connection_info' => $connection_info,
					'first_user_detail' => $first_user_detail,
					'second_user_detail' => $second_user_detail,
				];


				$response['data'][] = $combined_data;
			}
		}

		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function send_connection_post() {
		$user_id = $_POST['user_id'];
		$defaultImagePath = base_url('uploads/app_users/default.png');
	
		$condition = [
			'requester_id' => $user_id,
			'accept' => 'no',
		];
	
		$connection_data = $this->common_model->select_where('*', 'connection', $condition)->result_array();
	
		if (empty($connection_data)) {
			$response = [
				'status' => 200,
				'data' => [],
			];
		} else {
			$response = [
				'status' => 200,
				'data' => [],
			];
	
			foreach ($connection_data as $connection) {
				$approver_id = $connection['approver_id'];
				$connection_id = $connection['id'];
	
				$user_data = $this->common_model->select_where('*', 'users', ['id' => $user_id])->row_array();

				$approver_data = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array();

				$sharing_modules = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id,'requester_id' => $user_id])->result_array();

				$accepted_modules = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id, 'permission' => 'accept', 'approver_id' => $user_id])->result_array();

				$module_values_sharing = array_column($sharing_modules, 'module');
				$module_values_accepted = array_column($accepted_modules, 'module');

				$connection_info = [
					'id' => $connection['id'],
					'accept' => $connection['accept'],
					'requester_id' => $user_id,
					'approver_id' => $connection['approver_id'],
					'role' => $connection['role'],
					'sharing_modules' => implode(',', array_filter($module_values_sharing)),
					'accepted_modules' => implode(',', array_filter($module_values_accepted)),
					'message' => $connection['message'],
					'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
					'requester_name' => $user_data['name'],
					'approver_name' => $approver_data['name'],
				];
	
				$first_user_detail = [
					'id' => $user_data['id'],
					'name' => $user_data['name'],
					'email' => $user_data['email'],
					'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
				];
	
				$second_user_detail = [
					'id' => $approver_data['id'],
					'name' => $approver_data['name'],
					'email' => $approver_data['email'],
					'image' => isset($approver_data['image']) ? base_url('uploads/app_users/') . $approver_data['image'] : $defaultImagePath,
				];
			
				$combined_data = [
					'connection_info' => $connection_info,
					'first_user_detail' => $first_user_detail,
					'second_user_detail' => $second_user_detail,
				];
	
				$response['data'][] = $combined_data;
			}
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}
	
	public function chat_connection_post() {
		$user_id = $_POST['user_id'];
	
		$condition = [
			'approver_id' => $user_id,
			'accept' => 'yes',
		];
	
		$connection_data = $this->common_model->select_where('*', 'connection', $condition)->result_array();
        	
		if (empty($connection_data)) {
			$response = [
				'status' => 200,
                'data' => [], 
			];
		} else {
			$response = [
				'status' => 200,
				'data' => [],
			];
	
			foreach ($connection_data as $connection) {
				$requester_id = $connection['requester_id'];
	
				$user_data = $this->common_model->select_where('*', 'users', ['id' => $user_id])->row_array();
				$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
	
				$connection_info = [
					'id' => $connection['id'],
					'accept' => $connection['accept'],
					'requester_id' => $requester_id,
					'approver_id' => $connection['approver_id'],
					'role' => $connection['role'],
					'message' => $connection['message'],
					'image' => base_url()."uploads/app_users/" . $user_data['image'],
					'requester_name' => $requester_data['name'],
					'approver_name' => $user_data['name'],
				];

				$first_user_detail = [
					'id' => $user_data['id'],
					'name' => $user_data['name'],
					'email' => $user_data['email'],
					'image' => base_url()."uploads/app_users/" . $user_data['image'],
				];

				$second_user_detail = [
					'id' => $requester_data['id'],
					'name' => $requester_data['name'],
					'email' => $requester_data['email'],
					'image' => base_url()."uploads/app_users/" . $requester_data['image'],
				];
	
				$combined_data = [
					'connection_info' => $connection_info,
					'first_user_detail' => $first_user_detail,
					'second_user_detail' => $second_user_detail,
				];
				
				$response['data'][] = $combined_data;
			}
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);

	}
	
	public function chat_connection_delete_post() {
		$requester_id = $_POST['requester_id'];
		$approver_id = $_POST['approver_id'];

		$delete_co = $this->db-> where(['requester_id' => $requester_id, 'approver_id' => $approver_id])->delete('connection');

		if ($delete_co) {
			$response = [
				'status' => 200,
				'response' => 'Connection deleted successfully',
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$error_response = [
				'status' => 400, 
				'response' => 'Data deletion failed',
			];
			$this->set_response($error_response, REST_Controller::HTTP_BAD_REQUEST);
		}

	}

	public function share_response_post() {
		$requester_id = $_POST['requester_id'];
		$type = $_POST['type'];
		$entity_id = $_POST['entity_id'];
		$connection_ids = $_POST['connection_ids']; 
		$approvers = $_POST['receivers'];
		
		$connection_ids = explode(',', $connection_ids);
		$approver_ids = explode(',', $approvers);
	
		$inserted_entries = array();
	
		if (count($connection_ids) != count($approver_ids)) {
			$error_response = [
				'status' => 400, 
				'message' => 'Invalid input data: Chat ID and Receiver ID counts do not match.',
			];
			$this->set_response($error_response, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
	
		for ($i = 0; $i < count($connection_ids); $i++) {
			$connection_id = $connection_ids[$i]; 
			$approver_id = $approver_ids[$i];
	
			$data = [
				'approver_id' => $approver_id,
				'requester_id' => $requester_id,
				'connection_id' => trim($connection_id), // trim the connection
				'type' => $type,
				'entity_id' => $entity_id,
				'paid' => 'false',
			];
	
			$insert_result = $this->common_model->insert_array('share_response', $data);
			$this->firestore->addData($approver_id , 'shared_response');
			
			if ($insert_result) {
				$inserted_entries[] = $data;
			}
		}
	
		if (!empty($inserted_entries)) {
			$response = [
				'status' => 200,
				'message' => 'success',
				'responses' => $inserted_entries,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$error_response = [
				'status' => 400, 
				'message' => 'Data insertion failed',
			];
			$this->set_response($error_response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function detail_share_response_post() {
		$type = $_POST['type'];
		$entity_id = $_POST['entity_id'];
		
		if ($type === 'column') {
			$data = $this->common_model->select_where('*', 'session_entry', ['id' => $entity_id])->row_array();
		} elseif ($type === 'trellis') {
			$data = $this->common_model->select_where('*', 'trellis', ['id' => $entity_id])->row_array();
		} elseif ($type === 'ladder') {
			$data = $this->common_model->select_where('*', 'ladder', ['id' => $entity_id])->row_array();
		} elseif ($type === 'naq' || $type === 'pire') {
			$answers = $this->common_model->select_where('id, type, options, text, question_id', 'answers', ['response_id' => $entity_id])->result_array();
			
			$questions_with_titles = array();
	
			foreach ($answers as $answer) {
				$question_id = $answer['question_id'];
				
				$question = $this->common_model->select_where('title', 'questions', ['id' => $question_id])->row_array();
				
				$questions_with_titles[] = [
					'question' => $question,
					'answer' => $answer,
				];
			}
	
			$data = $questions_with_titles;
		} else {
			// Handle the case where an unsupported 'type' is provided
			$response = [
				'status' => 400,
				'message' => 'Unsupported type',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			return; // Exit the function
		}
		
		if (!empty($data)) {
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $data
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 404,
				'message' => 'Data not found',
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function accept_invite_app_get() {
		$approver_id = $this->input->get('approver_id');
		$requester_id = $this->input->get('requester_id');
		$approver_role = $this->input->get('approver_role');
		$defaultImagePath = base_url('uploads/app_users/default.png');
		
		$update_data = ['accept' => 'yes'];
	
		$conditions = [
			'approver_id' => $approver_id,
			'requester_id' => $requester_id,
		];
	
		$tribe_new = $this->common_model->select_where("*", "connection", $conditions)->row_array();
		$user_data = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array();
		$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
	
		if ($tribe_new) {
			$this->common_model->update_array($conditions, 'connection', $update_data);
			
		   $connection_data = [
			     'id' => $tribe_new['id'],
			     'accept' => $tribe_new['accept'],
			     'requester_id' => $requester_id,
			     'approver_id' => $approver_id,
			     'role' => $approver_role,
				 'message' => $tribe_new['message'],
				'image' => isset($requester_data['image']) ? base_url('uploads/app_users/') . $requester_data['image'] : $defaultImagePath,
				 'requester_name' => $requester_data['name'],
				 'approver_name' => $user_data['name'],
		   ];
			$first_user_detail = [
				'name' => $requester_data['name'],
				'email' => $requester_data['email'],
				'image' => isset($requester_data['image']) ? base_url('uploads/app_users/') . $requester_data['image'] : $defaultImagePath,
			];
			$second_user_detail = [
				'name' => $user_data['name'],
				'email' => $user_data['email'],
				'image' => isset($user_data['image']) ? base_url('uploads/app_users/') . $user_data['image'] : $defaultImagePath,
			];
			$combined_data = [
				'status' => 200,
				'data' => [
					'connection' => $connection_data,
					'first_user_detail' => $first_user_detail,
					'second_user_detail' => $second_user_detail,
				],
			];

			$this->set_response($combined_data, REST_Controller::HTTP_OK);

		} else {
			$response = [
				'status' => 400, 
				'response' => 'You have rejected this connection',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	public function reject_invite_app_get() {
		$requester_id = $_GET['requester_id'];
		$approver_id = $_GET['approver_id'];
	
		$this->db->where(['requester_id' => $requester_id, 'approver_id' => $approver_id])->delete('connection');
		$requester = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
		$approver = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array(); 

		$message = 'Hi ' . $requester['name'] . ',<br /><br />';
		$message .= $approver['name']. ' has rejected your invitation.<br />';

		$this->email->set_newline("\r\n");
		$this->email->set_mailtype('html');
		$this->email->from($this->smtp_user, 'Burgeon');
		$this->email->to($requester['email']);
		$this->email->subject('Burgeon Invitation Rejected');
		$this->email->message($message);

		if ($this->email->send()) {
			$response = [
				'status' => 200,
				'message' => 'Invitation rejected successfully',
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 500,
				'message' => 'Failed to send rejection email to sender',
			];
			$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

	}

	public function single_naq_response_post() {
		$response_id = $_POST['response_id'];
		
		if ($response_id <= 0) {
			$response = [
				'status' => 400,
				'message' => 'empty parameters',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}

		$data = $this->db
				->select('answers.options,answers.text, answers.response_id, questions.title')
				->join('questions', 'answers.question_id = questions.id') 
				->where(['answers.response_id' => $response_id, 'answers.type' => 'naq'])
				->get('answers')
				->result_array();

		if (empty($data)) {
			$response = [
				'status' => 400,
				'message' => 'Response not found',
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		} else {
			$response = [
				'status' => 200,
				'responses' => $data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}
		 
	public function single_pire_response_post() {
		$response_id = $_POST['response_id'];
		
		if ($response_id <= 0) {
			$response = [
				'status' => 400,
				'message' => 'empty parameters',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
		
		
		$data = $this->db
				->select('answers.options,answers.text, answers.response_id, questions.title')
				->join('questions', 'answers.question_id = questions.id') 
				->where(['answers.response_id' => $response_id, 'answers.type' => 'pire'])
				->get('answers')
				->result_array();
		
		if (empty($data)) {
			$response = [
				'status' => 400,
				'message' => 'Response not found',
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		} else {
			$response = [
				'status' => 200,
				'responses' => $data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
	    }
	}

	public function all_answers_response_post() {
		$user_id = $_POST['user_id'];
		$type = $_POST['type'];

		$data = $this->common_model->select_where_groupby_join(
			'answers.type, answers.response_id, answers.created_at, naq_scores.score',
			'answers',
			['answers.user_id' => $user_id, 'answers.type' => $type],
			'answers.response_id',
			'naq_scores',
			'answers.response_id = naq_scores.response_id'
		)->result_array();
		
		if (empty($data)) {
			$response = [
				'status' => 200,
				'responses' => [],
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 200,
				'responses' => $data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function share_with_other_post() {
		$user_id = $_POST['user_id'];
		$data = $this->common_model->select_where('*', 'share_response', ['requester_id' => $user_id])->result_array();

		if (empty($data)) {
			$response = [
				'status' => 200,
				'responses' => [],
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		} else {
			foreach ($data as &$row) {
				$approver_id = $row['approver_id'];

				$approver_data = $this->common_model->select_where('name', 'users', ['id' => $approver_id])->row_array();

				$row['approver_name'] = $approver_data['name'];
			}

			$response = [
				'status' => 200,
				'responses' => $data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function share_with_me_post() {
		$user_id = $_POST['user_id'];
		$data = $this->common_model->select_where('*', 'share_response', ['requester_id' => $user_id])->result_array();

		if (empty($data)) {
			$response = [
				'status' => 200,
				'responses' => [],
			];
		} else {
			foreach ($data as &$row) {
				$requester_id = $row['requester_id'];

				$requester_data = $this->common_model->select_where('name', 'users', ['id' => $requester_id])->row_array();

				$row['requester_name'] = $requester_data['name'];
			}
			$this->firestore->resetCount($user_id , 'shared_response');

			$response = [
				'status' => 200,
				'responses' => $data,
			];
		}

		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function pro_users_post() {
		
		$data = $this->common_model->select_where('id, name, email, time_zone, image', 'users', ['type' => 'coach'])->result_array();
		
		if (empty($data)) {
			$response = [
				'status' => 200,
				'data' => [],
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		} else {
			$response = [
				'status' => 200,
				'responses' => $data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function create_token_post(){

		$card_number = $_POST['card_number'];
		$exp_month = $_POST['exp_month'];
		$exp_year = $_POST['exp_year'];
		$cvc = $_POST['cvc'];

		$Token = $this->stripe_lib->createToken($card_number, $exp_month, $exp_year, $cvc);
		$response = [
			'status' => 200,
			'message' => 'Token created successfully',
			'data' => $Token,
		];
		$this->set_response($response, REST_Controller::HTTP_OK);
	}
	
	public function sage_payment_post() {
		$token = json_decode($_POST['token'], true);
		$user_id = $_POST['user_id'];
	
		if (!empty($token) && !empty($user_id)) {
			$type = $_POST['type'];
			$entity_id = $_POST['entity_id'];
			$approver_id = $_POST['approver_id'];
			// $approver_email = $_POST['approver_email'];
			$approver_email = 'm.hamza.nabil@gmail.com';
			
			$user = $this->common_model->select_where("*", "users", array('id' => $user_id))->row_array();
	
			if ($user) {
				$customer = $this->stripe_lib->addCustomer($user['name'], $user['email'], $token['id']);
				if ($customer !== null && isset($customer['id']) && !empty($customer['id'])) {
					$charge = $this->stripe_lib->chargeSage($customer['id']);
					if ($charge->status === 'succeeded') {
	
						$sub_data['user_id'] = $user_id;
						$sub_data['payment_method'] = $token['card']['brand'];
						$sub_data['stripe_customer_id'] = $customer['id'];
						$sub_data['payer_email'] = $customer['email'];
						$sub_data['amount'] = $charge->amount / 100;
						$sub_data['currency'] = $charge->currency;
						$sub_data['status'] = $charge->status;
	
						$this->common_model->insert_array('sage_payments', $sub_data);
	
						$existingConnection = $this->common_model->select_where('id', 'connection', ['requester_id' => $user_id, 'approver_id' => $approver_id])->row_array();

						if ($existingConnection) {
							$chatRoomData = [
								'requester_id' => $user_id,
								'type' => $type,
								'entity_id' => $entity_id,
								'approver_id' => $approver_id,
								'paid' => 'true',
								'connection_id' => $existingConnection['id']
							];
						} else {
							$notification_data = [
								'requester_id' => $user_id,
								'approver_id' => $approver_id,
								'role' => 'coach',
								'message' => 'You have a new connection request',
								'accept' => 'yes'
							];

							$this->common_model->insert_array('connection', $notification_data);
							$this->firestore->addData($approver_id , 'con_request');
							$newConnection = $this->common_model->select_where('id', 'connection', ['requester_id' => $user_id, 'approver_id' => $approver_id])->row_array();

							$chatRoomData = [
								'requester_id' => $user_id,
								'type' => $type,
								'entity_id' => $entity_id,
								'approver_id' => $approver_id,
								'paid' => 'true',
								'connection_id' => $newConnection['id']
							];
						}

						$insertResult = $this->common_model->insert_array('share_response', $chatRoomData);

						if ($insertResult) {
							$this->firestore->addData($approver_id , 'shared_response');
							$url = base_url();
	
							$message = "<p>Hi " . $user['name'] . ",</p>";
							$message .= "<p>You have received a payment of " . $charge->amount / 100 . " " . $charge->currency . " for the " . $type . "</p>";
					
							$this->email->set_newline("\r\n");
							$this->email->set_mailtype('html');
							$this->email->from($this->smtp_user, 'Burgeon');
							$this->email->to($approver_email);
							$this->email->subject('Burgeon submission response required.');
							$this->email->message($message);
							if($this->email->send()) {
								$response = [
									'status' => 200,
									'message' => 'Payment successful',
									'data' => $charge
								];
								$this->set_response($response, REST_Controller::HTTP_OK);
							} else {
								$response = [
									'status' => 400,
									'message' => 'Payment successful, but email sending failed',
									'data' => $charge
								];
								$this->set_response($response, REST_Controller::HTTP_OK);
							}
						} else {
							$response = [
								'status' => 400,
								'message' => 'Payment successful, but data insertion failed',
								'data' => $charge
							];
						}

					} else {
						$response = [
							'status' => 400,
							'message' => 'Payment denied',
						];
					}
				} else {
					$response = [
						'status' => 400,
						'error' => 'Customer not created',
						'message' => $customer,
					];
				}
			} else {
				$response = [
					'status' => 400,
					'message' => 'User not found',
				];
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Empty parameters',
			];

		}
	}

	public function sage_feedback_post() {
		$requester_id = $_POST['requester_id'];
		$approver_id = $_POST['approver_id'];
		$message = $_POST['message'];
		$shared_id = $_POST['shared_id'];
	
		if (!empty($message)) {
			$row_count = $this->common_model->select_where("*", "sage_feedback", array('shared_id' => $shared_id))->num_rows();
	
			if ($row_count < 5) {
				$data = array(
					'approver_id' => $approver_id,
					'requester_id' => $requester_id,
					'message' => $message,
					'shared_id' => $shared_id
				);
	
				try {
					$this->common_model->insert_array('sage_feedback', $data);
	
					$insertedId = $this->db->insert_id();
					$created_at = date('Y-m-d H:i:s');
	
					$data['id'] = $insertedId;
					$data['created_at'] = $created_at;
	
					$response = [
						'status' => 200,
						'message' => 'Feedback submitted successfully',
						'data' => $data
					];
					$this->set_response($response, REST_Controller::HTTP_OK);
				} catch (Exception $e) {
					$response = [
						'status' => 500,
						'message' => 'Internal Server Error: ' . $e->getMessage()
					];
					$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				}
			} else {
				$response = [
					'status' => 400,
					'message' => 'Feedback not submitted. Limit reached.'
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Feedback not submitted. Message is empty.'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	public function read_sage_feedback_post() {
		$shared_id = $_POST['shared_id'];
	
		if (!empty($shared_id)) {
			$data = $this->common_model->select_where('*', 'sage_feedback', array('shared_id' => $shared_id))->result_array();
			if (!empty($data)) {
				$response = [
					'status' => 200,
					'data' => $data
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 200,
					'data' => [],
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Invalid or missing shared_id parameter.'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function admin_access_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			
			if(isset($_POST['admin_access']) && !empty($_POST['admin_access'])){

				$status = $_POST['admin_access'];
			}
			else{
				$status = 'user';
			}
			$this->common_model->update_array(array('id'=>$user_id), "users", array('admin_access'=>$status));
		
			$updated_record = $this->common_model->select_where("*", "users", array('id' => $user_id))->row_array();

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

	public function module_details_post() {
		$type = $_POST['type'];
		$user_id = $_POST['user_id'];
	
		if ($type === 'column') {
			$data = $this->common_model->select_where('*', 'session_entry', ['user_id' => $user_id])->result_array();
		} elseif ($type === 'trellis') {
			$data = $this->common_model->select_where('*', 'trellis', ['user_id' => $user_id])->result_array();
		} elseif ($type === 'ladder') {
			$data = $this->common_model->select_where('*', 'ladder', ['user_id' => $user_id])->result_array();
		} elseif ($type === 'naq' || $type === 'pire') {
			$answers = $this->common_model->select_where('id, response_id, type, options, text, question_id, created_at', 'answers', ['user_id' => $user_id, 'type' => $type])->result_array();
	
			$data = array();
			$current_response_id = null;
			$grouped_data = array();
			$group_number = 1;
			$score = 0;

			foreach ($answers as $answer) {
				$response_id = $answer['response_id'];

				if ($response_id !== $current_response_id) {
					if (!empty($grouped_data)) {
						$data[] = ['group' => $group_number, 'created_at' => $date, 'score' => $score, 'response' => $grouped_data];
						$group_number++; 
					}
					$grouped_data = array();
				}
				$date = $answer['created_at'];
				
				$question_id = $answer['question_id'];
				$score = ($type !== 'pire') ? $this->common_model->select_where('*', 'naq_scores', ['response_id' => $response_id])->row_array()['score'] : '';

				$question = $this->common_model->select_where('title', 'questions', ['id' => $question_id])->row_array();
	
				$grouped_data[] = [
					'question' => $question,
					'answer' => $answer,
				];
	
				$current_response_id = $response_id;
			}
			if (!empty($grouped_data)) {
				$data[] = ['group' => $group_number, 'created_at' => $date, 'score' => $score, 'response' => $grouped_data];
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Type is unsupported.',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
	
		if (!empty($data)) {
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $data
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 200,
				'message' => 'Data is not found.',
				'data' => [],
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function module_type_list_post() {
		$connection_id = $this->input->post('connection_id');
	
		$connection_data = $this->common_model->select_where('*', 'shared_module', array('connection_id' => $connection_id))->result_array();
	
	
		if (empty($connection_data)) {
			$response = [
				'status' => 200,
				'data' => [],
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 200,
				'data' => $connection_data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function edit_module_post() {
		$connection_id = $this->input->post('connection_id');
		$requester_id = $this->input->post('requester_id');
		$approver_id = $this->input->post('approver_id');
		$role = $this->input->post('role');
	
		if (empty($connection_id)) {
			$response = [
				'status' => 400,
				'message' => 'Connection_id parameter is missing',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
	
		if (empty($this->input->post('module'))) {
			$this->db->delete('module_requested', array('connection_id' => $connection_id, 'requester_id' => $requester_id));
			$response = [
				'status' => 200,
				'message' => 'Module deleted successfully',
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
			return;
		}
	
		$raw_modules = explode(', ', $this->input->post('module'));
		$new_modules = array_map('lcfirst', $raw_modules);
	
		if (!empty($role)) {
			$this->common_model->update_array(['id' => $connection_id], "connection", ['role' => $role]);
		}
	
		$old_modules = $this->common_model->select_where(['module', 'permission'], 'module_requested', ['connection_id' => $connection_id, 'requester_id' => $requester_id])->result_array();

		foreach ($old_modules as $old_module) {
			$module_name = $old_module['module'];
			$old_permission = $old_module['permission'];
		
			if (in_array($module_name, $new_modules)) {
				$key = array_search($module_name, $new_modules);
				unset($new_modules[$key]);
			} else {
				$this->db->delete('module_requested', ['connection_id' => $connection_id, 'module' => $module_name, 'requester_id' => $requester_id]);
			}
		}
	
		foreach ($new_modules as $new_module) {
			$module_exists = $this->common_model->select_where('module', 'module_requested', ['connection_id' => $connection_id, 'module' => trim($new_module), 'requester_id' => $requester_id])->row_array();
	
			if (!$module_exists) {
				$shared_module = [
					'connection_id' => $connection_id,
					'module' => trim($new_module),
					'requester_id' => $requester_id,
					'approver_id' => $approver_id,
					'permission' => 'pending',
				];
				$this->db->insert('module_requested', $shared_module);	
			}
		}
		$this->firestore->addData($approver_id , 'con_request');
	
		$update_data = $this->common_model->select_where('connection_id, module', 'module_requested', ['connection_id' => $connection_id])->result_array();
	
		$response = [
			'status' => 200,
			'message' => 'success',
			'data' => $update_data
		];
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function accept_shared_module_post() {
		$permission = $_POST['permission'];
		$connection_id = $_POST['connection_id'];
		$approver_id = $_POST['approver_id'];
	
		$this->db->where(['connection_id' => $connection_id,'approver_id' => $approver_id]);
		$this->db->update('module_requested', ['permission' => $permission]);
	
		$affected_rows = $this->db->affected_rows();
	
		if ($permission === 'reject' && $affected_rows > 0) {
			$this->db->delete('module_requested', ['connection_id' => $connection_id]);
			$response_message = 'Permission is rejected.';
		} elseif ($permission === 'accept') {
			$data = $this->common_model->select_where('*', 'module_requested', ['connection_id' => $connection_id])->result_array();
		
			if (!empty($data)) {
				$response_message = 'Permission is accepted.';
				$response_status = 200;  
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response_message = 'No records found for the specified connection_id';
				$response_status = 400;  
			}
		} elseif ($affected_rows > 0) {
			$response_message = 'Permission is updated successfully.';
			$response_status = 200;
		} else {
			$response_message = 'Data is not found.';
			$response_status = 400; 
		}
		
		$response = [
			'status' => $response_status,
			'message' => $response_message
		];
		
		$this->set_response($response, REST_Controller::HTTP_OK);
		
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}	
	
	public function single_type_list_post() {
		$type = $_POST['module_type'];
		$connection_id = $_POST['connection_id'];
		$approver_id = $_POST['approver_id'];
	
		$share_responses = $this->common_model->select_where('*', 'share_response', ['connection_id' => $connection_id, 'type' => $type, 'approver_id' => $approver_id])->result_array();
	
		$data = array();
	
		foreach ($share_responses as $share_response) {
			$entity_id = $share_response['entity_id'];
	
			if ($type === 'column') {
				$data[] = $this->common_model->select_where('*', 'session_entry', ['id' => $entity_id])->row_array();
			} elseif ($type === 'trellis') {
				$data[] = $this->common_model->select_where('*', 'trellis', ['id' => $entity_id])->row_array();
			} elseif($type === 'ladder') {
				$data[] = $this->common_model->select_where('*', 'ladder', ['id' => $entity_id])->row_array();
			}
			 elseif ($type === 'naq' || $type === 'pire') {
				$answers = $this->common_model->select_where('id, response_id, type, options, text, question_id, created_at', 'answers', ['response_id' => $entity_id, 'type' => $type])->result_array();
				
				$grouped_data = array();
				$current_response_id = null;
				$group_number = 1;

				foreach ($answers as $answer) {
					$response_id = $answer['response_id'];
					
					if ($response_id !== $current_response_id) {
						if (!empty($grouped_data)) {
							$data[] = ['group' => $group_number, 'score' => $score, 'created_at' => $date, 'response' => $grouped_data];
							$group_number++;
						}
						$grouped_data = array();
					}

					$score = $this->common_model->select_where('*', 'naq_scores', ['response_id' => $response_id])->row_array();
					$score = $score['score'];

					$date = $answer['created_at'];
					$question_id = $answer['question_id'];
					
					$question = $this->common_model->select_where('title', 'questions', ['id' => $question_id])->row_array();
					
					$grouped_data[] = [
						'question' => $question,
						'answer' => $answer,
					];
	
					$current_response_id = $response_id;
				}

				if($type === 'pire'){
					$score = "";
					}
	
				if (!empty($grouped_data)) {
					$data[] = ['group' => $group_number, 'score' => $score, 'created_at' => $date, 'response' => $grouped_data];
				}
			}
		}
	
		if (!empty($data)) {
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => $data
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 200,
				'message' => 'Data not found.',
				'data' => []
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function single_share_list_post(){
		$requester_id = $_POST['requester_id'];
	
		$share_responses = $this->common_model->select_where('*', 'share_response', ['requester_id' => $requester_id])->result_array();
	
		if (!empty($share_responses)) {
			$data = array();
	
			foreach ($share_responses as $share_response) {
				$connection_id = $share_response['connection_id'];
	
				$share_item = [
					'id' => $share_response['id'],
					'requester_id' => $share_response['requester_id'],
					'approver_id' => $share_response['approver_id'],
					'type' => $share_response['type'],
					'paid' => $share_response['paid'],
					'connection_id' => $share_response['connection_id'],
					'entity_id' => $share_response['entity_id'],
					'status' => $share_response['status'],
					'created_at' => $share_response['created_at']
				];
	
				if (!isset($data[$connection_id])) {
					$data[$connection_id] = [
						'connection_id' => $connection_id,
						'share_list' => []
					];
				}
	
				$data[$connection_id]['share_list'][] = $share_item;
			}
	
			$response = [
				'status' => 200,
				'message' => 'success',
				'data' => array_values($data) // Re-index the array numerically
			];
	
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$response = [
				'status' => 200,
				'message' => 'Data not found.',
				'data' => []
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}

	public function delete_single_share_post(){
		$delete_id = $_POST['delete_id'];
		$this->db->delete('share_response', array('id' => $delete_id));
		$response = [
			'status' => 200,
			'message' => 'Your share has been deleted successfully.'
		];
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function completed_chk_post(){
		$session_id = $_POST['session_id'];

		if(!empty($session_id)){
			
			if(isset($_POST['completed']) && !empty($_POST['completed'])){

				$status = $_POST['completed'];
			}
			else{
				$status = 'no';
			}
			$this->common_model->update_array(array('id'=>$session_id), "session_entry", array('completed'=>$status));
		
			$updated_record = $this->common_model->select_where("*", "session_entry", array('id' => $session_id))->row_array();

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
					'message' => 'Record is not found.'
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
 
	public function trellis_share_insert_post() {
		
		$module_type = $_POST['module_type'];
		$connection_id = $_POST['connection_id'];
		$requester_id = $_POST['requester_id'];
		$approver_id = $_POST['approver_id']; 

		$data = [
			'connection_id' => $connection_id,
			'module' => $module_type,
			'requester_id' => $requester_id,
			'approver_id' => $approver_id,
			'permission' => 'accept',
			];
	
			$insert_result = $this->common_model->insert_array('module_requested', $data);
			
			
			if ($insert_result) {
				$inserted_entries[] = $data;
			}
		
	
		if (!empty($inserted_entries)) {
			$response = [
				'status' => 200,
				'message' => 'success',
				'responses' => $inserted_entries,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		} else {
			$error_response = [
				'status' => 400, 
				'message' => 'Data insertion is failed.',
			];
			$this->set_response($error_response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function share_trellis_read_post(){
		$user_id = $_POST['user_id'];

		if(!empty($user_id)){
			$trellis['trellis'] = $this->common_model->select_where("id,name,name_desc,purpose", "trellis", array('user_id'=>$user_id))->result_array();
			$trellis['tribe'] = $this->common_model->select_where("*", "tribe_new", array('user_id'=>$user_id))->result_array();
			$trellis['ladder'] = $this->common_model->select_where("id, type,favourite, option1, option2, date, text, description", "ladder", array('user_id'=>$user_id, 'favourite'=>'yes'))->result_array();
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
	public function user_detail_post() {
		$user_id = $_POST['user_id'];
	
		if (!empty($user_id)) {
			$user_detail = $this->common_model->select_where(
				"id, name, email, type, image, time_zone",
				"users",
				array('id' => $user_id)
			)->row_array();
	
			if ($user_detail) {
				$defaultImagePath = base_url('uploads/app_users/default.png');
				$image_url = isset($user_detail['image']) ? base_url('uploads/app_users/') . $user_detail['image'] : $defaultImagePath;
				$user_detail['image_url'] = $image_url;
	
				$response = [
					'status' => 200,
					'message' => 'success',
					'data' => $user_detail
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 404,
					'message' => 'User is not found'
				];
				$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Empty parameters'
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	public function app_share_insert_post() {
		
		$module_type = $_POST['module_type'];
		$connection_id = $_POST['connection_id'];
		$requester_id = $_POST['requester_id'];
		$approver_id = $_POST['approver_id'];

		$data = [
			'connection_id' => $connection_id,
			'module' => $module_type,
			]; 

		$existing_data = $this->common_model->select_where("*", "shared_module", array('connection_id' => $connection_id, 'module' => $module_type))->row_array();
		if (empty($existing_data)) {
			$data_to_insert = [
				'connection_id' => $connection_id,
				'module' => $module_type,
			];
			$insert_result = $this->common_model->insert_array('shared_module', $data_to_insert);
	
			if ($insert_result) {
				$response = [
					'status' => 200,
					'message' => 'success',
					'responses' => [$data_to_insert],
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 500,
					'message' => 'Internal Server Error. Unable to insert data.',
				];
				$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'Data already Shared.',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}		
	}
	public function paid_share_response_post() {

		$user_id = $_POST['user_id'];
		$data = $this->common_model->select_where('*', 'share_response', array('requester_id' => $user_id, 'paid' => 'true'))->result_array();

		if (empty($data)) {
			$response = [
				'status' => 200,
				'responses' => [],
			];
			$this->set_response($response, REST_Controller::HTTP_NOT_FOUND);
		} else {
			foreach ($data as &$row) {
				$approver_id = $row['approver_id'];

				$approver_data = $this->common_model->select_where('name', 'users', ['id' => $approver_id])->row_array();

				$row['approver_name'] = $approver_data['name'];
			}

			$response = [
				'status' => 200,
				'responses' => $data,
			];
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
	}
	
	public function approver_sending_request_post() {
		$user_id = $this->input->post('user_id');
		$defaultImagePath = base_url('uploads/app_users/default.png');
		$this->firestore->resetCount($user_id , 'con_request');

	
		$condition = [
			'approver_id' => $user_id,
			'permission' => 'pending',
		];
	
		$pending_data = $this->common_model->select_where('*', 'module_requested', $condition)->result_array();
	
		if (empty($pending_data)) {
			$response = [
				'status' => 200,

				'data' => [],
			];
		} else {
			$response = [
				'status' => 200,
				'data' => [],
			];
	
			$grouped_data = [];
	
			foreach ($pending_data as $pendingdata) {
				$connection_id = $pendingdata['connection_id'];
	
				if (!isset($grouped_data[$connection_id])) {
					$grouped_data[$connection_id] = [
						'pending_info' => [],
						'first_user_detail' => [],
						'second_user_detail' => [],
						'module' => [],
					];
				}
	
				$requester_id = $pendingdata['requester_id'];
				$approver_id = $pendingdata['approver_id'];
				$approver_data = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array();
				$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
				$pending_info = [
					'id' => $pendingdata['id'],
					'permission' => $pendingdata['permission'],
					'requester_id' => $requester_id,
					'approver_id' => $pendingdata['approver_id'],
					'connection_id' => $pendingdata['connection_id'],
					// 'image' => base_url()."uploads/app_users/" . $user_data['image'],
					'image' => isset($approver_data['image']) ? base_url('uploads/app_users/') . $approver_data['image'] : $defaultImagePath,
					'requester_name' => $requester_data['name'],
					'approver_name' => $approver_data['name'],
				];
	
				$first_user_detail = [
					'id' => $approver_data['id'],
					'name' => $approver_data['name'],
					'email' => $approver_data['email'],
					// 'image' => base_url()."uploads/app_users/" . $user_data['image'],
					'image' => isset($approver_data['image']) ? base_url('uploads/app_users/') . $approver_data['image'] : $defaultImagePath,
				];
	
				$second_user_detail = [
					'id' => $requester_data['id'],
					'name' => $requester_data['name'],
					'email' => $requester_data['email'],
					// 'image' => base_url()."uploads/app_users/" . $requester_data['image'],
					'image' => isset($requester_data['image']) ? base_url('uploads/app_users/') . $requester_data['image'] : $defaultImagePath,
				];
	
				// Collect module values for each connection_id
				$grouped_data[$connection_id]['pending_info'][] = $pending_info;
				$grouped_data[$connection_id]['first_user_detail'] = $first_user_detail;
				$grouped_data[$connection_id]['second_user_detail'] = $second_user_detail;
				$grouped_data[$connection_id]['module'][] = $pendingdata['module'];
			}
	
			// Combine all grouped data into the response
			foreach ($grouped_data as $combined_data) {
				$response['data'][] = $combined_data;
			}
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function requester_sending_request_post() {
		$user_id = $this->input->post('user_id');
		$defaultImagePath = base_url('uploads/app_users/default.png');
	
		$condition = [
			'requester_id' => $user_id,
			'permission' => 'pending',
		];
	
		$pending_data = $this->common_model->select_where('*', 'module_requested', $condition)->result_array();
	
		if (empty($pending_data)) {
			$response = [
				'status' => 200,
				'data' => [],
			];
		} else {
			$response = [
				'status' => 200,
				'data' => [],
			];
	
			$grouped_data = [];
	
			foreach ($pending_data as $pendingdata) {
				$connection_id = $pendingdata['connection_id'];
	
				if (!isset($grouped_data[$connection_id])) {
					$grouped_data[$connection_id] = [
						'pending_info' => [],
						'first_user_detail' => [],
						'second_user_detail' => [],
						'module' => [],
					];
				}
	
				$requester_id = $pendingdata['requester_id'];
				$approver_id = $pendingdata['approver_id'];
				$approver_data = $this->common_model->select_where('*', 'users', ['id' => $approver_id])->row_array();
				$requester_data = $this->common_model->select_where('*', 'users', ['id' => $requester_id])->row_array();
				$pending_info = [
					'id' => $pendingdata['id'],
					'permission' => $pendingdata['permission'],
					'requester_id' => $requester_id,
					'approver_id' => $pendingdata['approver_id'],
					'connection_id' => $pendingdata['connection_id'],
					// 'image' => base_url()."uploads/app_users/" . $user_data['image'],
					'image' => isset($approver_data['image']) ? base_url('uploads/app_users/') . $approver_data['image'] : $defaultImagePath,
					'requester_name' => $requester_data['name'],
					'approver_name' => $approver_data['name'],
				];
	
				$first_user_detail = [
					'id' => $approver_data['id'],
					'name' => $approver_data['name'],
					'email' => $approver_data['email'],
					// 'image' => base_url()."uploads/app_users/" . $user_data['image'],
					'image' => isset($approver_data['image']) ? base_url('uploads/app_users/') . $approver_data['image'] : $defaultImagePath,
				];
	
				$second_user_detail = [
					'id' => $requester_data['id'],
					'name' => $requester_data['name'],
					'email' => $requester_data['email'],
					// 'image' => base_url()."uploads/app_users/" . $requester_data['image'],
					'image' => isset($requester_data['image']) ? base_url('uploads/app_users/') . $requester_data['image'] : $defaultImagePath,
				];
	
				// Collect module values for each connection_id
				$grouped_data[$connection_id]['pending_info'][] = $pending_info;
				$grouped_data[$connection_id]['first_user_detail'] = $first_user_detail;
				$grouped_data[$connection_id]['second_user_detail'] = $second_user_detail;
				$grouped_data[$connection_id]['module'][] = $pendingdata['module'];
			}
	
			// Combine all grouped data into the response
			foreach ($grouped_data as $combined_data) {
				$response['data'][] = $combined_data;
			}
		}
	
		$this->set_response($response, REST_Controller::HTTP_OK);
	}
	public function pending_connection_update_post() {
		$connection_id = $this->input->post('connection_id');
		$module = $this->input->post('module');
		$role = $this->input->post('role');
		
		if (!empty($connection_id)) {
			$this->db->delete('module_requested', array('connection_id' => $connection_id));
			if ($this->input->post('module') !== null) {
				$new_modules = explode(',', $this->input->post('module'));
				if (!empty($role)) {
					$this->common_model->update_array(array('id'=>$connection_id), "connection", array('role'=>$role));
			   }
			   $connection_data = $this->common_model->select_where('requester_id,approver_id',"connection",['id'=>$connection_id])->row_array();
			  
				foreach ($new_modules as $module) {
					$shared_module = [
						'requester_id' => $connection_data['requester_id'],
						'approver_id' => $connection_data['approver_id'],
						'connection_id' => $connection_id,
						'module' => trim($module),
						'permission' => 'accept'
					];
					$this->db->insert('module_requested', $shared_module);

				}

				$update_data = $this->common_model->select_where('connection_id, module', 'module_requested', ['connection_id' => $connection_id])->result_array();
				
				$response = [
					'status' => 200,
					'message' => 'success',
					'data' => $update_data
				];
				$this->set_response($response, REST_Controller::HTTP_OK);
			} else {
				$response = [
					'status' => 400,
					'message' => 'module parameter is missing',
				];
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			}
		} else {
			$response = [
				'status' => 400,
				'message' => 'connection_id parameter is missing',
			];
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function getTimeZoneSecondIndex($firstIndex) {
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

        // Check if the first index exists in the array
        if (array_key_exists($firstIndex, $time_zone_map)) {
            // Return the corresponding second index
            return $time_zone_map[$firstIndex];
        } else {
            // If the first index is not found, you can return a default value or handle the error as needed
            return "Unknown Time Zone";
        }
    }
}