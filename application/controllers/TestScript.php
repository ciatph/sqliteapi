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
        // echo str_replace(substr($msg, 0, strpos($msg, ",") + 1), "", $msg);

        // 03: Check if single-item array can be split
        //$str = "hello";
        //$array = explode(",", $str);
        
        // 04: checking for splits of "1 bag: urea"
        /*
        $str = "1 bag: urea";
        $array = explode(",", $str);

        for($i=0; $i<count($array); $i++)
            echo "[" . $i . "]: " . $array[$i] . "<br>";
        */

        // 05: Test rtrim comma "," on last 
        // $str = "hello, world,";
        // echo rtrim($str, ",");

        // test on preg_replace
        $str = "hello, world! one(1), two(2), three(3)";
        echo preg_replace("/[^0-9,]/", "", $str);
        
        if(strpos(strtolower($str), "na") || strpos(strtolower($str), "n/a")){
            echo "HAS na!";
        }
        else{
            echo "HAS NO NA!";
        }
        
    }
}
?>