<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
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
	 * 主页
	 */
	public function home()
	{
		echo "TODO";
	}

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

			//get form
			$this->load->helper('form');
			if ( ! $form = get_post())
			{
				$this->load->view('signup.html');
				return;
			}
			$this->load->library('form_validation');

			//check form			
			$this->form_validation->set_data($form);
			$this->form_validation->set_rules($rules);
			if ( ! $this->form_validation->run())
			{
				foreach ($rules as  $rule)
				{
					if (form_error($rule['field']))
					{
						throw new Exception(strip_tags(form_error($rule['field'])));
					}
				}
			}


			//register
			$this->load->model('User_model','user');
			$this->user->register(filter($form, $members));
			
		}
		catch (Exception $e)
		{
			set_message('error', '失败', $e->getMessage());
			$this->load->view('signup.html');
			return;
		}

		//return
		set_message('success', '成功', '已提交申请');
		$this->load->view('signup.html');
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

			//get form
			$form = get_post();
			$this->load->helper('form');
			if ( ! $form)
			{
				$this->load->view('login.html');
				return;
			}

			//check form
			$this->load->library('form_validation');
			$this->form_validation->set_data($form);
			$this->form_validation->set_rules($rules);
			if ( ! $this->form_validation->run())
			{
				foreach ($rules as  $rule) 
				{
					if (form_error($rule['field']))
					{
						throw new Exception(strip_tags(form_error($rule['field'])));
					}
				}
				return;
			}

			//login
			$this->load->model('User_model','user');
			$data = $this->user->login(filter($form, $fields));

		}
		catch (Exception $e)
		{
			set_message('error', '失败', $e->getMessage());
			$this->load->view('login.html');
			return;
		}

		//return
		set_message('success', '成功', '登陆成功');
		echo "<script>window.location.href='home'</script>";
		;
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
