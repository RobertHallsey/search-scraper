<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Robert Hallsey <rhallsey@yahoo.com>
 * @copyright 2016 Robert Hallsey
 * @license GPL v 3.0
 *
 * Searches controller
 *
 */
class Searches extends CI_Controller {

protected $searches = array();
protected $sites = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('download');
		$this->load->library('form_validation');
		$this->load->library('table');
		$this->load->model('searches_model');
		$this->searches = $this->searches_model->get_searches();
		$this->sites = $this->searches_model->get_sites();
		$this->load->driver('scraper', array_column($this->sites, 'driver'));
	}

/**
 * The index method is also the add method
 *
 * @param none
 * @return none
 */
	public function index()
	{
		$this->form_validation->set_rules('search[name]', 'Search Name', 'required');
		$this->form_validation->set_rules('search[site_id]', 'Site', 'required');
		$this->form_validation->set_rules('search[url]', 'Search URL', 'required');
		if ($this->form_validation->run())
		{
			$data = $this->input->post('search', TRUE);
			$search_id = $this->searches_model->add_search($data);
			redirect();
		}
		$data = array(
			'subview' => 'search_add_view',
			'sites' => $this->sites,
			'searches' => $this->searches
		);
		$this->load->view('searches_view', $data);
	}

/**
 * Edit a search
 *
 * @param integer search id to edit
 * @return none
 */
	public function edit($search_id)
	{
		$search = $this->searches_model->get_search($search_id);
		$this->form_validation->set_rules('search[name]', 'Search Name', 'required');
		$this->form_validation->set_rules('search[site_id]', 'Site', 'required');
		$this->form_validation->set_rules('search[url]', 'Search URL', 'required');
		if ($this->form_validation->run())
		{
			$data = $this->input->post('search', TRUE);
			$this->searches_model->update_search($search_id, $data);
			redirect();
		}
		$data = array(
			'subview' => 'search_edit_view',
			'sites' => $this->sites,
			'searches' => $this->searches,
			'search' => $search
		);
		$this->load->view('searches_view', $data);
	}

/**
 * Delete a search
 *
 * @param integer search id to delete
 * @return none
 */
	public function delete($search_id)
	{
		$search = $this->searches_model->get_search($search_id);
		$search['id'] = $search_id;
		if ($this->input->server('REQUEST_METHOD') == 'POST')
		{
			$data = array('id' => $search_id);
			$this->searches_model->delete_search($data);
			redirect();
		}
		$data = array(
			'subview' => 'search_delete_view',
			'sites' => $this->sites,
			'searches' => $this->searches,
			'search' => $search
		);
		$this->load->view('searches_view', $data);
	}
	

/**
 * Execute a search
 *
 * @param integer search id to execute
 * @return none
 */
	public function execute($search_id)
	{
		$search = $this->searches_model->get_search($search_id);
		$this->form_validation->set_rules('search[url]', 'Search URL', 'required');
		if ($this->form_validation->run())
		{
			$search['url'] = $this->input->post('search[url]', true);
			$output = $this->scraper->scrape($search['url'], $search['driver']);
			force_download($search['name'] . '.csv', $output);
		}
		$data = array(
			'subview' => 'search_execute_view',
			'sites' => $this->sites,
			'searches' => $this->searches,
			'search' => $search
		);
		$this->load->view('searches_view', $data);
	}

}
