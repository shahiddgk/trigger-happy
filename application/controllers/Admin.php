<?php
ob_start();
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'third_party/stripe-php/init.php';

class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();

		$CI->config->load('stripe', TRUE);
        $this->stripe_api_key = $CI->config->item('stripe_api_key', 'stripe');   
	}

    public function index(){
        $this->load->view('admin/login');
    }

	public function dashboard(){
		$data['page_title'] = 'Dashboard';
		$data['num_rows'] = $this->common_model->select_where_table_rows('*', 'users', array('type'=>'user'));
        
		$stripe = new \Stripe\StripeClient($this->stripe_api_key);

		$data['subscriptions'] = $stripe->subscriptions->all(['expand' => ['data.customer', 'data.plan']]);

		// echo "<pre>"; print_r($data['subscriptions']); exit;

		$this->load->view('admin/include/header');
        $this->load->view('admin/dashboard', $data);
        $this->load->view('admin/include/footer');
    }
	
	public function add_question(){
		$data['page_title'] = 'Add Question';
        $this->load->view('admin/include/header');
        $this->load->view('admin/add_question', $data);
        $this->load->view('admin/include/footer');
    }

	public function questions(){
		$data['page_title'] = 'Questions List';
		$questions = $this->common_model->select_all_order_by('*', 'questions', 'id', 'ASC')->result_array();

		$filter = $this->input->get('filter');

		if ($filter) {
			$questions = array_filter($questions, function ($question) use ($filter) {
				return $question['type'] === $filter;
			});
		}

		$data['questions'] = $questions;

		$this->load->view('admin/include/header');
		$this->load->view('admin/questions', $data);
		$this->load->view('admin/include/footer');
	}

	public function insert_question(){
		$json_options = 'NULL';
		$data['title'] = $this->input->post('q_title');
		$data['response_type'] = $this->input->post('res_type');
		$data['sub_title'] = $this->input->post('sub_title');
		$data['video_url'] = $this->input->post('video_url');
		if($_POST['res_type'] == 'open_text'){
			$data['text_length'] = $this->input->post('text_length');
		}
		if(!empty($_POST['q_options'])){
			$json_options = json_encode($this->input->post('q_options'));
		}
		$data['options'] = $json_options;
		$data['type'] = $this->input->post('question_for');
		$this->db->insert('questions', $data);

		redirect('admin/questions'); 
	}

	public function edit_question($id){

		$data['page_title'] = 'Edit Question';
		$data['question'] = $this->common_model->select_where("*" , 'questions', array('id'=> $id))->row_array();
		$this->load->view('admin/include/header');
        $this->load->view('admin/edit_question', $data);
        $this->load->view('admin/include/footer');
	}

	public function edit_profile(){
		$data['page_title'] = 'Edit Profile';
		$data['user_data'] = $this->common_model->select_where("*" , 'users', array('id'=> $this->session->userdata('userid')))->row_array();
		$this->load->view('admin/include/header');
        $this->load->view('admin/edit_profile', $data);
        $this->load->view('admin/include/footer');
	}

	public function update_profile(){
		$id = $this->input->post('user_id');
		$data['name'] = $this->input->post('name');
		$data['email'] = $this->input->post('email');
		if(!empty($_POST['password'])){
			$data['password'] = sha1($this->input->post('password'));
		}
		if($_FILES['profile_img']['name']!=''){

			$image  =  $_FILES['profile_img']['name'];
			$data['image']  =  $image;
			$temp   =  $_FILES['profile_img']['tmp_name'];
			if (!file_exists('./uploads/profile')) {
				mkdir('./uploads/profile', 0755, true);
			} 
			$path= './uploads/profile/'.$image;
			move_uploaded_file($temp,$path);
		}
		$this->common_model->update_array(array('id'=> $id), 'users', $data);
		redirect('admin/dashboard'); 
	}

	public function update_question($id){
		$json_options = 'NULL';
		$data['title'] = $this->input->post('q_title');
		$data['video_url'] = $this->input->post('video_url');
		$data['response_type'] = $this->input->post('res_type');
		$data['sub_title'] = $this->input->post('sub_title');
		if($_POST['res_type'] == 'open_text'){
			$data['text_length'] = $this->input->post('text_length');
		}
		if(!empty($_POST['q_options'])){
			$json_options = json_encode($this->input->post('q_options'));
		}
		$data['options'] = $json_options;
		$data['type'] = $this->input->post('question_for'); // Add the selected value to the data array
		$this->common_model->update_array(array('id' => $id), 'questions', $data);

		redirect('admin/questions'); 
	}

	public function delete_question($id){

		$this->common_model->delete_where(array('id'=> $id), 'questions');
		redirect('admin/questions'); 
	}

    public function login(){
		$email	=	$this->input->post('email');
		$password	=	$this->input->post('password');
		$data = $this->common_model->select_where("*","users", array('email'=>$email, 'password'=>sha1($password), 'type'=>'admin'))->row_array();
		if(!empty($data)){
			if($data['status']=='inactive'){
				echo "inactive"; exit;
			} 

			$data = array(
				'user_logged_in'  =>  TRUE,
				'userid' => $data['id'],
				'usertype' => $data['type'],
				'username' => $data['name'],
				'useremail' => $data['email'],
				'userimage' => $data['image']
			);
			
			$this->session->set_userdata($data);	

 			$this->session->set_flashdata('flash_message', 'Login successfully.');
			redirect(site_url('admin/dashboard'));
		}
		else{
			$this->session->set_flashdata('error_message', 'Login error.');
			redirect(site_url()); 
		} 
	}

	public function logout(){
		$this->session->unset_userdata('user_logged_in');
		$this->session->unset_userdata('usertype');
		$this->session->unset_userdata('username');
		$this->session->unset_userdata('useremail');
		$this->session->unset_userdata('userid');
		$this->session->set_flashdata('error_message', 'Logout successfully.');
		redirect(site_url()); 
	}

	public function users_list(){
		$data['page_title'] = 'Users List';
		$data['users'] = $this->common_model->select_where_ASC_DESC('*', 'users', array('type'=>'user','email !=' =>'test@triggerhappy.com'), 'id','ASC')->result_array();
        $this->load->view('admin/include/header');
        $this->load->view('admin/users', $data);
        $this->load->view('admin/include/footer');
    }

	public function users_by_date(){
		$data['page_title'] = 'User Responses By Date';
        $this->load->view('admin/include/header');
        $this->load->view('admin/users_by_date', $data);
        $this->load->view('admin/include/footer');
    }

	public function get_users_by_date(){
		$selectedDate = $this->input->post('date');
		$users = $this->common_model->get_users_reponse_by_date($selectedDate)->result();
		echo json_encode($users);
	}

	public function date_group($id = null){
		$data['page_title'] = 'Date Group';
		
		$data['answers'] = $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$id), 'created_at' , 'DESC', 'DATE(created_at)')->result_array();
        $this->load->view('admin/include/header');
        $this->load->view('admin/date_group', $data);
        $this->load->view('admin/include/footer');
    }
	
	public function get_response($response_id = null){
		$data['page_title'] = 'Users Response';
		$data['answers'] = $this->common_model->select_two_tab_join_where("a.* , q.title",'answers a', 'questions q', 'a.question_id=q.id', array('a.response_id'=>$response_id))->result_array();
		$this->load->view('admin/include/header');
        $this->load->view('admin/answers', $data);
        $this->load->view('admin/include/footer');
    }

	public function delete_user($id){
		$this->common_model->delete_where(array('id'=> $id), 'users');
		redirect('admin/users_list'); 
	}

	public function trellis(){
		$data['page_title'] = 'Trellis List';
		$data['trellis'] = $this->common_model->select_all("*", "trellis")->result_array();
		$this->load->view('admin/include/header');
		$this->load->view('admin/trellis', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function user_needs($id) {
		$data['page_title'] = 'User Needs';
		$data['needs'] = $this->common_model->select_where("*", "identity", array('user_id'=> $id, 'type' => 'needs'))->result_array();

		$this->load->view('admin/include/header');
		$this->load->view('admin/user_needs', $data);
		$this->load->view('admin/include/footer'); 
	}
	
	public function user_identity($id) {
		$data['page_title'] = 'User Identity';
		$data['identity'] = $this->common_model->select_where("*", "identity", array('user_id'=> $id, 'type' => 'identity'))->result_array();

		$this->load->view('admin/include/header');
		$this->load->view('admin/user_identity', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function user_tribe($id) {
		$data['page_title'] = 'User Tribe';
		$data['tribe'] = $this->common_model->select_where("*", "tribe", array('user_id'=> $id))->result_array();

		$this->load->view('admin/include/header');
		$this->load->view('admin/user_tribe', $data);
		$this->load->view('admin/include/footer'); 
	}
	
	public function user_principle($id) {
		$data['page_title'] = 'User Principle';
		$data['principle'] = $this->common_model->select_where("*", "principles", array('user_id'=> $id, 'type' => 'principles'))->result_array();

		$this->load->view('admin/include/header');
		$this->load->view('admin/user_principles', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function user_rhythms($id) {
		$data['page_title'] = 'Rhythms';
		$data['rhythms'] = $this->common_model->select_where("*", "principles", array('user_id'=> $id, 'type' => 'rhythms'))->result_array();

		$this->load->view('admin/include/header');
		$this->load->view('admin/user_rhythms', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function user_goal($id) {
		$data['page_title'] = 'Goal/Challanges';
		$data['goal'] = $this->common_model->select_where("*", "ladder", array('user_id'=> $id, 'type' => 'goal'))->result_array();

		$this->load->view('admin/include/header');
		$this->load->view('admin/user_goal', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function user_achievements($id) {
		$data['page_title'] = 'Memories/Achievements';
		$data['achievements'] = $this->common_model->select_where("*", "ladder", array('user_id'=> $id, 'type' => 'achievements'))->result_array();
		
		$this->load->view('admin/include/header');
		$this->load->view('admin/user_achievements', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function settings() {
		$data['page_title'] = 'settings';
		$data['trellis'] = $this->common_model->select_all("*", "settings")->row_array();
		$this->load->view('admin/include/header');
		$this->load->view('admin/settings', $data);
		$this->load->view('admin/include/footer'); 
	}

	public function trilles_settings() 
	{
		if(isset($_POST['goal'])){
			$data['goal']= $this->input->post('goal');	
		}
		if(isset($_POST['achievements'])){
			$data['achievements']= $this->input->post('achievements');	
		}
		if(isset($_POST['principle'])){
			$data['principle']= $this->input->post('principle');	
		}
		if(isset($_POST['rhythms'])){
			$data['rhythms']= $this->input->post('rhythms');	
		}
		if(isset($_POST['needs'])){
			$data['needs']= $this->input->post('needs');	
		}
		if(isset($_POST['identity'])){
			$data['identity']= $this->input->post('identity');	
		}
		if(isset($_POST['tribe'])){
			$data['tribe']= $this->input->post('tribe');	
		}
		if(isset($_POST['needs'])){
			$data['needs']= $this->input->post('needs');	
		}
		if(isset($_POST['cur_apple'])){
			$data['cur_apple']= $this->input->post('cur_apple');	
		}
		if(isset($_POST['coming_apple'])){
			$data['coming_apple']= $this->input->post('coming_apple');	
		}
		if(isset($_POST['cur_playstore'])){
			$data['cur_playstore']= $this->input->post('cur_playstore');	
		}
		if(isset($_POST['coming_playstore'])){
			$data['coming_playstore']= $this->input->post('coming_playstore');	
		}
		if(isset($_POST['new_updates'])){
			$data['new_updates']= $this->input->post('new_updates');	
		}
		
		$result_array =  $this->common_model->select_all("*", "settings")->result_array();

		if(count($result_array)== 1) {
			$result = $this->common_model->update_array(array('id'), 'settings', $data);
		}
		else{
			$result = $this->common_model->insert_array('settings', $data);
		}
			redirect('admin/settings', 'refresh');
	}

}

?>

