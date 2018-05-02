<?php
class Cassava extends CI_Controller{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('cassava_model');
		$this->load->helper('url_helper');
	}



	// load the basic welcome page
	public function index()
	{
		$data['title'] =  "Cassava API Test";

		$this->load->view('templates/header', $data);
		$this->load->view('cassava/index', $data);
		$this->load->view('templates/footer');				
	}



	/**
	 * Route: Get the raw farmland plots data as raw, (unprocessed) JSON
	 */
	public function getdata($param = "json"){
		$parameter = $this->input->get('param');

		$param = ($parameter) ? $parameter : $param;
		echo $this->cassava_model->getplotdata($param);					
	}		



	/**
	 * Export all cassava data to CSV
	 * Uses file input/output stream
	 */
	public function export_csv_array(){
		header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=\"cassava_data".".csv\"");
        header("Pragma: no-cache");
        header("Expires: 0");

	  	// Open the write output stream
        $handle = fopen('php://output', 'w');

        // Get the cleaned cassava data
        $result = $this->cassava_model->getdata();

        $flag = false;
        foreach ($result as $row) {
		    // Convert stdClass $row object returned from query to normal array
		    $array = json_decode(json_encode($row), True);

		    // Write normalized row to CSV
        	fputcsv($handle, $array);
        }
        fclose($handle);
        exit;         
	}


	/** 
	 * Send a POST requst using cURL 
	 * @param string $url to request 
	 * @param array $post values to send 
	 * @param array $options for cURL 
	 * @return string 
	 */	
	public function get_remote($url, array $post = array(), array $options = array(), $timeout){		
		$defaults = array( 
			CURLOPT_POST => 1, 
			CURLOPT_HEADER => 0, 
			CURLOPT_URL => $url, 
			CURLOPT_FRESH_CONNECT => 1, 
			CURLOPT_RETURNTRANSFER => 1, 
			CURLOPT_FORBID_REUSE => 1, 
			CURLOPT_TIMEOUT => $timeout,	// no. of seconds to fetch data
			CURLOPT_POSTFIELDS => http_build_query($post) 
		);
		
		$ch = curl_init(); 
		
		curl_setopt_array($ch, ($options + $defaults)); 
		
		if( ! $result = curl_exec($ch)) 
		{ 
			// trigger_error(curl_error($ch)); 
			curl_close($ch); 
			return "TIMEOUT"; 
		} 
		
		curl_close($ch); 
		return $result; 
	}


	/**
	 * Route: Read the client-input url and get a POST data from it
	 * Default timeout is 360 sec (3 minutes)
	 */
	public function getremote($url = ""){
		$rurl = $this->input->get('url');
		$data = $this->get_remote($rurl, array(), array(), 360);

		if($rurl != ""){
			if($data != "TIMEOUT"){
				echo $data;
			}
			else{
				echo "<br>Timeout processing request.";
			}
		}
		else{
			echo "<br>Cannot process, URL is empty.";
		}
	}


	/**
	 * Route: Custom processing method that can call and wait for (1) or more remote URLS
	 */
	public function remote_model($url = ""){
		$url = "http://ciatph.000webhostapp.com/sqliteapi/cassava/getdata";
		$url2 = "http://ciatph.000webhostapp.com/bioweb/tree/genus";
		$data = $this->get_remote($url, array(), array(), 5);

		$msg1 = ($data != "TIMEOUT") ? "<br>LOADED DATA 1!<br><br>" : "<br>ERROR LOADING DATA 1!<br>";
		echo $msg1 . $data;
		
		$data2 = $this->get_remote($url2, array(), array(), 10);
		$msg2 = ($data2 != "TIMEOUT") ? "<br>LOADED DATA 2!<br><br>" : "<br>ERROR LOADING DATA 2!<br>";
		echo $msg2 . $data2;
	}
}
?>