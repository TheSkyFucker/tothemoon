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
		//config
		$level_limit = 0;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);

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

	/**
	 * 获取签到列表
	 */
	public function application_list()
	{
		//config
		$level_limit = 10;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);

		//application list
		$result = $this->db->get('sign_application')
			->result_array();
		return $result;
	}

	/**
	 * 处理签到申请
	 */
	public function handle_application($form)
	{
		//config
		$level_limit = 10;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);

		//check id
		$where = array('id' => $form['id']);
		if ( ! $result = $this->db->where($where)
			->get('sign_application')
			->result_array())
		{
			throw new Exception("不存在该申请。");
		}

		//check result
		if ($form['result'] == 1)
		{
			echo "TODO 加入sign_log";
			$where = array('id' => $form['id']);
			$this->db->delete('sign_application', $where);
			throw new Exception("已通过", 1);
		}
		if ($form['result'] == 0)
		{
			$where = array('id' => $form['id']);
			$this->db->delete('sign_application', $where);
			throw new Exception("已拒绝", 1);
		}
		throw new Exception("处理结果只能为 0 or 1", 0);
		
	}

}
