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
	}

	public function signup_post(){

		$name	=	$_POST['name'];
		$email	=	$_POST['email'];
		$password	=	$_POST['password'];
		$result = $this->common_model->select_where("*", "users", array('email'=>$email))->result_array();
		if($result){
			$response['error'] = 'Already signed up';
		}
		else{
				$data['name'] = $name;
				$data['email'] = $email;
				$data['password'] = sha1($password);
				$data['type'] = 'user';
				$data['status'] = 'active';
				$result = $this->common_model->insert_array('users', $data);
			if($result){
				$response['user_signup'] = 'TRUE';
			}
			else{
				$response['user_signup'] = 'FALSE';
			}
		}
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

   	public function login_post(){

		$email	=	$_POST['email'];
		$password	=	$_POST['password'];
			
		$data['login'] = $this->common_model->select_where("*","users", array('email'=>$email,'password'=>sha1($password)));
		
		if($data['login']->num_rows()>0){
			$row = $data['login']->row();
			if($row->status=='inactive'){
				$response['error'] = 'inactive user';
				$this->set_response($response, REST_Controller::HTTP_OK);
			} 

			$user_data = array(
				'user_logged_in'  =>  TRUE,
				'usertype' => $row->type,
				'username' => $row->name,
				'useremail' => $row->email,
				'userid' => $row->id
			);
			
			if($_POST['rememberme']=='on')   
			{
				$cookieUsername = array(
					'name'   => 'frontuser',
					'value'  => $email,
					'expire' => time()+1000,
					'path'   => '/',
					'secure' => false
				);
				$cookiePassword = array(
					'name'   => 'frontpass',
					'value'  => $password,
					'expire' => time()+1000,
					'path'   => '/',
					'secure' => false
				);
				$check_rem = array(
					'name'   => 'user_rememeber',
					'value'  => 1,
					'expire' => time()+1000,
					'path'   => '/',
					'secure' => false
				);
			
				$this->input->set_cookie($cookieUsername);
				$this->input->set_cookie($check_rem);
				$this->input->set_cookie($cookiePassword);
			}

			$response['user_login'] = 'TRUE';
			$response['user_session'] = $user_data;
			$response['user_cookie'] = $_COOKIE;
			$this->set_response($response, REST_Controller::HTTP_OK);
			
		}
		else{
			$response['user_login'] = 'FALSE';
			$this->set_response($response, REST_Controller::HTTP_OK);
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
			$response['questions'] = $questions;
		}
		else{
			$response['error'] = 'data not found';
		} 
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function social_login_post(){

		$name	    =	$_POST['name'];
		$email	    =	$_POST['email'];
		$auth_id	=	$_POST['auth_id'];

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
						'authID' => $valid_user['social_auth_id'],
						'userid' => $valid_user['id']
					);
					$response['user_login'] = 'TRUE';
					$response['user_session'] = $user_data;
				}
				elseif(empty($valid_user['social_auth_id'])){
					$data['social_auth_id'] = $auth_id;
					$this->common_model->update_array(array('id'=> $valid_user['id']), 'users', $data);
					$data = array(
						'user_logged_in'  =>  TRUE,
						'usertype' => $valid_user['type'],
						'username' => $valid_user['name'],
						'useremail' => $valid_user['email'],
						'authID' => $auth_id,
						'userid' => $valid_user['id']
					);
					$response['login_session'] = $data;
				}
				else{
					$response['error'] = 'Invalid Auth id';
				}
				$this->set_response($response, REST_Controller::HTTP_OK);
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
							$response['message'] = 'New social user created';
							$response['user_login'] = 'TRUE';
							$response['user_session'] = $user_data;
						}
					}
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
		else{
			$response['error'] = 'Invalid User';
		}
		$this->set_response($response, REST_Controller::HTTP_OK);
	}


	public function single_answer_post(){
		$question_id = $_POST['question_id'];
		$user_id = $_POST['user_id'];
		$created_at = $_POST['created_at'];
		$result = $this->common_model->select_where("*", "answers", array('user_id'=>$user_id,'question_id'=>$question_id,'created_at'=>$created_at))->row_array();

		$response['single_answer'] = $result;
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function user_response_post(){
		$data['question_id'] = $_POST['question_id'];
		$data['user_id'] = $_POST['user_id'];
		$data['options'] =  $_POST['options'];
		$data['text'] = $_POST['text'];
		$insert = $this->common_model->insert_array('answers', $data);
		if($insert){
			$response['message'] = 'Success';
		}
		else{
			$response['error'] = 'Error inserting';
		}
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

	public function change_password_post(){
		$user_id = $_POST['user_id'];
		$old_password = $_POST['old_password'];
		$auth_id = $_POST['auth_id'];
		$update['password'] = sha1($_POST['new_password']);
		if($auth_id){
			$result = $this->common_model->update_array(array('id'=> $user_id,'social_auth_id'=>$auth_id), 'users', $update);
			if($this->db->affected_rows()> 0){
				$response['success'] = 'Password changed';
			}else{
				$response['error'] = 'No change in password';
			}
		}
		elseif($old_password){
			$result = $this->common_model->update_array(array('id'=> $user_id,'password'=>sha1($old_password)), 'users', $update);
			if($this->db->affected_rows()> 0){
				$response['success'] = 'Password changed';
			}else{
				$response['error'] = 'No change in password';
			}
		}
		else{
            $response['error'] = 'Invalid parameters';
		}
		$this->set_response($response, REST_Controller::HTTP_OK);
	}

}
