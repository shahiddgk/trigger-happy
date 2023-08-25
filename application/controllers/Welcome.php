<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function privacy_policy(){
		$this->load->view('privacy_policy');
	}

	// public function refill_naqe_scores(){
	// 	$sql = "SELECT *
	// 	FROM answers
	// 	WHERE answers.type = 'naq' 
	// 	GROUP BY response_id ";
		    
	// 	$query = $this->db->query($sql);
	// 	$naq_records = $query->result_array();
	
	// 	// echo "<pre>"; print_r($naq_records); exit;
	// 	foreach ($naq_records as $key => $value) {

	// 		$response_id = $value['response_id'];

	// 		$response = $this->common_model->select_where("*", "answers", array('response_id' => $response_id , 'type' => 'naq'));
	
	// 		if ($response->num_rows() > 0) {
	// 			$answer_array = $response->result_array();
	
	// 			$total_score = array_reduce($answer_array, function ($acc, $value) {
	// 				$options = strtolower($value['options']);
	// 				$score = 0;
				
	// 				switch ($options) {
	// 					case 'never':
	// 						$score = 1;
	// 						break;
	// 					case 'rarely':
	// 						$score = 2;
	// 						break;
	// 					case 'often':
	// 						$score = 3;
	// 						break;
	// 					case 'always':
	// 						$score = 4;
	// 						break;
	// 				}
				
	// 				return $acc + $score;
	// 			}, 0);
				
	
	// 			$naq_score = array(
	// 				'user_id' => $value['user_id'],
	// 				'score' => $total_score,
	// 				'response_id' => $value['response_id'],
	// 				'level' => $value['level'],
	// 				'seed' => $value['seed'],
	// 				'response_date' => date('Y-m-d H:i:s')
	// 			);
	// 			$this->common_model->insert_array('naq_scores', $naq_score);
	// 		}else{
	// 			echo "Error";
	// 		}

	// 	}
	// }
}
