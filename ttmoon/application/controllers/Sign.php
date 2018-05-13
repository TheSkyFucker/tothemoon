<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Utoken");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

defined('BASEPATH') OR exit('No direct script access allowed');


class Sign extends CI_Controller {


	/*****************************************************************************************************
	 * 接口函数 for all
	 *****************************************************************************************************/

	/*****************************************************************************************************
	 * 接口函数 for 前端
	 *****************************************************************************************************/

	/**
	 * 签到
	 */
	public function register()
	{

		//register
		try
		{	
			$this->load->model('Sign_model', 'sign');
			$this->sign->register();			
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '申请成功，请找签到负责人确认。', array());
	}

	/**
	 * 获取签到列表
	 */
	public function application_list()
	{
		//application_list
		try
		{
			$this->load->model('Sign_model','sign');
			$data = $this->sign->application_list();
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '获取成功', $data);		
	}

	/**
	 * 处理签到申请
	 */
	public function handle_application()
	{
		//config
		$rules = array(
			array(
				'field' => 'id',
				'label' => '申请编号',
				'rules' => 'required|numeric'
				),
			array(
				'field' => 'result',
				'label' => '处理结果',
				'rules' => 'required|numeric'
				)
		);

		//handle
		try
		{
			//get input
			foreach ($rules as $rule)
			{
				$field = $rule['field'];
				$form[$field] = $this->input->get($field);
			}

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($form);
			$this->form_validation->set_rules($rules);
			if ( ! $this->form_validation->run())
			{
				$this->load->helper('form');
				foreach ($rules as  $rule) 
				{
					if (form_error($rule['field']))
					{
						throw new Exception(strip_tags(form_error($rule['field'])));
					}
				}
				return;
			}
			$this->load->model('Sign_model', 'sign');
			$data = $this->sign->handle_application($form);
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '处理成功', $data);		
	}

}
