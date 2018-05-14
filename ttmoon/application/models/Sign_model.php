<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 签到系统
 */
class Sign_model extends CI_Model {

	/*****************************************************************************************************
	 * private 接口
	 *****************************************************************************************************/

	public function is_morning()
	{
		$this->load->helper('date');
		$time = time();
		$begin = mysql_to_unix(date('Y-m-d ', $time).'07:00:00');
		$end = mysql_to_unix(date('Y-m-d ', $time).'10:30:00');
		return $begin <= $time && $time <= $end;
	}

	public function is_afternoon()
	{
		$this->load->helper('date');
		$time = time();
		$begin = mysql_to_unix(date('Y-m-d ', $time).'14:00:00');
		$end = mysql_to_unix(date('Y-m-d ', $time).'16:10:00');
		return $begin <= $time && $time <= $end;
	}

	public function is_evening()
	{
		$this->load->helper('date');
		$time = time();
		$begin = mysql_to_unix(date('Y-m-d ', $time).'10:00:00');
		$end = mysql_to_unix(date('Y-m-d ', $time).'20:30:00');
		return $begin <= $time && $time <= $end;
	}

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

		//check label
		if ($this->is_morning())
		{
			$label = date('Y-m-d ', time()).'早上';
		}
		else if ($this->is_afternoon())
		{
			$label = date('Y-m-d ', time()).'下午';
		}
		else if ($this->is_evening())
		{
			$label = date('Y-m-d ', time()).'晚上';
		}
		else 
		{
			throw new Exception("当前非合法签到时间");
		}


		//check statu
		$where = array(
			"username" => $username,
			"label" => $label
			);
		if ($this->db->where($where)
			->get('sign_application')
			->result_array())
		{
			throw new Exception("已提交过 ".$label." 的签到申请，请联系签到负责人。");
		}

		//add to application list
		$data = array(
			"username" => $username,
			"label" => $label
			);
		$this->db->insert('sign_application', $data);
		throw new Exception("成功提交 ".$label." 的签到申请，请联系签到负责人。", 1);
		
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
		$result = $this->db->order_by('date', 'ASC')
			->get('sign_application')
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
