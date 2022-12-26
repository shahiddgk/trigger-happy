<?php

      class User_model extends CI_model{

            function all(){
                  return $users = $this->db->get('users')->result_array(); // SELECT * FROM `users`
      
            }    

            function create($fromArray){
                  $this->db->insert('users', $fromArray);
                  $this->db->last_query(); exit;
            }

            function getuser($userId){
                  $this->db->where('user_id', $userId);
                  return $users = $this->db->get('users')->row_array();  // SELECT * FROM `users` where user_id = ?
            }

            function updateUser($userId, $fromArray){
                  $this->db->where('user_id', $userId);
                  $this->db->update('users', $fromArray);
            }

            function deleteUser($userId) {
                  $this->db->where('user_id', $userId);
                  $this->db->delete('users');
            }


            function articleList($limit, $offset){
                  $this->db->limit($limit, $offset);
                  return $this->db->get_where('users')->result_array();
            }

            // function num_rows(){
            //             $id = '4';
            //             $q = $this->db->select()
            //                         ->from('users')
            //                         ->where(['user_id'=> $id])
            //                         ->get();
            //             return $q->num_rows();
            // }
}

?>