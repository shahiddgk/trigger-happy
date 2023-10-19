<?php if ( ! defined('BASEPATH')) exit ('No direct script  allow'); 

class Common_model extends  CI_Model {
	
	function select_all($select,$table)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		return $this->db->get();
	}
	
	// function model_lisitng()
	// {
	// 	 $sql = "SELECT id, priority, name, language, table_id, 
	// 	 (SELECT name FROM brands b WHERE language = 'eng' AND b.table_id=m.brand_id)
	// 	  AS brand_name FROM models m WHERE 1 = 1 ORDER BY priority ASC"; 
	// 	return $this->db->query($sql);

	function select_all_order_by($select,$table,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		return $this->db->get();
	}

	function select_where($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		return $this->db->get();
	}

	
	// function select_group($select,$table,$where,$group)
	// {
	// $this->db->select($select);
	// $this->db->from($table);
 	// $this->db->group_by($group);  
 	// $this->db->get();
	// }
	function select_groupby($select,$table,$groupby)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		
		$this->db->group_by( $groupby ); 
		return $this->db->get();
	}
	
	function select_distinct($select,$table,$where)
	{	
		$this->db->distinct($select);
		$this->db->from( $table );
		$this->db->where( $where );
		return $this->db->get();
	}
	
	
	function select_where_groupby($select,$table,$where,$groupby)
	{	
		$this->db->select($select);
		$this->db->from( $table );
		$this->db->where($where);
		$this->db->group_by($groupby);
		return $this->db->get();
	}
	
	function select_single_field($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$qry = $this->db->get();
		if($qry->num_rows()>0)
		{
			$rr	=	$qry->row_array();
			return	$rr[$select];
		}
		else
		{
			return '';
		}
	}
	
	function select_limit_order($select,$table,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_ASC_DESC( $select,$table,$where,$orderBy_columName,$ASC_DESC )
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_ASC_DESC_Group_by( $select,$table,$where,$orderBy_columName,$ASC_DESC,$group_by )
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->group_by($group_by);
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}

	
	function select_where_order($select,$table,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_wher_order($select,$table,$where,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_limit_order($select,$table,$where,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_table_rows($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();
	}
	
	
	function select_limit($select,$table,$page,$recordperpage)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->limit( $recordperpage , $page );
		$result=$this->db->get();
		return $result;	
		
	}

	function get_users_reponse_by_date($selectedDate)
	{
		$this->db->select('user_id, DATE(answers.created_at) AS cr_date, name, email');
		$this->db->from('answers');
		$this->db->join('users' , 'users.id = user_id');
		$this->db->where('DATE(answers.created_at)', $selectedDate);
		$this->db->group_by('user_id , DATE(answers.created_at)');
		$result = $this->db->get();
		return $result;
		
	}

	public function user_activity_report()
	{
		$ninetyDaysAgo = date('Y-m-d', strtotime('-90 days'));
	
		$sql = "SELECT
			users.id,
			users.name,
			users.created_at,
			users.level,
			SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'pire') AS count_pire,
			SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'trellis') AS count_trellis,
			SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'column') AS count_column,
			SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'ladder') AS count_ladder
		FROM
			users
		LEFT JOIN
			scores AS sc ON sc.user_id = users.id
		WHERE
			users.type = 'user'
			AND users.email != 'test@triggerhappy.com'
		GROUP BY
			users.id ";
	
		$query = $this->db->query($sql);
		$report_data = $query->result();
	
		foreach ($report_data as &$user_data) {
			$user_id = $user_data->id;
	
			$additional_data = $this->executeQuery(
				'reminders as rem',
				'DATE(rem.created_at) AS created_at,
				 SUM(rem.status = \'active\') AS sum_active_reminders,
				 SUM(rem.reminder_stop = \'yes\') AS sum_yes_reminders,
				 COUNT(rem.user_id) as sum_reminders',
				"user_id = $user_id AND DATE(rem.created_at) >= '$ninetyDaysAgo'"
			);
			$user_data->additional_data = $additional_data;
	
			$naq_score = $this->executeQuery(
				'naq_scores as naq',
				'DATE(MIN(naq.response_date)) AS min_naq_response,
				DATE(MAX(naq.response_date)) AS max_naq_response,
				MAX(naq.score) AS max_naq_score,
				MIN(naq.score) AS min_naq_score,
				CASE 
					WHEN MAX(naq.score) < MIN(naq.score) 
						THEN MAX(naq.score) - MIN(naq.score)
					ELSE 
						MIN(naq.score) - MAX(naq.score) 
				END AS delta',
				"user_id = $user_id"
			);
			$user_data->naq_score = $naq_score;

			$user_data->total_count = $this->select_where_groupby('*', 'scores', array('user_id' => $user_id , 'response_date >=' => $ninetyDaysAgo) , 'response_date')->num_rows();

		}
	
		if ($query) {
			return $query->result();
		} else {
			echo "Database error: " . $this->db->error();
			return array();
		}
	}
	
	
	public function api_user_activity_report($user_id) {
		
		$ninetyDaysAgo = date('Y-m-d', strtotime('-90 days'));

		$sql = "SELECT users.id, users.name, DATE(users.created_at) AS created_at, users.level,
		SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'pire') AS count_pire,
		SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'trellis') AS count_trellis,
		SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'column') AS count_column,
		SUM(sc.response_date >= '$ninetyDaysAgo' AND sc.type = 'ladder') AS count_ladder
		FROM users
		
		LEFT JOIN scores AS sc ON sc.user_id = users.id	
		WHERE users.id = $user_id
		GROUP BY users.id ";

        $query = $this->db->query($sql);
        $report_data = $query->result();
		foreach ($report_data as &$user_data) {
			$user_id = $user_data->id;
			$additional_data = $this->executeQuery(
				'reminders as rem',
				'DATE(rem.created_at) AS created_at,
				 SUM(rem.status = \'active\') AS sum_active_reminders,
				 SUM(rem.reminder_stop = \'yes\') AS sum_yes_reminders,
				 COUNT(rem.user_id) as sum_reminders',
				"user_id = $user_id AND DATE(rem.created_at) >= '$ninetyDaysAgo'"
			);
			$user_data->additional_data = $additional_data;

			$naq_score = $this->executeQuery(
				'naq_scores as naq',
				'DATE(MIN(naq.response_date)) AS min_naq_response,
				DATE(MAX(naq.response_date)) AS max_naq_response,
				MAX(naq.score) AS max_naq_score,
				MIN(naq.score) AS min_naq_score,
				CASE 
					WHEN MAX(naq.score) < MIN(naq.score) 
						THEN MAX(naq.score) - MIN(naq.score)
					ELSE 
						MIN(naq.score) - MAX(naq.score) 
				END AS delta',
				"user_id = $user_id"
			);
			$user_data->naq_score = $naq_score;
			
			$user_data->total_count = $this->select_where_groupby('*', 'scores', array('user_id' => $user_id , 'response_date >=' => $ninetyDaysAgo) , 'response_date')->num_rows();

		}
		
	    if ($query) {
			return $query->row_array();
	    } else {
			echo "Database error: " . $this->db->error();
			return array();
	    }
	}

	public function get_naq_report($start_date, $end_date) {

		if (!empty($start_date) && !empty($end_date)) {
			$where_date_filter = " AND DATE(answers.created_at) BETWEEN '$start_date' AND '$end_date'";
		} else {
			$where_date_filter = "";
		}

		$sql = "SELECT answers.response_id, DATE(answers.created_at) as naq_date, users.name,
				questions.id AS question_id, questions.title AS question_title,
				answers.options, answers.text, naq_scores.score
				FROM answers
				JOIN users ON users.id = answers.user_id
				JOIN questions ON questions.id = answers.question_id
				LEFT JOIN naq_scores ON naq_scores.response_id = answers.response_id
				WHERE answers.type = 'naq' $where_date_filter
				GROUP BY response_id, question_id";

		$query = $this->db->query($sql);
		$naq_records = $query->result_array();
	
		$result = [];
		foreach ($naq_records as $value) {
			$result[$value['response_id']]['name'] = $value['name'];
			$result[$value['response_id']]['naq_date'] = $value['naq_date'];
			$result[$value['response_id']]['score'] = $value['score'];
			$result[$value['response_id']]['questions_and_answers'][$value['question_id']] = [
				'question_title' => $value['question_title'],
				'options' => $value['options'],
				'text' => $value['text']
			];
		}
	
		return $result;
	}

	
	private function executeQuery($tableName, $columns, $where = "") {
		$sql = "SELECT $columns FROM $tableName";
		if (!empty($where)) {
			$sql .= " WHERE $where";
		}
		$query = $this->db->query($sql);
		return $query->result()[0];
	}
	
	function select_table_rows($select,$table)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$query=$this->db->get();
		return $query->num_rows();
	}
	
	
	
	function update_array($where,$table,$data)
	{
		$this->db->where( $where );
		$this->db->update( $table , $data);	
	}
	
	function insert_array($table,$data)
	{
		$this->db->insert( $table,$data );
		return $this->db->insert_id();	
	}
	
	function delete_where($where,$tbl_name)
	{
		$this->db->where($where);
		$this->db->delete($tbl_name);

	}
	
	function join_two_tab( $select , $from , $jointab , $condition, $orderBy_columName , $ASC_DESC ){
			$this->db->select( $select );
			$this->db->from( $from );
			$this->db->join( $jointab, $condition,'left' );
			$this->db->order_by( $orderBy_columName , $ASC_DESC );			
			return $this->db->get();
		
	}
	function join_two_tab_witout_left( $select , $from , $jointab , $condition, $orderBy_columName , $ASC_DESC ){
		$this->db->select( $select );
		$this->db->from( $from );
		$this->db->join( $jointab, $condition);
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		return $this->db->get();
	
}
	function join_two_tab_where( $select, $from, $jointable, $condition, $where, $recordperpage, $page, $orderBy_columName, $ASC_DESC ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition ,'left');
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );	
		return $this->db->get();

	}
	
	
	function select_two_tab_join_where( $select, $from, $jointable, $condition, $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition, 'left' );
		$this->db->where( $where );
		return   $this->db->get();
	}
	
	
	function select_or_like( $select,$table,$where,$orcondition,$recordperpage,$page,$orderBy_columName,$ASC_DESC ){
		$this->db->select( $select );
		$this->db->from( $table );
		//$this->db->like( $like );
		$this->db->or_like($orcondition); 
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		return $this->db->get();
	
	}
	
	function like_search( $select,$table,$where,$like,$orderBy_columName,$ASC_DESC ){
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->or_like($like); 
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		$this->db->where( $where );
		return $this->db->get();
	
	}
	
	
	function select_or_like_rows( $select,$table,$where,$orcondition ){
		$this->db->select( $select );
		$this->db->from( $table );
		//$this->db->like( $like );
		$this->db->or_like($orcondition); 		
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();
	
	}
	
	
	function join_tab_where( $select , $from , $jointab , $condition, $where, $orderBy_columName , $ASC_DESC ){
	
			$this->db->select( $select );
			$this->db->from( $from );
			$this->db->join( $jointab, $condition );
			$this->db->where( $where );
			$this->db->order_by( $orderBy_columName , $ASC_DESC );			
			return $this->db->get();
	}

	function join_tab_where_left( $select , $from , $jointab , $condition, $where, $orderBy_columName , $ASC_DESC ){
	
		$this->db->select( $select );
		$this->db->from( $from );
		$this->db->join( $jointab, $condition, 'left' );
		$this->db->where( $where );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		return $this->db->get();
	}
	
	function select_where_like($select,$table,$where_con,$where,$limit)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where_con );
		$this->db->like($where); 
		$this->db->limit($limit);
		return $this->db->get();
	}
	
	
	function join_three_tab_where( $select, $from, $jointable1, $condition1, $jointable2, $condition2,  $where, $recordperpage, $page, $orderBy_columName, $ASC_DESC ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable1 , $condition1 );
		$this->db->join( $jointable2 , $condition2 );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );	
		return $this->db->get();

	}
	
	function join_three_tables( $select, $from, $jointable1, $condition1, $jointable2, $condition2,  $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable1 , $condition1 );
		$this->db->join( $jointable2 , $condition2 );
		$this->db->where( $where );
		return 	$this->db->get();
	}
	
	
	
	function select_limit_by($select,$table,$where,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	
	function join_two_tab_where_numrows( $select, $from, $jointable, $condition, $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		return $this->db->get();

	}
	
	
	function select_limit_where($select,$table,$where,$page,$recordperpage)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$result=$this->db->get();
		return $result;	
		
	}
	
	
	function select_table_rows_where($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();
	}
	
	function join_two_tab_where_limit( $select, $from, $jointable, $condition,$where,$page,$recordperpage ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$query=$this->db->get();
		return $query;
	}
	
	function join_two_tab_where_simple( $select, $from, $jointable, $condition, $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		return $this->db->get();
	}
	
	
	function join_two_tab_where_groupby( $select, $from, $jointable, $condition, $where ,$group_by ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$this->db->group_by( $group_by );
		$query=$this->db->get();
		return $query;
	}
	
	
	function select_limit_order_where($select,$table,$where,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}

	public function getChatRoomData() {
		$this->db->select('chat_room.read_at, chat_room.chat_id, chat_room.receiver_id, users.name, users.image, chat_room.entry_text, chat_room.sender_id');
		$this->db->from('chat_room');
		$this->db->join('users', 'chat_room.sender_id = users.id');
		$this->db->group_by('chat_room.chat_id');

		$query = $this->db->get();
	
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
	}
	

    public function chat_messages($chat_id) {
        $this->db->select('chat_room.chat_id, chat_room.receiver_id, users.name, users.image, chat_room.entry_text, chat_room.sender_id, DATE_FORMAT(chat_room.created_at, "%h:%i %p") as created_at');
        $this->db->from('chat_room');
        $this->db->join('users', 'chat_room.sender_id = users.id');
        $this->db->where('chat_room.chat_id', $chat_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array();
        }
    }

	public function unread_messages_count($chat_id) {
		$this->db->select('COUNT(*) as count');
		$this->db->from('chat_room');
		$this->db->where('chat_room.chat_id', $chat_id);
		$this->db->where('chat_room.read_at', NULL);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
	}
 
	public function get_reminders($user_id, $current_time) {
		$currentdate = $current_time->format('Y-m-d');
		$data_time = $current_time->format('Y-m-d H:i:00');
	
		$this->db->select('*');
		$this->db->from('reminders r');
		$this->db->where('r.user_id', $user_id);
		$this->db->where('r.date_time', $data_time);
		$this->db->where('NOT EXISTS (SELECT 1 FROM reminder_history rh WHERE r.id = rh.entity_id AND DATE(rh.created_at) = ' . $this->db->escape($currentdate) . ')');
	
		$query = $this->db->get();
	
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return array();
		}
	}
}
?>