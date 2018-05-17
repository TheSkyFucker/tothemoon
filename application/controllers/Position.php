<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

defined('BASEPATH') OR exit('No direct script access allowed');


class Position extends CI_Controller {


	/*****************************************************************************************************
	 * 接口函数 for all
	 *****************************************************************************************************/

	/*****************************************************************************************************
	 * 接口函数 for 前端
	 *****************************************************************************************************/

	/**
	 * 申请位置
	 */
	public function register()
	{
		//config
		$rules = array(
			array(
				'field' => 'id',
				'label' => '申请编号',
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
			$this->load->model('Position_model', 'position');
			$data = $this->position->register($form);
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		echo "逻辑错误，联系后台。";
	}

	/**
	 * 位置信息
	 */
	public function profile()
	{
		//config
		$rules = array(
			array(
				'field' => 'id',
				'label' => '申请编号',
				'rules' => 'numeric'
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
			$this->load->model('Position_model', 'position');
			$data = $this->position->profile($form);
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '查询成功', $data);
	}

}
