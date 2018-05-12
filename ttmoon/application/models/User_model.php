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
	/*private function is_timeout($last_visit)
	{
		$this->load->helper('date');
		$pre_unix = human_to_unix($last_visit);
		$now_unix = time();
		return $now_unix - $pre_unix > 10000;
	}*/


	/**********************************************************************************************
	 * public 接口
	 **********************************************************************************************/


	/**
	 * 检测凭据
	 */
	/*public function check_token($token) 
	{

		//不存在
		$where = array('Utoken' => $token);
		if ( ! $result = $this->db->select('Ulast_visit')
			->where(array('Utoken' => $token))
			->get('user')
			->result_array())
		{
			throw new Exception('会话已过期，请重新登陆', 401);
		}
		else
		{
			$user = $result[0];
			if ($this->is_timeout($user['Ulast_visit']))
			{
				throw new Exception('会话已过期，请重新登陆', 401);
			}
			else 
			{
				//刷新访问时间
				$new_data = array('Ulast_visit' => date('Y-m-d H:i:s',time()));
				$this->db->update('user', $new_data, $where);
			}
		}
	}*/


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
}
