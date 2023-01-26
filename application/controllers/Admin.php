<?php
ob_start();
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

    public function index(){
        $this->load->view('admin/login');
    }

	public function dashboard(){
		$data['num_rows'] = $this->common_model->select_where_table_rows('*', 'users', array('type'=>'user'));
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
		$data['questions'] = $this->common_model->select_all_order_by('*', 'questions','id','ASC')->result_array();
        $this->load->view('admin/include/header');
        $this->load->view('admin/questions', $data);
        $this->load->view('admin/include/footer');
    }

	public function insert_question(){
		$json_options = 'NULL';
		$data['title'] = $this->input->post('q_title');
		$data['response_type'] = $this->input->post('res_type');
		$data['sub_title'] = $this->input->post('sub_title');
		if($_POST['res_type'] == 'open_text'){
			$data['text_length'] = $this->input->post('text_length');
		}
		if(!empty($_POST['q_options'])){
			$json_options = json_encode($this->input->post('q_options'));
		}
		$data['options'] = $json_options;
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
		$data['response_type'] = $this->input->post('res_type');
		$data['sub_title'] = $this->input->post('sub_title');
		if($_POST['res_type'] == 'open_text'){
			$data['text_length'] = $this->input->post('text_length');
		}
		if(!empty($_POST['q_options'])){
			$json_options = json_encode($this->input->post('q_options'));
		}
		$data['options'] = $json_options;
		$this->common_model->update_array(array('id'=> $id), 'questions', $data);

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
		$data['users'] = $this->common_model->select_where_ASC_DESC('*', 'users', array('type'=>'user'), 'id','ASC')->result_array();
        $this->load->view('admin/include/header');
        $this->load->view('admin/users', $data);
        $this->load->view('admin/include/footer');
    }

	public function users_response($id = null){
		$data['page_title'] = 'Users Response';
		$data['answers'] = $this->common_model->get_resposne_combo($id);
        $this->load->view('admin/include/header');
        $this->load->view('admin/answers_combo', $data);
        $this->load->view('admin/include/footer');
    }

	public function date_respose($user_id = null, $date = null){
		$data['page_title'] = 'Users Response';
		$data['answers'] = $this->common_model->get_date_vise_resposne($user_id, $date);
        $this->load->view('admin/include/header');
        $this->load->view('admin/answers', $data);
        $this->load->view('admin/include/footer');
    }

	public function delete_user($id){
		$this->common_model->delete_where(array('id'=> $id), 'users');
		redirect('admin/users_list'); 
	}

}

?>

