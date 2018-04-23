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
	 * Get the raw farmland plots data as raw, (unprocessed) JSON
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
}
?>