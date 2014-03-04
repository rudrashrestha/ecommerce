<?php
 /**
 * Products (Admin) - Controller
   @rudra shrestha - rusagar.com
 */

defined('BASEPATH') or die('No direct Script Access');
require_once APPPATH . 'libraries/Admin_controller.php';

class Products extends Admin_Controller {	
	
	private $use_inventory = false;
	
	function __construct()
	{		
		parent::__construct();
		$this->restricted_pages = array('index', 'form');		
		$this->load->model('product_model');
		$this->load->helper('form');
                $this->lang->load('product');
               
	}

	function index($order_by="name", $sort_order="ASC", $code=0, $page=0, $rows=15)
	{
        
		$this->template->set('title', 'Products');
		
		$data['code']		= $code;
		$term               = false;
		$category_id		= false;
		
		//get the category list for the drop menu
        $this->load->model('category_model');
		$data['categories']	= $this->category_model->get_categories_tierd();
		
		$post			= $this->input->post(null, false);
                
		$this->load->model('search_model');
		if($post)
		{
			$term       = json_encode($post);
			$code       = $this->search_model->record_term($term);
			$data['code']  = $code;
		}
		elseif ($code)
		{
			$term       = $this->search_model->get_term($code);
		}
		
		//store the search term
		$data['term']		= $term;
		$data['order_by']	= $order_by;
		$data['sort_order']	= $sort_order;
		
		$data['products']	= $this->product_model->products(array('term'=>$term, 'order_by'=>$order_by, 'sort_order'=>$sort_order, 'rows'=>$rows, 'page'=>$page));

		//total number of products
		$data['total']		= $this->product_model->products(array('term'=>$term, 'order_by'=>$order_by, 'sort_order'=>$sort_order), true);

		
		$this->load->library('pagination');
		
		$config['base_url']             = site_url('admin/products/index/'.$order_by.'/'.$sort_order.'/'.$code.'/');
		$config['total_rows']		= $data['total'];
		$config['per_page']		= $rows;
		$config['uri_segment']		= 7;
		$config['first_link']		= 'First';
		$config['first_tag_open']	= '<li>';
		$config['first_tag_close']	= '</li>';
		$config['last_link']		= 'Last';
		$config['last_tag_open']	= '<li>';
		$config['last_tag_close']	= '</li>';

		$config['full_tag_open']	= '<div class="pagination"><ul>';
		$config['full_tag_close']	= '</ul></div>';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close']	= '</a></li>';
		
		$config['num_tag_open']		= '<li>';
		$config['num_tag_close']	= '</li>';
		
		$config['prev_link']		= '&laquo;';
		$config['prev_tag_open']	= '<li>';
		$config['prev_tag_close']	= '</li>';

		$config['next_link']		= '&raquo;';
		$config['next_tag_open']	= '<li>';
		$config['next_tag_close']	= '</li>';
		
		$this->pagination->initialize($config);
		
		//$this->load->view($this->config->item('admin_folder').'/products', $data);
                $this->template->load('templates/admin/brainlight', 'admin/products/index', $data);	
	}
	
	//basic category search
	function product_autocomplete()
	{
		$name	= trim($this->input->post('name'));
		$limit	= $this->input->post('limit');
		
		if(empty($name))
		{
			echo json_encode(array());
		}
		else
		{
			$results	= $this->product_model->product_autocomplete($name, $limit);
			
			$return		= array();
			
			foreach($results as $r)
			{
				$return[$r->id]	= $r->name;
			}
			echo json_encode($return);
		}
		
	}
	
	function bulk_save()
	{
		$products = $this->input->post('product');
		
		if(!$products)
		{
			$this->session->set_flashdata('error',  'Error occured');
			redirect('admin/products');
		}
				
		foreach($products as $id=>$product)
		{
			$product['id']	= $id;
			$this->product_model->save($product);
		}
		
		$this->session->set_flashdata('message', 'Products updated successfully.');
		redirect('admin/products');
	}
	
