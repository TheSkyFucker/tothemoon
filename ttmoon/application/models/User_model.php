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
	public function check_user($token, $level_limit) 
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
		$where = array('username' => $user['username']);
		$user = $this->db->where($where)
			->get('user_base')
			->result_array()[0];
		if ($user['role'] < $level_limit)
		{
			throw new Exception("你的权限为".$user['role'].", 该操作需要权限".$level_limit);
		}

		return $user['username'];
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
		$where = array(
			'username' => $user['username'],
			'password' => $user['password']
			);
		if ( ! $this->db->where($where)
			->get('user_base')
			->result_array())
		{
			throw new Exception('用户不存在');
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

		//return
		$ret = array('token' => $new_data['token']);
		return $ret;
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
			$user_base = array('username', 'password', 'realname');
			$user_detail = array('username', 'sex', 'born', 'grade', 'college', 'major', 'register');
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

		//get sign.history
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
		return $user;
	}
}
