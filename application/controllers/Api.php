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

	public function sign_up_post(){

		$result = $this->common_model->select_where("*", "users", array('email'=>$_POST['email']));
		if(count($result->result_array())>0){
			$response['error'] = 'Already signed up';
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
				$data['name'] = $_POST['name'];
				$data['email'] = $_POST['email'];
				$data['password'] = sha1($_POST['password']);
				$data['type'] = 'user';
				$data['status'] = 'active';
				$result = $this->common_model->insert_array('users', $data);
			if($result){
				$response['message'] = 'user registration successful';
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
			else{
				$response['error'] = 'user registration error';
				$this->set_response($response, REST_Controller::HTTP_OK);
			}
		}
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

			$data = array(
				'user_logged_in'  =>  TRUE,
				'usertype' => $row->type,
				'username' => $row->name,
				'useremail' => $row->email,
				'userid' => $row->id
			);
			
			$this->session->set_userdata($data);
			
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

			$response['message'] = 'login success';
			$response['user_session'] = $_SESSION;
			$response['user_cookie'] = $_COOKIE;
			$this->set_response($response, REST_Controller::HTTP_OK);
			
		}
		else{
			$response['error'] = 'login failed';
			$this->set_response($response, REST_Controller::HTTP_OK);
		} 
	}

	public function questions_get(){
		$response['questions'] = $this->common_model->select_all_order_by('*', 'questions','id','ASC')->result_array();
		if($response['questions']){
			$this->set_response($response, REST_Controller::HTTP_OK);
		}
		else{
			$response['error'] = 'data not found';
			$this->set_response($response, REST_Controller::HTTP_OK);
		} 
	}


}