	function form($id = false, $duplicate = false)
	{
		$this->product_id	= $id;
		$this->load->library('form_validation');
		$this->load->model(array('option_model', 'category_model', 'digital_product_model'));
		$this->lang->load('digital_product');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		
		//$data['categories']		= $this->Category_model->get_categories_tierd();
		//$data['product_list']	= $this->Product_model->get_products();
		$data['file_list']		= $this->digital_product_model->get_list();

                $this->template->set('title', 'Products');
                
		//default values are empty if the product is new
		$data['id']			= '';
		$data['sku']                    = '';
		$data['name']			= '';
		$data['slug']			= '';
		$data['description']		= '';
		$data['excerpt']		= '';
		$data['price']			= '';
		$data['saleprice']		= '';
		$data['weight']			= '';
		$data['track_stock'] 		= '';
		$data['seo_title']		= '';
		$data['meta']			= '';
		$data['shippable']		= '';
		$data['taxable']		= '';
		$data['fixed_quantity']		= '';
		$data['quantity']		= '';
		$data['enabled']		= '';
		$data['featured']		= '';
		$data['related_products']	= array();
		$data['product_categories']	= array();
		$data['images']			= array();
		$data['product_files']		= array();

		//create the photos array for later use
		$data['photos']		= array();

		if ($id)
		{	
			// get the existing file associations and create a format we can read from the form to set the checkboxes
			$pr_files 		= $this->digital_product_model->get_associations_by_product($id);
			foreach($pr_files as $f)
			{
				$data['product_files'][]  = $f->file_id;
			}
			
			// get product & options data
			$data['product_options']	= $this->option_model->get_product_options($id);
			$product			= $this->product_model->get_product($id);
			
			//if the product does not exist, redirect them to the product list with an error
			if (!$product)
			{
				$this->session->set_flashdata('error', 'The requested product could not be found.');
				redirect('admin/products');
			}
			
			//helps us with the slug generation
			$this->product_name	= $this->input->post('slug', $product->slug);
			
			//set values to db values
			$data['id']				= $id;
			$data['sku']				= $product->sku;
			$data['name']				= $product->name;
			$data['seo_title']			= $product->seo_title;
			$data['meta']				= $product->meta;
			$data['slug']				= $product->slug;
			$data['description']        = $product->description;
			$data['excerpt']			= $product->excerpt;
			$data['price']				= $product->price;
			$data['saleprice']			= $product->saleprice;
			$data['weight']				= $product->weight;
			$data['track_stock']        = $product->track_stock;
			$data['shippable']			= $product->shippable;
			$data['quantity']			= $product->quantity;
			$data['taxable']			= $product->taxable;
			$data['fixed_quantity']     = $product->fixed_quantity;
			$data['enabled']			= $product->enabled;
			$data['featured']			= $product->featured;
			
			//make sure we haven't submitted the form yet before we pull in the images/related products from the database
			if(!$this->input->post('submit'))
			{
				$data['product_categories']	= $product->categories;
				$data['related_products']	= $product->related_products;
				$data['images']			= (array)json_decode($product->images);
			}
		}
		
		//if $data['related_products'] is not an array, make it one.
		if(!is_array($data['related_products']))
		{
			$data['related_products']	= array();
		}
		if(!is_array($data['product_categories']))
		{
			$data['product_categories']	= array();
		}
		
		//no error checking on these
		$this->form_validation->set_rules('caption', 'Caption');
		$this->form_validation->set_rules('primary_photo', 'Primary');
                
                $this->form_validation->set_rules('name', 'Name', 'trim|required|max_length[64]');        
		$this->form_validation->set_rules('sku', 'SKU', 'trim|required');
		$this->form_validation->set_rules('seo_title', 'SEO Title', 'trim');
		$this->form_validation->set_rules('meta', 'Meta description', 'trim');		
		$this->form_validation->set_rules('slug', 'Slug', 'trim');
		$this->form_validation->set_rules('description', 'Description', 'trim|required');
		$this->form_validation->set_rules('excerpt', 'Excerpt', 'trim');
		$this->form_validation->set_rules('price', 'Price', 'trim|required|numeric|floatval');
		$this->form_validation->set_rules('saleprice', 'Sale price', 'trim|required|numeric|floatval');
		$this->form_validation->set_rules('weight', 'Weight', 'trim|numeric|floatval');
		$this->form_validation->set_rules('track_stock', 'Stock', 'trim|numeric');
		$this->form_validation->set_rules('quantity', 'Quantity', 'trim|numeric');
		$this->form_validation->set_rules('shippable', 'Shippable', 'trim|numeric');
		$this->form_validation->set_rules('taxable', 'Taxable', 'trim|numeric');
		$this->form_validation->set_rules('fixed_quantity', 'Quantity', 'trim|numeric');
		$this->form_validation->set_rules('enabled', 'Enabled', 'trim|numeric');
		$this->form_validation->set_rules('featured', 'Featured', 'trim|numeric');

		/*
		if we've posted already, get the photo stuff and organize it
		if validation comes back negative, we feed this info back into the system
		if it comes back good, then we send it with the save item
		
		submit button has a value, so we can see when it's posted
		*/
		
		if($duplicate)
		{
			$data['id']	= false;
		}
		if($this->input->post('submit'))
		{
			//reset the product options that were submitted in the post
			$data['product_options']	= $this->input->post('option');
			$data['related_products']	= $this->input->post('related_products');
			$data['product_categories']	= $this->input->post('categories');
			$data['images']			= $this->input->post('images');
			$data['product_files']		= $this->input->post('downloads');
			
		}
		
		if ($this->form_validation->run() == FALSE)
		{
                        $this->template->load('templates/admin/brainlight', 'admin/products/product_form', $data);
		}
		else
		{
			$this->load->helper('text');
			
			//first check the slug field
			$slug = $this->input->post('slug');
			
			//if it's empty assign the name field
			if(empty($slug) || $slug=='')
			{
				$slug = $this->input->post('name');
			}
			
			$slug	= url_title(convert_accented_characters($slug), 'dash', TRUE);
			
			//validate the slug
                        /*
			$this->load->model('Routes_model');

			if($id)
			{
				$slug		= $this->routes_model->validate_slug($slug, $product->route_id);
				$route_id	= $product->route_id;
			}
			else
			{
				$slug	= $this->Routes_model->validate_slug($slug);
				
				$route['slug']	= $slug;	
				$route_id	= $this->Routes_model->save($route);
			}
                         * */
                         

			$save['id']				= $id;
			$save['sku']				= $this->input->post('sku');
			$save['name']				= $this->input->post('name');
			$save['seo_title']			= $this->input->post('seo_title');
			$save['meta']				= $this->input->post('meta');
			$save['description']                    = $this->input->post('description');
			$save['excerpt']			= $this->input->post('excerpt');
			$save['price']				= $this->input->post('price');
			$save['saleprice']			= $this->input->post('saleprice');
			$save['weight']				= $this->input->post('weight');
			$save['track_stock']                    = $this->input->post('track_stock');
			$save['fixed_quantity']                 = $this->input->post('fixed_quantity');
			$save['quantity']			= $this->input->post('quantity');
			$save['shippable']			= $this->input->post('shippable');
			$save['taxable']			= $this->input->post('taxable');
			$save['enabled']			= $this->input->post('enabled');
			$post_images				= $this->input->post('images');
			
			$save['slug']				= $slug;
			
			
			if($primary	= $this->input->post('primary_image'))
			{
				if($post_images)
				{
					foreach($post_images as $key => &$pi)
					{
						if($primary == $key)
						{
							$pi['primary']	= true;
							continue;
						}
					}	
				}
				
			}
			
			$save['images']	= json_encode($post_images);
			
			
			if($this->input->post('related_products'))
			{
				$save['related_products'] = json_encode($this->input->post('related_products'));
			}
			else
			{
				$save['related_products'] = '';
			}
			
			//save categories
			$categories			= $this->input->post('categories');
			if(!$categories)
			{
				$categories	= array();
			}
			
			// format options
			$options	= array();
			if($this->input->post('option'))
			{
				foreach ($this->input->post('option') as $option)
				{
					$options[]	= $option;
				}

			}	
			
			// save product 
			$product_id	= $this->product_model->save($save, $options, $categories);
			
			// add file associations
			// clear existsing
			$this->digital_product_model->disassociate(false, $product_id);
			// save new
			$downloads = $this->input->post('downloads');
			if(is_array($downloads))
			{
				foreach($downloads as $d)
				{
					$this->digital_product_model->associate($d, $product_id);
				}
			}			
                        /*
			//save the route
			$route['id']	= $route_id;
			$route['slug']	= $slug;
			$route['route']	= 'cart/product/'.$product_id;
			
			$this->Routes_model->save($route);
			*/
			$this->session->set_flashdata('message', 'The product has been saved.');

			//go back to the product list
			redirect('admin/products');
		}
	}
	
