<?php
/**
 * Query return type definitions
 */
abstract class ResultReturnType
{
	const ResultSet = "resultset";
	const Arrays = "arrays";
	const Row = "row";
}




class Cassava_model extends CI_Model{
	public function __construct()
	{
		$this->load->database();
	}


	/**
	 * Get consolidated farmland data
	 * Tables: farmland_setup, cultutal_mngt, fertilizer_basal/top/side, pest_disease, production
	 */
	public function getplotdata($returnType = ResultReturnType::Arrays)
	{
		$sql = "SELECT farmland_setup._plotno, farmland_setup._year, farmland_setup._did, farmland_setup._fid, farmland_setup._userid, users.email, 
			cultural_mngt._02noplow, cultural_mngt._03noharrow, cultural_mngt._05terrain, cultural_mngt._07cropestmethod, cultural_mngt._08varietyplanted, 
			cultural_mngt._09pdist_prow, cultural_mngt._11pesticiderate, cultural_mngt._12freq, cultural_mngt._13dose, cultural_mngt._14pstcide_type, 
			cultural_mngt._15srctype, cultural_mngt._16srcname, 
			farmland_setup._06loc, farmland_setup._07pdate, farmland_setup._08hvdate, farmland_setup._09soil, farmland_setup._10eco,
			fertilizer_basal._01map AS BASAL_MAP, fertilizer_basal._02rate AS BASAL_RATE, fertilizer_basal._03type AS BASAL_TYPE, fertilizer_basal._04qty AS BASAL_QTY, 
			fertilizer_top._01map AS TOP_MAP, fertilizer_top._02rate AS TOP_RATE, fertilizer_top._03type AS TOP_TYPE, fertilizer_top._04qty AS TOP_QTY,  
			fertilizer_side._01map AS SIDE_MAP, fertilizer_side._02rate AS SIDE_RATE, fertilizer_side._03type AS SIDE_TYPE, fertilizer_side._04qty AS SIDE_QTY, 
			pest_disease._01muni, pest_disease._02brgy, pest_disease._03whiteflies, pest_disease._04mites, pest_disease._05mealybugs, 
			pest_disease._06witchb_symptoms, pest_disease._07cblight, pest_disease._08cmosaic, pest_disease._09anthracnose, pest_disease._10rot, 
			pest_disease._11growthstg, pest_disease._12areapl, pest_disease._13areainfect, pest_disease._14varietyinfect, pest_disease._15deg, 
			production._02hvmethod, production._03yieldhect, production._04rootspl, notes._01note
			
			FROM farmland_setup 

			LEFT JOIN cultural_mngt ON cultural_mngt._fid=farmland_setup._fid 
			LEFT JOIN fertilizer_basal ON fertilizer_basal._fid=farmland_setup._fid 
			LEFT JOIN fertilizer_top ON fertilizer_top._fid=farmland_setup._fid 
			LEFT JOIN fertilizer_side ON fertilizer_side._fid=farmland_setup._fid 
			LEFT JOIN pest_disease ON pest_disease._fid=farmland_setup._fid 
			LEFT JOIN production ON production._fid=farmland_setup._fid 
			LEFT JOIN notes ON notes._fid=farmland_setup._fid 
			LEFT JOIN users ON users.fb_id=farmland_setup._userid 

			WHERE farmland_setup._userid = cultural_mngt._userid 
			
			AND farmland_setup._fid = cultural_mngt._fid 
			AND farmland_setup._userid = fertilizer_basal._userid 
			AND farmland_setup._fid = fertilizer_basal._fid 
			AND farmland_setup._userid = fertilizer_top._userid 
			AND farmland_setup._fid = fertilizer_top._fid 
			AND farmland_setup._userid = fertilizer_side._userid 
			AND farmland_setup._fid = fertilizer_side._fid 
			AND farmland_setup._userid = pest_disease._userid 
			AND farmland_setup._fid = pest_disease._fid 
			AND farmland_setup._userid = production._userid 
			AND farmland_setup._fid = production._fid 
			AND farmland_setup._userid = notes._userid 
			AND farmland_setup._fid = notes._fid";

		// Return data following the format defined in ResultReturnType
		if($returnType == ResultReturnType::ResultSet){
			return $this->db->query($sql, false);
		}
		else if($returnType == ResultReturnType::Arrays){
			$query = $this->db->query($sql, false);
			return $query->result_array();
		}
		else if($returnType == ResultReturnType::Row){
			$query = $this->db->query($sql, false);
			return $query->row_array();
		}
	}	


	public function stripspaces($string){
		return str_replace(' ', '', $string);
	}


	/**
	 * Get raw farmland data and clean/modify it according to data specification
	 */
	public function getdata()
	{
		$result = $this->getplotdata(ResultReturnType::ResultSet);

		// Write the column headers


		foreach($result->result() as $row){
			// Create separate column fields for _06loc (longitude, latitude)
			if(strlen($row->_06loc) > 3){
				$gps = explode(",", $this->stripspaces($row->_06loc));
				$row->lat = $gps[0];
				$row->lon = $gps[1];
				$row->_06loc = "";
			}

			// Record the formatted raw data into a new array
			$output[] = $row;
		}

		return $output;
	}

}
?>