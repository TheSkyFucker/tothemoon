<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 用户管理
 */
class User_model extends CI_Model {

	/*****************************************************************************************************
	 * private 接口
	 *****************************************************************************************************/


	/**
	 * 生成一个未被占用的Utoken
	 */
	private function create_token()
	{
		$this->load->helper('string');

		$token = random_string('alnum', 30);
		$where = array(
			'token' => $token
			);
		while ($this->db->where($where)
			->get('token_user')
			->result_array());
		{
			$token = random_string('alnum',30);
		}
		return $token;
	}


	/**
	 * 检测时间差
	 */
	private function is_timeout($last_visit)
	{
		$this->load->helper('date');
		$pre_unix = human_to_unix($last_visit);
		$now_unix = time();
		return $now_unix - $pre_unix > 10000;
	}


	/**********************************************************************************************
	 * public 接口
	 **********************************************************************************************/


	/**
	 * 检测凭据
	 */
	public function check_user($token, $level_limit = null) 
	{

		//check token
		$where = array('token' => $token);
		if ( ! $result = $this->db->where($where)
			->get('token_user')
			->result_array())
		{
			throw new Exception('会话已过期，请重新登陆', 401);
		}
		$user = $result[0];
		if ($this->is_timeout($user['last_visit']))
		{
			throw new Exception('会话已过期，请重新登陆', 401);
		}
		else 
		{
			//刷新访问时间
			$new_data = array('last_visit' => date('Y-m-d H:i:s',time()));
			$this->db->update('token_user', $new_data, $where);
		}

		//check level
		if (isset($level_limit))
		{
			$where = array('username' => $user['username']);
			if ( ! $result = $this->db->where($where)
				->get('manager_user')
				->result_array())
			{
				throw new Exception("只有管理员可以操作");
			}
			$manager = $result[0];
			if (time() > strtotime($manager['deadline']))
			{
				throw new Exception("当前不是您的管理员权限可使用的时间");
			}
			if ($manager['level'] < $level_limit)
			{
				throw new Exception("你的权限为".$manager['level'].", 该操作需要权限".$level_limit);
			}
		}

		return $user['username'];
	}

	public function check_role($username, $role_limit)
	{
		$where = array('username' => $username);
		$user = $this->db->where($where)
			->get('user_base')
			->result_array()[0];
		if ($user['role'] < $role_limit)
		{
			if ($role_limit == 5)
			{
				throw new Exception("只有正式成员可以进行该操作");
			}
			else
			{
				throw new Exception("未知角色要求");
			}
		}

	}


	/**********************************************************************************************
	 * 接口 for 前端
	 **********************************************************************************************/


	/**
	 * 注册
	 */
	public function register($user)
	{
		//check username
		$where = array('username' => $user['username']);
		if ($this->db->where($where)
			->get('user_base')
			->result_array())
		{
			throw new Exception('用户名已存在');
		}
		if ($this->db->where($where)
			->get('user_application')
			->result_array())
		{
			throw new Exception("该用户名已提交注册申请");
		}

		//check date
		if ( ! strtotime($user['born']))
		{
			throw new Exception("生日格式错误");
		}

		//add into application table
		$user_application = array(
			'username' => $user['username'],
			'form' => json_encode($user)
			);
		$this->db->insert('user_application', $user_application);

	}

	/**
	 * 登陆
	 */
	public function login($user)
	{
		//check user
		$where = array('username' => $user['username']);
		if ( ! $result = $this->db->where($where)
			->get('user_base')
			->result_array())
		{
			//check application list
			if ($this->db->where($where)
				->get('user_application')
				->result_array())
			{
				throw new Exception("账号审核中，请通知管理人员。");
			}
			throw new Exception('用户不存在');

		}
		$password = $result[0]['password'];
		if ($user['password'] != $password)
		{
			throw new Exception("密码错误");
		}


		//update token
		$where = array('username' => $user['username']);
		$new_data = array(
			'token' => $this->create_token(),
			'last_visit' => date('Y-m-d H:i:s', time())
			);
		if ( ! $result  = $this->db->where($where)
			->get('token_user')
			->result_array())
		{
			$new_data['username'] = $user['username'];
			$this->db->insert('token_user', $new_data);
		}
		else
		{
			$this->db->update('token_user', $new_data, $where);
		}

		//get profile
		$form = array('username' => $user['username']);
		$ret = $this->profile($form);
		$this->session->set_userdata('token', $new_data['token']);

	}

