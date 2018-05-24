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

	public function position($where)
	{
		if ( ! $results = $this->db->where($where)
			->get('position_user')
			->result_array())
		{
			return -1;
		}
		$position = $results[0]['id'];
		return $position;
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
		$position_min = 1;
		$position_max = 36;
		$role_limit = 5;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token);
		$this->user->check_role($username, $role_limit);

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
			'result' => 1,
			'date' => date('Y-m-d H:i:s', time())
			);
		$this->db->insert('position_log', $log);
		throw new Exception("申请成功，获得位置".$form['id'], 1);
		
	}

	/**
	 * 位置信息
	 */
	public function profile($form = null)
	{
		//config
		$ip_head = '59.77.134.';
		$ip_table = array(0, 
			38, 37, 39, 36, 40, 41, 
			26, 25, 27, 24, 28, 29, 
			14, 13, 15, 12, 16, 17,
			44, 43, 45, 42, 46, 47,
			32, 31, 33, 30, 34, 35,
			20, 19, 21, 18, 22, 23
			);

		//all
		if ( ! $form)
		{
			$results = $this->db->get('position_user')->result_array();
			for ($i = 1; $i <= 36; $i++)
			{
				$ret[$i] = array(
					'username' => '-',
					'ip' => $ip_head.$ip_table[$i]
					);
			}
			foreach ($results as $position)
			{
				$ret[$position['id']]['username'] = $position['username'];
			}
			return $ret;
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
