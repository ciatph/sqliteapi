<?php
/**
 * Query return type definitions
 */
abstract class ResultReturnType
{
	const ResultSet = "resultset";
	const Arrays = "arrays";
	const Row = "row";
	const Json = "json";
}

const MAX_FERTILIZER_NAME = "14-14-14";
const FERTILIZERS = array(
	"14" => "14-14-14",
	"15" => "15-15-15",
	"16" => "16-20-0",
	"46" => "46-0-0"
);



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
		$sql = "SELECT farmland_setup._plotno, farmland_setup._year, farmland_setup._fid, users.email, 
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
		else if($returnType == ResultReturnType::Json){
			$query = $this->db->query($sql, false);
			$json_array = array();
			foreach($query->result() as $result){
				$json_array[] = $result;
			}
			return json_encode($json_array);
		}		
	}	


	/**
	 * Remove all spaces from a string
	 */
	public function stripspaces($string){
		return str_replace(' ', '', $string);
	}


	/**
	 * Clean the fertilizers field (type, rate quantity)
	 * Process multiple entries in (1) cell; items separated by commas
	 * Misc: transfer [fertilizer]_QTY "others" to [fertilizer_type]
	 * Remove all characters that precede "("
	 * Remove all "(" and ")"
	 * Replace all "&" with a comma ","
	 */
	public function cleanFertilizers($QTY, $TYPE, $RATE){
		if($QTY != ""){
			$TYPE = $QTY;

			$TYPE = str_replace(
				substr($TYPE, 0, strpos($TYPE, "(")), 
				"", $TYPE);

			// replace all "&" with commas ","
			$TYPE = str_replace(['&'], ",", $TYPE);		
			// Remove all "(", ")" and spaces
			$TYPE = str_replace(['(', ')'], "", $TYPE);	
			$TYPE = str_replace([' ,', ', '], ",", $TYPE);	

			// process multiple TOP_TYPES
			$type_arr = explode(",", $TYPE);

			$QTY = "";
			$TYPE = "";
			$RATE = "";

			for($i=0; $i<count($type_arr); $i++){
				// check for inner rate:type key-pairs
				$fertilizer = explode(":", $type_arr[$i]);

				if(count($fertilizer) > 1){
					if(strlen($fertilizer[1]) < strlen(MAX_FERTILIZER_NAME) && array_key_exists($fertilizer[1], FERTILIZERS))
						$fertilizer[1] = FERTILIZERS[trim($fertilizer[1])];
					
					$TYPE .= $fertilizer[1] . ",";
					$RATE .= preg_replace("/[^0-9.,]/", "",  $fertilizer[0]) . ",";
				}
				else{
					$fertilizer = explode(" ", $type_arr[$i]);
					if(count($fertilizer) > 1){
						if(strlen($fertilizer[1]) < strlen(MAX_FERTILIZER_NAME) && array_key_exists($fertilizer[1], FERTILIZERS))
							$fertilizer[1] = FERTILIZERS[trim($fertilizer[1])];

						$TYPE .= $fertilizer[1] . ",";
						$RATE .= preg_replace("/[^0-9.,]/", "",  $fertilizer[0]) . ",";
					}
				}
			}				
		} 

		$QTY = rtrim($QTY, ",");
		$RATE = rtrim($RATE, ",");
		return array($QTY, $TYPE, $RATE);
	}


	/**
	 * Turn the fertilizer Months After Application (MAP) into:
	 * - blank ("") if its na or n/a
	 * - 0 if its other alphabet strings (upon application, after application, etc)
	 */
	public function cleanFertilizerMAP($MAP){
		if(preg_match("/[a-z]/i", $MAP)){
			if(strpos(strtolower($MAP), "na") || strpos(strtolower($MAP), "n/a")){
				return "";
			}
			else{
				return 0;
			}
		}
		
		return preg_replace("/[^0-9.]/", "", $MAP);
	}
	
 
	/**
	 * Get raw farmland data and clean/modify it according to data specification
	 */
	public function getdata()
	{
		$result = $this->getplotdata(ResultReturnType::ResultSet);

		// Write the column headers
		$queryArr = $result->result();
		foreach($queryArr[0] as $key => $val)
		{
			$keys[] = $key;
		}

		// Insert new column names not in the original query
		array_push($keys, "lat", "lon", "pwidth", "pheight");

		// Process the body
		foreach($result->result() as $row){
			// 01. Create separate column fields for _06loc (longitude, latitude)
			if(strlen($row->_06loc) > 3 && strpos($row->_06loc, ",") !== false){
				$gps = explode(",", $this->stripspaces($row->_06loc));
				$row->lat = $gps[0];
				$row->lon = $gps[1];
				$row->_06loc = "";
			}
			else{
				// blank values
				$row->lat = "";
				$row->lon = "";
			}

			// 02. Create separate fields for _09pdist_prow (width, height)
			if(strpos(strtolower($row->_09pdist_prow) ,"x") !== false){
				$row->_09pdist_prow = preg_replace("/[^0-9,.xX]/", "", $row->_09pdist_prow);
				$size = explode("x", $this->stripspaces($row->_09pdist_prow));
				$row->width = $size[0];
				$row->height = $size[1];
			}
			else{
				$row->width = "";
				$row->height = "";
			}

			// 03. Remove metric units on applicable items
			$row->_02noplow = preg_replace("/[^0-9.,]/", "", $row->_09pdist_prow);
			$row->_03noharrow = preg_replace("/[^0-9.,]/", "", $row->_03noharrow);
			$row->_12freq = preg_replace("/[^0-9.,]/", "", $row->_12freq);
			$row->_11growthstg = preg_replace("/[^0-9.,]/", "", $row->_11growthstg);
			$row->_12areapl = preg_replace("/[^0-9.,]/", "", $row->_12areapl);
			$row->_04rootspl = preg_replace("/[^0-9.,]/", "", $row->_04rootspl);
			// yield: might need to normalize to kg/hec
			$row->_03yieldhect = preg_replace("/[^0-9.,]/", "", $row->_03yieldhect);
			// 04: Text values should default to 0: upon planting, before planting
			$row->BASAL_MAP = $this->cleanFertilizerMAP($row->BASAL_MAP);
			$row->BASAL_RATE = preg_replace("/[^0-9.,]/", "", $row->BASAL_RATE);
			$row->TOP_MAP = $this->cleanFertilizerMAP($row->TOP_MAP);
			$row->TOP_RATE = preg_replace("/[^0-9.,]/", "", $row->TOP_RATE);
			$row->SIDE_MAP = $this->cleanFertilizerMAP($row->SIDE_MAP);
			$row->SIDE_RATE = preg_replace("/[^0-9.,]/", "", $row->SIDE_RATE);

			// Clean Fertilizer BASAL
			if($row->BASAL_QTY != ""){
				$values = $this->cleanFertilizers($row->BASAL_QTY, $row->BASAL_TYPE, $row->BASAL_RATE);
				$row->BASAL_QTY = $values[0];
				$row->BASAL_TYPE = $values[1];
				$row->BASAL_RATE = $values[2];
			}

			// Clean Fertilizer TOP
			if($row->TOP_QTY != ""){
				$values = $this->cleanFertilizers($row->TOP_QTY, $row->TOP_TYPE, $row->TOP_RATE);
				$row->TOP_QTY = $values[0];
				$row->TOP_TYPE = $values[1];
				$row->TOP_RATE = $values[2];
			}

			// Clean Fertilizer SIDE
			if($row->SIDE_QTY != ""){
				$values = $this->cleanFertilizers($row->SIDE_QTY, $row->SIDE_TYPE, $row->SIDE_RATE);
				$row->SIDE_QTY = $values[0];
				$row->SIDE_TYPE = $values[1];
				$row->SIDE_RATE = $values[2];
			}

			// Record the formatted raw data into a new array
			$output[] = $row;
		}

		// Append the column name headers to the final array
		array_unshift($output, $keys);
		return $output;
	}
}
?>