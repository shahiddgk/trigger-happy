<?php
ob_start();
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
		$this->load->library('session');
		$this->load->library('stripe_lib');
	}

    public function index(){
        $this->load->view('admin/login');
    }

	public function dashboard(){
		$data['page_title'] = 'Dashboard';
		$data['num_rows'] = $this->common_model->select_where_table_rows('*', 'users', array('type'=>'user'));

		$data['subscriptions'] = $this->stripe_lib->getSubscribersList();

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

		$allowedTypes = ['pire', 'naq'];

		$questions = array_filter($questions, function($question) use ($allowedTypes) {
			return in_array($question['type'], $allowedTypes);
		});

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

		$sql = "SELECT * FROM users WHERE type IN ('admin', 'coach') AND email = ? AND password = ? AND status = 'active'";
		$query = $this->db->query($sql, array($email, sha1($password)));

		if ($query->num_rows() > 0) {
			$user = $query->row_array();

			$data = array(
				'user_logged_in'  =>  TRUE,
				'userid' => $user['id'],
				'usertype' => $user['type'],
				'username' => $user['name'],
				'useremail' => $user['email'],
				'userimage' => $user['image']
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
		
		$data['answers'] = $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$id,'type'=>'pire'), 'created_at' , 'DESC', 'DATE(created_at)')->result_array();
        $this->load->view('admin/include/header');
        $this->load->view('admin/date_group', $data);
        $this->load->view('admin/include/footer');
    }

	public function date_group_naq($id = null){
		$data['page_title'] = 'Date Group NAQ';
		
		$data['answers'] = $this->common_model->select_where_ASC_DESC_Group_by("*",'answers', array('user_id'=>$id, 'type'=>'naq'), 'created_at' , 'DESC', 'DATE(created_at)')->result_array();
        $this->load->view('admin/include/header');
        $this->load->view('admin/date_group_naq', $data);
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

	public function user_activity(){
		$data['page_title'] = 'User Activity';
		$data['users'] = $this->common_model->user_activity_report();
		$this->load->view('admin/include/header');
		$this->load->view('admin/user_activity_report', $data);
		$this->load->view('admin/include/footer');
	}
	public function naq_report() {
		$data['page_title'] = 'NAQ Reports';
	
		$start_date = $this->input->get('selectedStartDate'); // Get start date from URL parameter
		$end_date = $this->input->get('selectedEndDate');     // Get end date from URL parameter
	
		$data['naq_report'] = $this->common_model->get_naq_report($start_date, $end_date);
		$data['selected_start_date'] = $start_date;
		$data['selected_end_date'] = $end_date;
		$this->load->view('admin/include/header');
		$this->load->view('admin/naq_report', $data);
		$this->load->view('admin/include/footer');
	}

	public function export_csv() {
    $start_date = $this->input->get('selectedStartDate');
    $end_date = $this->input->get('selectedEndDate');
    $naq_report = $this->common_model->get_naq_report($start_date, $end_date);

    // Generate the file name
	if (!empty($start_date) && !empty($end_date)) {
		$filename = "naq-report-" . date('mdy', strtotime($start_date)) . "-" . date('mdy', strtotime($end_date)) . ".csv";
	} else {
		$filename = "naq-report.csv";
	}
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
	
		$output = fopen('php://output', 'w');
	
		$csv_headers = array(
			'Name',
			'NAQ Date',
		);
	
		if (!empty($naq_report) && !empty($naq_report[array_key_first($naq_report)]['questions_and_answers'])) {
			foreach ($naq_report[array_key_first($naq_report)]['questions_and_answers'] as $index => $qa) {
				$csv_headers[] = preg_replace('/^[^:]+:\s*/', '', strip_tags($qa['question_title']));
	
				if ($index == 56) {
					$csv_headers[] = 'Why Chosen Yes?';
				} elseif ($index == 57) {
					$csv_headers[] = 'Why Chosen Yes?';
				}
			}
		}
	
		// Add the "Score" header
		$csv_headers[] = 'Score';
	
		fputcsv($output, $csv_headers);
	
		// Add data rows
		foreach ($naq_report as $naq) {
			$csv_data = array(
				$naq['name'],
				$naq['naq_date'],
			);
	
			if (!empty($naq['questions_and_answers'])) {
				foreach ($naq['questions_and_answers'] as $index => $qa) {
					$options = strtolower($qa['options']);
	
					switch ($options) {
                        case 'never':
                            $option = '1-'.$options ;
                            break;
							case 'rarely':
							$option = '2-'.$options ;
							break;
							case 'often':
								$option = '3-'.$options ;
								break;
							case 'always':
								$option = '4-'.$options ;
								break;
							default:
								$option = $options ;
							}
										
					$csv_data[] = $option;

					if ($index == 56) {
						$csv_data[] = $qa['text'];
					} elseif ($index == 57) {
						$csv_data[] = $qa['text'];
					}
				}
			}
	
			// Add the score value to the data
			$csv_data[] = $naq['score'];
	
			fputcsv($output, $csv_data);
		}
	
		fclose($output);
	}	

	public function insert_feedback() {
		$shared_id = $this->input->post('shared_id');
		$param = $this->input->post('param');
		$message = $this->input->post('message');
		$condition = [
			'entity_id' => $shared_id,
			'type' => $param,
		];
		$chat_room = $this->common_model->select_where('id, sender_id', 'chat_room', $condition)->row();
	
		$data = array(
			'shared_id' => $chat_room->id,
			'sender_id' => $this->session->userdata('userid'),
			'receiver_id' => $chat_room->sender_id,
			'message' => $message,
		);
	
		$result = $this->common_model->insert_array('sage_feedback', $data);
	
		if ($result) {
			echo 'success';
		} else {
			echo 'error';
		}
	}
	
	// pire positive questions

	public function pire_positive(){
		$data['page_title'] = 'Pire Pos Questions List';
		$questions = $this->common_model->select_all_order_by('*', 'questions', 'id', 'ASC')->result_array();
	
		// Filter questions where 'type' is equal to 'pire_pos'
		$filteredQuestions = array_filter($questions, function($question) {
			return $question['type'] === 'pire_pos';
		});
	
		$data['questions'] = $filteredQuestions;
	
		$this->load->view('admin/include/header');
		$this->load->view('admin/pire_positive', $data);
		$this->load->view('admin/include/footer');
	}

	public function add_pire_positive(){
		$data['page_title'] = 'Pire Positive Question';

		$this->load->view('admin/include/header');
		$this->load->view('admin/add_pire_positive', $data);
		$this->load->view('admin/include/footer');
	}

	public function insert_pire_positive(){
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

		redirect('admin/pire_positive'); 
	}

	public function edit_pire_positive($id){

		$data['page_title'] = 'Edit Question';
		$data['question'] = $this->common_model->select_where("*" , 'questions', array('id'=> $id))->row_array();
		$this->load->view('admin/include/header');
        $this->load->view('admin/edit_pire_positive', $data);
        $this->load->view('admin/include/footer');
	}

	public function delete_pire_positive($id){

		$this->common_model->delete_where(array('id'=> $id), 'questions');
		redirect('admin/pire_positive'); 
	}

	public function update_pire_positive($id){
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
		$data['type'] = $this->input->post('question_for');
		$this->common_model->update_array(array('id' => $id), 'questions', $data);

		redirect('admin/pire_positive'); 
	}

	public function share_response($type = NULL, $sender_id = NULL) {
		if (!$sender_id) {
			redirect('admin/share_response');
		}
	
		$receiver_id = $this->session->userdata('userid');
	
		$where = array('receiver_id' => $receiver_id, 'sender_id' => $sender_id);
	
		if ($type) {
			$where['type'] = $type;
		}
	
		$chat_rooms = $this->common_model->select_where('*', 'chat_room', $where)->result_array();
		usort($chat_rooms, function ($a, $b) {
			return strtotime($b['created_at']) - strtotime($a['created_at']);
		});
	
		foreach ($chat_rooms as $key => $chat_room) {
			$sender_id = $chat_room['sender_id'];
			$sender_name = $this->common_model->select_where('name', 'users', array('id' => $sender_id))->row()->name;
			$chat_rooms[$key]['sender_name'] = $sender_name;
		}
	
		$data['chat_room'] = $chat_rooms;
	
		$this->load->view('admin/include/header');
		$this->load->view('admin/share_response', $data);
		$this->load->view('admin/include/footer');
	}
	
	public function response_detail() {
		$type = $this->input->get('type');
		$entity_id = $this->input->get('entity_id');
		
		if ($type == 'naq' || $type == 'pire') {
			$this->db->select('answers.options, answers.text, questions.title, answers.type');
			$this->db->from('answers');
			$this->db->join('questions', 'answers.question_id = questions.id', 'left');
			$this->db->where('answers.response_id', $entity_id);
			$query = $this->db->get();
			$data['response_data'] = $query->result_array();
		} elseif ($type == 'ladder') {
			$data['response_data'] = $this->common_model->select_where('*', 'ladder', array('response_id' => $entity_id))->result_array();
		}
	
		$this->db->select('sage_feedback.message, sage_feedback.sender_id,');
		$this->db->from('chat_room');
		$this->db->join('sage_feedback', 'chat_room.id = sage_feedback.shared_id', 'left');
		$this->db->where('chat_room.entity_id', $entity_id);

		$chat_message_query = $this->db->get();

		if ($chat_message_query->num_rows() > 0) {
			$data['chat_message'] = $chat_message_query->result();
		} else {
			$data['chat_message'] = array();
		}
	
		$data['param_type'] = $type;
		$data['entity_id'] = $entity_id;
	
		$this->load->view('admin/include/header');
		$this->load->view('admin/response_detail', $data);
		$this->load->view('admin/include/footer');
	}
	
	public function sage_list() {
		$chat_rooms = $this->common_model->select_where('*', 'chat_room', array('receiver_id' => $this->session->userdata('userid')))->result_array();
	
		$sage_list = [];
	
		foreach ($chat_rooms as $chat_room) {
			$sender_id = $chat_room['sender_id'];
			$sender_info = $this->common_model->select_where('name, email', 'users', array('id' => $sender_id))->row();
	
			if ($sender_info) {
				$sender_name = $sender_info->name;
				$sender_email = $sender_info->email;
	
				if (!isset($sage_list[$sender_id])) {
					$sage_list[$sender_id] = [
						'sender_name' => $sender_name,
						'sender_email' => $sender_email,
						'pire_count' => 0,
						'naq_count' => 0, 
						'ladder' => false,
					];
				}
	
				if ($chat_room['type'] == 'pire') {
					$sage_list[$sender_id]['pire_count']++;
				} elseif ($chat_room['type'] == 'naq') {
					$sage_list[$sender_id]['naq_count']++; 
				} elseif ($chat_room['type'] == 'ladder') {
					$sage_list[$sender_id]['ladder'] = true;
				}
			}
		}
	    $data['page_title'] = 'Sage List';
		$data['sage_list'] = $sage_list;
	
		$this->load->view('admin/include/header');
		$this->load->view('admin/sage_list', $data);
		$this->load->view('admin/include/footer');
	}
	
} 
?>