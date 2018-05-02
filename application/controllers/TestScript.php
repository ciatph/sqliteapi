<?php
class TestScript extends CI_Controller{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('cassava_model');
		$this->load->helper('url_helper');
    }
    

	// load the basic welcome page
	public function index()
	{
		$data['title'] =  "PHP Experiments";

		$this->load->view('templates/header', $data);
		$this->load->view('testscript/index', $data);
		$this->load->view('templates/footer');				
    }    
    

    /**
     * Testing playground for PHP strings demos
     */
    public function strings(){
        echo "strings test<br>";
        $msg = "Hello, world!";

        // 01: Replace all 'o' and 'l' with "?"
        //$msg = str_replace(['o', 'l'], "?", $msg);

        // 02: Find "," and echo all string before it
        // echo substr($msg, 0, strpos($msg, ","));
        echo str_replace(substr($msg, 0, strpos($msg, ",") + 1), "", $msg);
    }
}
?>