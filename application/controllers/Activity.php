<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

defined('BASEPATH') OR exit('No direct script access allowed');


class Activity extends CI_Controller {


	/*****************************************************************************************************
	 * 接口函数 for all
	 *****************************************************************************************************/

	/*****************************************************************************************************
	 * 接口函数 for 前端
	 *****************************************************************************************************/

	/**
	 * 新建活动
	 */
	public function register()
	{

		//config
		$rules = array(
			array(
				'field' => 'title',
				'label' => '活动标题',
				'rules' => 'required|min_length[1]|max_length[100]'
				),
			array(
				'field' => 'begin',
				'label' => '活动开始时间',
				'rules' => 'required'
				),
			array(
				'field' => 'end',
				'label' => '活动结束时间',
				'rules' => 'required'
				),
			array(
				'field' => 'place',
				'label' => '活动地点',
				'rules' => 'required|min_length[1]|max_length[200]'
				)
		);
		$members = array();
		foreach ($rules as $rule) 
		{
			array_push($members, $rule['field']);
		}

		//register
		try
		{	

			//get post
			$post = get_post();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
			$this->form_validation->set_rules($rules);
			if ( ! $this->form_validation->run() )
			{
				$this->load->helper('form');
				foreach ($rules as $rule)
				{
					if (form_error($rule['field']))
					{
						throw new Exception(strip_tags(form_error($rule['field'])));
					}
				}
				return;
			}

			//过滤 && register
			$this->load->model('Activity_model', 'activity');
			$this->activity->register(filter($post, $members));
			
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		echo "若看到此信息，联系后台人员(activity.register).";

	}

	/**
	 * 获取活动列表
	 */
	public function list()
	{
		echo "hh";
	}
}