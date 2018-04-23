<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');


	/**
	 * A common sql query helper ported from BioTrack (android) sqlite query class
	 * @author madbarua 2017/11/22
	 */
	class QueryHelper_lib{
		protected $ci;

		public function __construct($params = array())
		{	
			$this->ci =& get_instance();
			$this->ci->load->helper('url');
			$this->ci->config->item('base_url');
			$this->ci->load->database();
		}	


		public function sayHello()
		{
			return "hello, angel!";
		}


    /**
     * Retrieve table field values given the following parameters
     * @param tableName     name of table to search
     * @param projection    Array[] string order of column fields to retrieve
     * @param selection     single table field to check against
     * @param selectionArgs single value to check against existing table field
     * @param sortOder      ASC or DESC
     * @param sortArg       table field on which to base sorting. Default is null
     * @return
     */
		public function search_data($tableName, $projection = array(), $selection, $selectionArgs, $sortOrder, $sortArg)
		{
			if(!empty($projection)){
				$project = implode("','", $projection);
				$project = trim($project, ",");
			}

			$fields =  (!empty($projection)) ? implode(",", $projection) : " * " ;

			$sql = "SELECT " . $fields . 
				" FROM " . $tableName . " WHERE " . $selection . "=" . $selectionArgs . "";

			$query = $this->ci->db->query($sql, false);	
			//$data['search_result'] = $query->result_array();

			$json_array = array();
			foreach ($query->result() as $result) {
				$json_array[] = $result;
			}

			//foreach ($data['search_result'] as $key => $value) {
				//echo $key . " = " . $value["_id"];
			//}

			// use json_encode() to interact directly with raw dataTabales
			// return json_encode($json_array);
			return $json_array;
		}



		/**
		 * Select all specified values joined from other tables
		 * @param tableName 		an array of tables for selection where table (1) is the main table
		 * @param fields 				an array of <tableName>.<field> which should be selected from joined tables
		 * @param on 						an array of identifying fields from a (LEFT JOIN) table in tableName[1..N]
		 * @param where 				an array of matching fields from the main (FROM) table to the (LEFT JOIN) tables
		 * @param selection 		an array of fields as a criteria for finding a match
		 * @param selectionArgs 	an array of values for each of the specified selection[]
		 */
		function searchdb($tableName = array(), $fields = array(), $on = array(), $where = array(), $selection = array(), $selectionArgs = array()){
			$_tableName = implode(",", $tableName);
			$_fields = implode("','", $fields);
			$_on = implode("','", $on);
			$_where = implode("','", $where);
			$_selection = implode("','", $selection);
			$_selectionArgs = implode("','", $selectionArgs);

			$i = 1;
			$sql = "SELECT " . $_fields . " FROM " . $_tableName[0];

			foreach($_tableName as $table){
				if($i > 0){
					$sql .= " LEFT JOIN " . $table . " ON " . $table . "." . $_on[$i] . ", ";
				}
				$i++;
			}

			$i=1;
			$sql .= " WHERE ";

			foreach($_where as $whereClause){
				if($i > 0){
					$sql .= $whereClause . " AND ";
				}
				$i++;
			}			

			echo $sql;
		}


		/**
		 * Select all column field and values from a table
		 * @param tableName     name of table to search
		 * @return JSON data of all of a table's column field values
		 */
		public function selectall($tableName)
		{
			$query = $this->ci->db->query('SELECT * FROM ' . $tableName, false);
			$json_array = array();
			foreach ($query->result() as $result) {
				$json_array[] = $result;
			}

			// use json_encode() to interact directly with raw dataTabales
			//return json_encode($json_array);
			return $json_array;		
		}


		/**
		 * Format an array[] of query results into (ajax) dataTables-required format
		 * @param res 	array[] of query results
		 * @return JSON encoded format or query results that can be read by dataTables
		 */
		public function encode_data($res = FALSE){
			$count = json_encode($res);	

			$settings = array(
				'draw' => 1,
				'recordsTotal' => count(json_decode($count, true)),
				'recordsFiltered' => count(json_decode($count, true)),
				'data' => $res
			);

			return json_encode($settings);					
		}


		public function custom_query($sql = FALSE)
		{
			$query = $this->ci->db->query($sql);
			$json_array = array();
			foreach ($query->result() as $result) {
				$json_array[] = $result;
			}

			return $json_array;
		}
	}
?>