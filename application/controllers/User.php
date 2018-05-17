<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Utoken");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

defined('BASEPATH') OR exit('No direct script access allowed');


class User extends CI_Controller {


	/*****************************************************************************************************
	 * 接口函数 for all
	 *****************************************************************************************************/

	/*****************************************************************************************************
	 * 接口函数 for 前端
	 *****************************************************************************************************/

	/**
	 * 注册
	 */
	public function register()
	{

		//config
		$rules = array(
			array(
				'field' => 'username',
				'label' => '用户名',
				'rules' => 'required|min_length[3]|max_length[16]|alpha_dash'
				),
			array(
				'field' => 'password',
				'label' => '密码',
				'rules' => 'required|min_length[6]|max_length[16]'
				),
			array(
				'field' => 'passconf',
				'label' => '再次输入密码',
				'rules' => 'required|matches[password]'
				),
			array(
				'field' => 'realname',
				'label' => '姓名',
				'rules' => 'required|min_length[1]|max_length[10]'
				),
			array(
				'field' => 'sex',
				'label' => '性别',
				'rules' => 'required|min_length[1]|max_length[1]'
				),
			array(
				'field' => 'born',
				'label' => '出生日期',
				'rules' => 'required|min_length[1]|max_length[30]'
				),
			array(
				'field' => 'grade',
				'label' => '年级',
				'rules' => 'required|min_length[1]|max_length[2]'
				),
			array(
				'field' => 'college',
				'label' => '学院',
				'rules' => 'required|min_length[1]|max_length[30]'
				),
			array(
				'field' => 'major',
				'label' => '专业',
				'rules' => 'required|min_length[1]|max_length[30]'
				),
			array(
				'field' => 'student_id',
				'label' => '学号',
				'rules' => 'required|min_length[1]|max_length[30]'
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
			if ( ! $this->load->model('User_model','user')) 
			{
				throw new Exception("载入 User_model 错误, 请联系管理员。");
			}
			$this->user->register(filter($post, $members));
			
		}
		catch (Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '申请成功', array());

	}

	/**
	 * 登陆
	 */
	public function login() 
	{

		//config
		$rules = array(
			array(
				'field' => 'username',
				'label' => '用户名',
				'rules' => 'required'
				),
			array(
				'field' => 'password',
				'label' => '密码',
				'rules' => 'required'
				)
		);
		$fields = array();
		foreach ($rules as $rule) 
		{
			array_push($fields, $rule['field']);
		}

		//login
		try
		{

			//get post
			$post = get_post();

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($post);
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

			//过滤 && login
			$this->load->model('User_model','user');
			$data = $this->user->login(filter($post, $fields));

		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '登陆成功', $data);

	}

	/**
	 * 申请列表
	 */
	public function application_list()
	{
		//application_list
		try
		{
			$this->load->model('User_model','user');
			$data = $this->user->application_list();
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
	 * 处理申请
	 */
	public function handle_application()
	{
		//config
		$rules = array(
			array(
				'field' => 'username',
				'label' => '用户名',
				'rules' => 'required'
				),
			array(
				'field' => 'result',
				'label' => '处理结果',
				'rules' => 'required'
				)
		);

		//handle
		try
		{
			//get input
			$form = array(
				'username' => $this->input->get('username'),
				'result' => $this->input->get('result')
				);

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
			$this->load->model('User_model', 'user');
			$data = $this->user->handle_application($form);
		}
		catch(Exception $e)
		{
			output_data($e->getCode(), $e->getMessage(), array());
			return;
		}

		//return
		output_data(1, '处理成功', $data);

	}

	/**
	 * 用户信息
	 */
	public function profile()
	{
		try
		{
			$form = array('username' => $this->input->get('username'));
			$this->load->model('User_model', 'user');
			$data = $this->user->profile($form);
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
