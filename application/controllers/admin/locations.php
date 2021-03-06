<?php
 /**
   @rudra shrestha - rusagar.com
 */

defined('BASEPATH') or die('No direct Script Access');
require_once APPPATH . 'libraries/Admin_controller.php';

class Locations extends Admin_Controller {	
	
	function __construct()
	{		
		parent::__construct();
		$this->restricted_pages = array('index', 'country_form');
		$this->load->model('location_model');
		$this->lang->load('location');
	}
	
	function index()
	{
		$this->template->set('title', 'Locations');
		$data['locations']	= $this->location_model->get_countries();
		
		$this->template->load('templates/admin/brainlight', 'admin/locations/countries', $data);
		
	}
	
	function organize_countries()
	{
		$countries	= $this->input->post('country');
		$this->location_model->organize_countries($countries);
	}
	
	function country_form($id = false)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
	
		
		$this->template->set('title', 'Locations');
		
		//default values are empty if the product is new
		$data['id']					= '';
		$data['name']				= '';
		$data['iso_code_2']			= '';
		$data['iso_code_3']			= '';
		$data['status']				= false;
		$data['postcode_required']	= false;
		$data['address_format']		= '';
		$data['tax']				= 0;

		if ($id)
		{	
			$country		= (array)$this->location_model->get_country($id);
			//if the country does not exist, redirect them to the country list with an error
			if (!$country)
			{
				$this->session->set_flashdata('error', lang('error_country_not_found'));
				redirect($this->config->item('admin_folder').'/locations');
			}
			
			$data	= array_merge($data, $country);
		}
		
		$this->form_validation->set_rules('name', 'lang:name', 'trim|required');
		$this->form_validation->set_rules('iso_code_2', 'lang:iso_code_2', 'trim|required');
		$this->form_validation->set_rules('iso_code_3', 'lang:iso_code_3', 'trim|required');
		$this->form_validation->set_rules('address_format', 'lang:address_format', 'trim');
		$this->form_validation->set_rules('postcode_required', 'lang:require_postcode', 'trim');
		$this->form_validation->set_rules('tax', 'lang:tax', 'trim|numeric');
		$this->form_validation->set_rules('status', 'lang:status', 'trim');		
	
