<?php
//coder - rudra shrestha -- updated
class Cart extends Front_Controller {


	function __construct()
	{
		parent::__construct();
		
		//make sure we're not always behind ssl
		
	}

	function index()
	{
		$this->load->model(array('Banner_model'));
		$this->load->helper('directory');

		$data['homepage']			= true;
		
		//$this->load->view('homepage', $data);
		$this->template->load('templates/frontend/homepage', 'pages/home',$data);
	}

}
