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
		//get data
		$this->load->model('Sign_model', 'sign');
		$this->session->set_userdata('3day', $this->sign->dashboard(3));
		$this->session->set_userdata('today', $this->sign->dashboard(1));;
		$this->session->set_userdata('sign_button', -1);
		if ($this->session->has_userdata('profile'))
		{
			$username = $this->session->userdata('profile')['username'];
			$this->session->set_userdata('sign_button', $this->sign->sign_statu($username));
		}
		$this->load->view('home.html');
	}

	/**
	 * 布局
	 */
	public function seat()
	{
		$this->load->model('Position_model', 'position');
		$this->session->set_userdata('position', $this->position->profile());
		$this->load->view('seat.html');
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
	 * 退出登陆
	 */
	public function logout( )
	{
		$this->session->sess_destroy();
		set_message('success', '成功', '退出成功');
		echo "<script>window.location.href='home'</script>";		
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
			$this->user->login(filter($form, $fields));

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
	}

	/**
	 * 申请列表
	 */
	public function application_list()
	{
		//handle
		try
		{
			$this->load->model('User_model','user');
			$form = array(
				'username' => $this->input->get('username'),
				'result' => $this->input->get('result')
				);
			if ($form['username'] != null && $form['result'] != null)
			{
				$this->user->handle_application($form);
			}

		}
		catch(Exception $e)
		{
			set_message($e->getCode() == 0 ? 'error' : 'success', $e->getCode() == 0 ? '失败' : '成功', $e->getMessage());
		}



		//get list
		try
		{
			$this->session->set_userdata('data', $this->user->application_list());
			$this->load->view('user_application_list.html');
		}
		catch(Exception $e)
		{
			set_message($e->getCode() == 0 ? 'error' : 'success', $e->getCode() == 0 ? '失败' : '成功', $e->getMessage());
			echo "<script>window.location.href='home'</script>";
		}
		
	}

	/**
	 * 用户信息
	 */
	public function profile()
	{
		try
		{		
			$form['username'] = $this->input->get('username');
			$this->load->model('User_model', 'user');
			$data = $this->user->profile($form);
			$username = $data['username'];
			$this->load->model('Sign_model', 'sign');
			$data['sign_history'] = $this->sign->history($username);
			$data['sign_logs'] = $this->sign->log_of_user($username);
			$this->session->set_userdata('data', $data);
		}
		catch (Exception $e)
		{
			set_message($e->getCode() == 0 ? 'error' : 'success', $e->getCode() == 0 ? '失败' : '成功', $e->getMessage());
			echo "<script>window.location.href='home'</script>";
		}
		$this->load->view('user_profile.html');
	}

	/**
	 * 修改信息
	 */
	public function setting()
	{
		//config
		$rules = array(
			array(
				'field' => 'qq',
				'label' => 'qq',
				'rules' => 'min_length[3]|max_length[16]|numeric'
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
				$this->load->view('user_setting.html');
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
			$this->user->setting(filter($form, $members));
			
		}
		catch (Exception $e)
		{
			set_message('error', '失败', $e->getMessage());
			$this->load->view('user_setting.html');
			return;
		}

		//return
		$where = array('username' => $this->session->userdata('profile')['username']);
		$this->session->set_userdata('profile', $this->user->profile($where));
		set_message('success', '成功', '修改成功	');
		$this->load->view('user_setting.html');
	}


	/**
	 * 上传头像
	 */
	public function upload_avatar()
	{
		if ($_SERVER['REQUEST_METHOD'] == "OPTIONS")
		{
			return;
		}

		//upload
		try
		{
			if ( ! $this->session->has_userdata('profile'))
			{
				throw new Exception("请登陆");
			}
			$username = $this->session->userdata('profile')['username'];

			//upload config
			$config['upload_path'] = './assets/uploads/user_avatar/';
			$config['allowed_types'] = 'jpg';
			$config['file_name'] = $username;
			$config['overwrite'] = TRUE;
			$config['max_size'] = 10000;
			$config['max_width'] = 2000;
			$config['max_height'] = 2000;
			$this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('userfile'))
        	{
        		throw new Exception($this->upload->display_errors());
	        }
    		else
        	{	
        		$data = array('upload_data' => $this->upload->data());
            	$this->load->model('User_model', 'user');
            	$where = array('username' => $username);
            	if ( ! $this->db->where($where)->get('user_avatar')->result_array())
            	{
            		$this->db->insert('user_avatar', $where);
            	}
        	}
		}
		catch(Exception $e)
		{
			set_message($e->getCode() == 0 ? 'error' : 'success', $e->getCode() == 0 ? '失败' : '成功', $e->getMessage());
			echo "<script>window.location.href='setting'</script>";
			return;
		}
		set_message('success', '成功', '上传成功');
		echo "<script>window.location.href='setting'</script>";
	}

}
