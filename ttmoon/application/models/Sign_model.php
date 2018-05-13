<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 签到系统
 */
class Sign_model extends CI_Model {

	/*****************************************************************************************************
	 * private 接口
	 *****************************************************************************************************/


	/**********************************************************************************************
	 * public 接口
	 **********************************************************************************************/


	/**********************************************************************************************
	 * 接口 for 前端
	 **********************************************************************************************/


	/**
	 * 注册
	 */
	public function register()
	{

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, 0);

		//check statu
		$where = array("username" => $username);
		if ($this->db->where($where)
			->get('sign_application')
			->result_array())
		{
			throw new Exception("您已提交申请，请联系签到负责人。");
		}

		//add to application list
		$data = array('username' => $username);
		$this->db->insert('sign_application', $data);
	}

}
