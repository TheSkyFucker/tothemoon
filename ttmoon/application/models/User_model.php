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
	/*private function create_token()
	{
		$this->load->helper('string');
		$token=random_string('alnum',30);
		while ($this->db->where(array('Utoken'=>$token))
			->get('user')
			->result_array());
		{
			$token=random_string('alnum',30);
		}
		return $token;
	}*/


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

		//
//		$form['Utoken'] = $this->create_token();
//		$this->db->insert('user', filter($form, $members_user));
//		$this->db->insert('user_info', filter($form, $members_info));
	}

}