	/**
	 * 获取注册列表
	 */
	public function application_list()
	{
		//check user & level
		$token = get_token();
		$required_level = 10;
		$this->check_user($token, $required_level);

		//return list
		$applications = $this->db->get('user_application')
			->result_array();
		$list = array();
		foreach ($applications as $application)
		{
			$temp = json_decode($application['form'], true);
			unset($temp['password']);
			unset($temp['passconf']);
			array_push($list, $temp);
		}
		return $list;
	}


	/**
	 * 处理申请
	 */
	public function handle_application($form)
	{
		//check user & level
		$token = get_token();
		$required_level = 10;
		$this->check_user($token, $required_level);

		//check username
		$where = array('username' => $form['username']);
		if ( ! $application = $this->db->where($where)
			->get('user_application')
			->result_array())
		{
			throw new Exception("该用户名不在申请列表中");
		}
		$application = $application[0];

		//check result
		if ($form['result'] == 0)
		{
			$this->db->delete('user_application', $where);
			throw new Exception("拒绝成功", 1);
		}
		if ($form['result'] == 1)
		{

			$form = json_decode($application['form'], true);
			$form['register'] = date('Y-m-d H:i:s', time());
			$user_base = array('username', 'password', 'realname');
			$user_detail = array('username', 'sex', 'born', 'grade', 'college', 'major', 'student_id', 'register');
			$this->db->insert('user_base', filter($form, $user_base));
			$this->db->insert('user_detail', filter($form, $user_detail));
			$this->db->delete('user_application', $where);
			throw new Exception("通过成功", 1);
		}
		throw new Exception("处理结果只能为 0 or 1");
	}

	/**
	 * 用户信息
	 */
	public function profile($form)
	{
		//check form
		if ( ! $form['username'])
		{
			$token = get_token();
			$username = $this->check_user($token);
			$form['username'] = $username;
		}

		//check user
		$where = array('username' => $form['username']);
		if ( ! $user = $this->db->where($where)
			->get('user_base')
			->result_array())
		{
			throw new Exception("该用户不存在");
		}
		$user = $user[0];

		//get user.detail
		$user_detail = $this->db->where($where)
			->get('user_detail')
			->result_array()[0];
		$user = array_merge($user, $user_detail);
		unset($user['password']);

		//check sex
		if ($user['sex'] == 0)
		{
			$user['sex'] = '女';
		}
		else if ($user['sex'] == 1)
		{
			$user['sex'] = '男';
		}
		else
		{
			$user['sex'] = '?';			
		}

		//check manger
		$where = array('username' => $user['username']);
		if ( ! $results = $this->db->where($where)
			->get('manager_user')
			->result_array())
		{
			$user['is_manager'] = false;
		}
		else 
		{
			$manager = $results[0];
			if (strtotime($manager['deadline']) < time())
			{
				$user['is_manager'] = false;
			}
			else 
			{
				$user['is_manager'] = true;
				$user['level'] = $manager['level'];
			}
		}
		
		//get sign
		$this->load->model('Sign_model', 'sign');
		$sign_log = $this->sign->log($user['username']);
		$user['sign_history'] = array();
		foreach ($sign_log as $log)
		{
			if ($log['result'] == 1)
			{
				$msg = $log['label']." ".substr($log['date'], 11)." 签到成功";
			}
			else if ($log['result'] == 0)
			{
				$msg = $log['label']." ".substr($log['date'], 11)." 签到被无情拒绝";
			}
			else
			{
				$msg = "未知结果，请联系管理员。";
			}
			array_push($user['sign_history'], $msg);
		}
		$user['sign_statu'] = $this->sign->sign_statu($user['username']);

		//get position
		$this->load->model('Position_model', 'position');
		$where = array('username' => $user['username']);
		$user['position'] = $this->position->position($where);
		return $user;
	}
}
