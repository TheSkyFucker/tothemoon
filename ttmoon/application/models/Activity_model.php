<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 活动管理
 */
class Activity_model extends CI_Model {

	/*****************************************************************************************************
	 * private 接口
	 *****************************************************************************************************/

	private function is_date($date)
	{
		if ( ! $temp = strtotime($date))
		{
			return FALSE;
		}
		$temp = date('Y-m-d H:i:s', $temp);
		return $date == $temp;
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
	public function register($form)
	{
		//config
		$level_limit = 10;

		//check user
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);
		$organizer = array();
		$creator = array(
			'username' => $username,
			'role' => '创建者'
			);
		array_push($organizer, $creator);
		$form['organizer'] = json_encode($organizer);

		//check begin && end
		if ( ! $this->is_date($form['begin']))
		{
			throw new Exception("开始时间不符合格式 xxxx-xx-xx xx:xx:xx");
		}
		if ( ! $this->is_date($form['end']))
		{
			throw new Exception("结束时间不符合格式 xxxx-xx-xx xx:xx:xx");
		}

		//check title
		$where = array('title' => $form['title']);
		if ($this->db->where($where)
			->get('activity_base')
			->result_array())
		{
			throw new Exception("已有该标题活动");
		}

		//do create
		$data = $form;
		$this->db->insert('activity_base', $form);
		throw new Exception("活动 ".$form['title']." 创建成功", 1);
		
	}

}