		if ($this->form_validation->run() == FALSE)
		{
			$this->template->load('templates/admin/brainlight', 'admin/locations/country_form', $data);
		}
		else
		{
			$save['id']					= $id;
			$save['name']				= $this->input->post('name');
			$save['iso_code_2']			= $this->input->post('iso_code_2');
			$save['iso_code_3']			= $this->input->post('iso_code_3');
			$save['address_format']		= $this->input->post('address_format');
			$save['postcode_required']	= $this->input->post('postcode_required');
			$save['status'] 			= $this->input->post('status');
			$save['tax'] 				= $this->input->post('tax');

			$promo_id = $this->location_model->save_country($save);
			
			$this->session->set_flashdata('message', lang('message_saved_country'));
			
			//go back to the product list
			redirect('admin/locations');
		}
	}

	
	function delete_country($id = false)
	{
		if ($id)
		{	
			$location	= $this->location_model->get_country($id);
			//if the promo does not exist, redirect them to the customer list with an error
			if (!$location)
			{
				$this->session->set_flashdata('error', lang('error_country_not_found'));
				redirect('admin/locations');
			}
			else
			{
				$this->location_model->delete_country($id);
				
				$this->session->set_flashdata('message', lang('message_deleted_country'));
				redirect('admin/locations');
			}
		}
		else
		{
			//if they do not provide an id send them to the promo list page with an error
			$this->session->set_flashdata('error', lang('error_country_not_found'));
			redirect('admin/locations');
		}
	}
	
	function delete_zone($id = false)
	{
		if ($id)
		{	
			$location	= $this->location_model->get_zone($id);
			//if the promo does not exist, redirect them to the customer list with an error
			if (!$location)
			{
				$this->session->set_flashdata('error', lang('error_zone_not_found'));
				redirect('admin/locations');
			}
			else
			{
				$this->location_model->delete_zone($id);
				
				$this->session->set_flashdata('message', lang('message_deleted_zone'));
				redirect('admin/locations/zones/'.$location->country_id);
			}
		}
		else
		{
			//if they do not provide an id send them to the promo list page with an error
			$this->session->set_flashdata('error', lang('error_zone_not_found'));
			redirect($this->config->item('admin_folder').'/locations');
		}
	}
	
	function zones($country_id)
	{
		$data['countries']	= $this->location_model->get_countries();
		$data['country']	= $this->location_model->get_country($country_id);
		if(!$data['country'])
		{
			$this->session->set_flashdata('error', lang('error_zone_not_found'));
			redirect('admin/locations');
		}
		$data['zones']	= $this->location_model->get_zones($country_id);
		
		$this->template->set('title', sprintf(lang('country_zones'), $data['country']->name));

		$this->template->load('templates/admin/brainlight', 'admin/locations/country_zones', $data);	
	}
	
	function zone_form($id = false)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
	
		$data['countries']		= $this->location_model->get_countries();
	
		$this->template->set('title', lang('zone_form'));
		
		//default values are empty if the product is new
		$data['id']			= '';
		$data['name']		= '';
		$data['country_id']	= '';
		$data['code']		= '';
		$data['tax']		= 0;
		$data['status']		= false;
		
		if ($id)
		{	
			$zone		= (array)$this->location_model->get_zone($id);

			//if the country does not exist, redirect them to the country list with an error
			if (!$zone)
			{
				$this->session->set_flashdata('error', lang('error_zone_not_found'));
				redirect('admin/locations');
			}
			
			$data	= array_merge($data, $zone);
		}
		
		$this->form_validation->set_rules('country_id', 'Country ID', 'trim|required');
		$this->form_validation->set_rules('name', 'lang:name', 'trim|required');
		$this->form_validation->set_rules('code', 'lang:code', 'trim|required');
		$this->form_validation->set_rules('tax', 'lang:tax', 'trim|numeric');
		$this->form_validation->set_rules('status', 'lang:status', 'trim');		
	
		if ($this->form_validation->run() == FALSE)
		{
			$this->template->load('templates/admin/brainlight', 'admin/locations/country_zone_form', $data);	
		}
		else
		{
			$save['id']			= $id;
			$save['country_id']	= $this->input->post('country_id');
			$save['name']		= $this->input->post('name');
			$save['code']		= $this->input->post('code');
			$save['status'] 	= $this->input->post('status');
			$save['tax'] 		= $this->input->post('tax');

			$this->location_model->save_zone($save);
			
			$this->session->set_flashdata('message', lang('message_zone_saved'));
			//go back to the product list
			redirect('admin/locations/zones/'.$save['country_id']);
		}
	}
	
	function get_zone_menu()
	{
		$id	= $this->input->post('id');
		$zones	= $this->location_model->get_zones_menu($id);
		
		foreach($zones as $id=>$z):?>
		
		<option value="<?php echo $id;?>"><?php echo $z;?></option>
		
		<?php endforeach;
	}
	
	
	function zone_areas($id)
	{
		$data['zone']			= $this->location_model->get_zone($id);
		$data['areas']			= $this->location_model->get_zone_areas($id);
		

		$this->template->set('title', sprintf(lang('zone_areas_for'), $data['zone']->name));
		
		$this->template->load('templates/admin/brainlight', 'admin/locations/country_zone_areas', $data);
	}

	function delete_zone_area($id = false)
	{
		if ($id)
		{	
			$location	= $this->location_model->get_zone_area($id);
			//if the promo does not exist, redirect them to the customer list with an error
			if (!$location)
			{
				$this->session->set_flashdata('error', lang('error_zone_area_not_found'));
				redirect('admin/locations');
			}
			else
			{
				$this->location_model->delete_zone_area($id);
				
				$this->session->set_flashdata('message', lang('message_deleted_zone_area'));
				redirect('admin/locations/zone_areas/'.$location->zone_id);
			}
		}
		else
		{
			//if they do not provide an id send them to the promo list page with an error
			$this->session->set_flashdata('error', lang('error_zone_area_not_found'));
			redirect('admin/locations/');
		}
	}
		
	function zone_area_form($zone_id, $area_id =false)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');

		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		
		$zone					= $this->location_model->get_zone($zone_id);
		$data['page_title']		= sprintf(lang('zone_area_form'), $zone->name);

		//default values are empty if the product is new
		$data['id']			= '';
		$data['code']		= '';
		$data['zone_id']	= $zone_id;
		$data['tax']		= 0;

		if ($area_id)
		{	
			$area	= (array)$this->location_model->get_zone_area($area_id);

			//if the country does not exist, redirect them to the country list with an error
			if (!$area)
			{
				$this->session->set_flashdata('error', lang('error_zone_area_not_found'));
				redirect('admin/locations/zone_areas/'.$zone_id);
			}

			$data	= array_merge($data, $area);
		}

		$this->form_validation->set_rules('code', 'lang:code', 'trim|required');
		$this->form_validation->set_rules('tax', 'lang:tax', 'trim|numeric');

		if ($this->form_validation->run() == FALSE)
		{
			$this->template->load('templates/admin/brainlight', 'admin/locations/country_zone_area_form', $data);	
		}
		else
		{
			$save['id']			= $area_id;
			$save['zone_id']	= $zone_id;
			$save['code']		= $this->input->post('code');
			$save['tax'] 		= $this->input->post('tax');

			$this->location_model->save_zone_area($save);

			$this->session->set_flashdata('message', lang('message_saved_zone_area'));

			//go back to the product list
			redirect('admin/locations/zone_areas/'.$save['zone_id']);
		}
	}
}