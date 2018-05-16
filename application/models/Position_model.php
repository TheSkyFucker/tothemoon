<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 座位系统
 */
class Position_model extends CI_Model {

	/*****************************************************************************************************
	 * private 接口
	 *****************************************************************************************************/

	/**********************************************************************************************
	 * public 接口
	 **********************************************************************************************/

	public function log($where)
	{
		return $this->db->where($where)
			->order_by('date', 'DESC')
			->get('position_log')
			->result_array();
	}

	/**********************************************************************************************
	 * 接口 for 前端
	 **********************************************************************************************/

	/**
	 * 申请座位
	 */
	public function register($form)
	{
		//config
		$level_limit = 5;
		$position_min = 1;
		$position_max = 36;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);

		//check has position or not
		$where = array('username' => $username);
		if ($this->db->where($where)
			->get('position_user')
			->result_array())
		{
			throw new Exception("您已有位置。", 0);
		}

		//check owner
		if ($form['id'] < $position_min || $form['id'] > $position_max)
		{
			throw new Exception("编号错误，座位只有".$position_min."~".$position_max);
		}
		$where = array('id' => $form['id']);
		if ($this->db->where($where)
			->get('position_user')
			->result_array())
		{
			throw new Exception("该位置已属于他人", 0);
		}

		//do register
		$data = array(
			'id' => $form['id'],
			'username' => $username
			);
		$this->db->insert('position_user', $data);

		//success & log
		$log = array(
			'position_id' => $form['id'],
			'username' => $username,
			'result' => 1
			);
		$this->db->insert('position_log', $log);
		throw new Exception("申请成功，获得位置".$form['id'], 1);
		
	}

	/**
	 * 位置信息
	 */
	public function profile($form)
	{
		//all
		if ( ! $form['id'])
		{
			$results = $this->db->get('position_user')->result_array();
			return $results;
		}

		//one
		$where = array('id' => $form['id']);
		if ( ! $results = $this->db->where($where)
			->get('position_user')
			->result_array())
		{
			throw new Exception("位置".$form['id']."无记录", 0);
		}

		$position = $results[0];
		$position['history'] = array();
		$where = array('position_id' => $form['id']);
		$logs = $this->log($where);
		foreach ($logs as $log)
		{
			if ($log['result'] == 1)
			{
				$msg = substr($log['date'], 0, 11)." ".$log['username']." 获得了位置 ".$log['position_id'];
			}
			else if ($log['result'] == -1)
			{
				$msg = substr($log['date'], 0, 11)." ".$log['username']." 失去了位置 ".$log['position_id'];
			}
			else 
			{
				$msg = substr($log['date'], 0, 11)." "."未知结果，请联系管理员。";
			}
			array_push($position['history'], $msg);
		}
		return $position;

	}

}