	function product_image_form()
	{
		$data['file_name'] = false;
		$data['error']	= false;
		//$this->load->view($this->config->item('admin_folder').'/iframe/product_image_uploader', $data);
                $this->template->load('templates/admin/brainlight_side', 'admin/products/product_image_uploader', $data);
                
	}
	
	function product_image_upload()
	{
		$data['file_name'] = false;
		$data['error']	= false;
		
		$config['allowed_types'] = 'gif|jpg|png';
		//$config['max_size']	= $this->config->item('size_limit');
		$config['upload_path'] = 'uploads/images/full';
		$config['encrypt_name'] = true;
		$config['remove_spaces'] = true;

		$this->load->library('upload', $config);
		
		if ( $this->upload->do_upload())
		{
			$upload_data	= $this->upload->data();
			
			$this->load->library('image_lib');
			/*
			
			I find that ImageMagick is more efficient that GD2 but not everyone has it
			if your server has ImageMagick then you can change out the line
			
			$config['image_library'] = 'gd2';
			
			with
			
			$config['library_path']		= '/usr/bin/convert'; //make sure you use the correct path to ImageMagic
			$config['image_library']	= 'ImageMagick';
			*/			
			
			//this is the larger image
			$config['image_library'] = 'gd2';
			$config['source_image'] = 'uploads/images/full/'.$upload_data['file_name'];
			$config['new_image']	= 'uploads/images/medium/'.$upload_data['file_name'];
			$config['maintain_ratio'] = TRUE;
			$config['width'] = 600;
			$config['height'] = 500;
			$this->image_lib->initialize($config);
			$this->image_lib->resize();
			$this->image_lib->clear();

			//small image
			$config['image_library'] = 'gd2';
			$config['source_image'] = 'uploads/images/medium/'.$upload_data['file_name'];
			$config['new_image']	= 'uploads/images/small/'.$upload_data['file_name'];
			$config['maintain_ratio'] = TRUE;
			$config['width'] = 235;
			$config['height'] = 235;
			$this->image_lib->initialize($config); 
			$this->image_lib->resize();
			$this->image_lib->clear();

			//cropped thumbnail
			$config['image_library'] = 'gd2';
			$config['source_image'] = 'uploads/images/small/'.$upload_data['file_name'];
			$config['new_image']	= 'uploads/images/thumbnails/'.$upload_data['file_name'];
			$config['maintain_ratio'] = TRUE;
			$config['width'] = 150;
			$config['height'] = 150;
			$this->image_lib->initialize($config); 	
			$this->image_lib->resize();	
			$this->image_lib->clear();

			$data['file_name']	= $upload_data['file_name'];
		}
		
		if($this->upload->display_errors() != '')
		{
			//$data['error'] = $this->upload->display_errors();
                        $data['error'] = "You did not select a file to upload.";
		}
		//$this->load->view($this->config->item('admin_folder').'/iframe/product_image_uploader', $data);
                $this->template->load('templates/admin/brainlight_side', 'admin/products/product_image_uploader', $data);
	}
	
	function delete($id = false)
	{
		if ($id)
		{	
			$product	= $this->product_model->get_product($id);
			//if the product does not exist, redirect them to the customer list with an error
			if (!$product)
			{
				$this->session->set_flashdata('error','The requested product could not be found');
				redirect('admin/products');
			}
			else
			{

				

				//if the product is legit, delete them
				$this->product_model->delete_product($id);

				$this->session->set_flashdata('message', 'The product has been deleted.');
				redirect('admin/products');
			}
		}
		else
		{
			//if they do not provide an id send them to the product list page with an error
			$this->session->set_flashdata('error','The requested product could not be found');
			redirect('admin/products');
		}
	}
}
