<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 签到系统
 */
class Sign_model extends CI_Model {

	/*****************************************************************************************************
	 * private 接口
	 *****************************************************************************************************/

	private function is_signable()
	{
		//config
		$time = time();
		$date = date('Y-m-d', $time);
		$options = array(
			array(
				'begin' => strtotime($date.' 08:00:00'),
				'end' => strtotime($date.' 09:30:00'),
				'label' => '早上'
				),
			array(
				'begin' => strtotime($date.' 13:00:00'),
				'end' => strtotime($date.' 13:50:00'),
				'label' => '下午'
				),
			array(
				'begin' => strtotime($date.' 18:30:00'),
				'end' => strtotime($date.'19:30:00'),
				'label' => '晚上'
				)
			);

		//check		
		$this->load->helper('date');
		foreach ($options as $option)
		{
			if ($option['begin'] <= $time && $time <= $option['end'])
			{
				return $date.' '.$option['label'];
			}
		}
		return null;
	}

	private function update_visit($data)
	{
		//config
		$username = $data['username'];
		$label = $data['label'];

		//sign before
		$where = array('username' => $username);
		if ( ! $sign_user = $this->db->where($where)
			->get('sign_user')
			->result_array())
		{
			$sign_user = array(
				'username' => $username,
				'last_sign' => $label,
				'begin_sign' => $label
				);
			$this->db->insert('sign_user', $sign_user);
			return;
		}
		$sign_user = $sign_user[0];

		//check last_visit
		$pre_unix = mysql_to_unix(substr($sign_user['last_sign'], 0, 11)."00:00:00");
		$now_unix = mysql_to_unix(substr($label, 0, 11)."00:00:00");
		if ($now_unix - $pre_unix > 86400)
		{
			$sign_user['begin_sign'] = $label; 
		}
		$sign_user['last_sign'] = $label;

		//update sign.user
		$where = array('username' => $sign_user['username']);
		unset($sign_user['username']);
		$this->db->update('sign_user', $sign_user, $where);
	
	}

	private function later($label1, $label2)
	{
		$day1 = strtotime(substr($label1, 0, 10));
		$day2 = strtotime(substr($label2, 0, 10));
		$time1 = substr($label1, 11);
		if ($time1 == '早上')
		{
			$time1 = 1;
		}
		else if ($time1 == '下午')
		{
			$time1 = 2;
		}
		else if ($time1 == '晚上')
		{
			$time1 = 3;
		}
		$time2 = substr($label2, 11);
		if ($time2 == '早上')
		{
			$time2 = 1;
		}
		else if ($time2 == '下午')
		{
			$time2 = 2;
		}
		else if ($time2 == '晚上')
		{
			$time2 = 3;
		}
		return $day1 == $day2 ? $time1 > $time2 : $day1 > $day2;
	}


	/**********************************************************************************************
	 * public 接口
	 **********************************************************************************************/

	/**
	 * 签到状态: -1(不可签到) 0(签到失败) 1(签到成功) 2(可签到) 3(审核中)
	 */
	public function sign_statu($username)
	{
		if ( ! $label = $this->is_signable())
		{
			return -1;
		}
		$where = array(
			'username' => $username,
			'label' => $label
		);
		if ($this->db->where($where)
			->get('sign_application')
			->result_array())
		{
			return 3;
		}
		if ($results = $this->db->where($where)
			->get('sign_log')
			->result_array())
		{
			$log = $results[0];
			return $log['result'];
		}

		return 2;
	}

	public function count($day)
	{
		$temp = strtotime(date('Y-m-d', time()));
		$ret = 0;
		while ($day > 0)
		{
			$ret += sizeof($this->db->select(array('username'))
			->where('result', 1)
			->like('label', date('Y-m-d', $temp))
			->get('sign_log')
			->result_array());
			$temp -= 86400;
			$day -= 1;
		}
		return $ret;
	}

	public function count_user($day)
	{
		$users = array();
		$temp = strtotime(date('Y-m-d', time()));
		while ($day > 0)
		{
			$results = $this->db->select(array('username'))
			->where('result', 1)
			->like('label', date('Y-m-d', $temp))
			->get('sign_log')
			->result_array();
			foreach ($results as $log) 
			{
				if ( ! in_array($log['username'], $users))
				{
					array_push($users, $log['username']);
				}
			}
			$temp -= 86400;
			$day -= 1;
		}
		return sizeof($users);
	}


	public function dashboard($day)
	{
		//config
		$users = array();
		$labels = array(
			'morning' => '早上',
			'afternoon' => '下午',
			'evening' => '晚上'
			);
		$time = strtotime(date('Y-m-d', time()));
		$ret['count_sign'] = 0;
		$ret['count_user'] = 0;
		$ret['log'] =  array();
		foreach ($labels as $key => $label)
		{
			$ret[$key] = 0;
		}

		while ($day > 0)
		{
			//count
			$results = $this->db->select(array('username', 'date', 'label'))
				->where('result', 1)
				->like('label', date('Y-m-d', $time))
				->order_by('date', 'DESC')
				->get('sign_log')
				->result_array();
			$ret['count_sign'] += sizeof($results);
			foreach ($results as $log)
			{
				array_push($ret['log'], $log);				
			}

			foreach ($results as $log) 
			{
				if ( ! in_array($log['username'], $users))
				{
					array_push($users, $log['username']);
				}
			}

			foreach ($labels as $key => $label)
			{
				$ret[$key] += sizeof($this->db->select(array('username'))
					->where('result', 1)
					->like('label', date('Y-m-d', $time)." ".$label)
					->get('sign_log')
					->result_array());
			}
			$time -= 86400;
			$day -= 1;
		}

		$ret['count_user'] = sizeof($users);

		//get more info
		foreach ($ret['log'] as $key => $log)
		{
			$where = array('username' => $log['username']);
			$ret['log'][$key]['realname'] = $this->db->select('realname')
				->where($where)
				->get('user_base')
				->result_array()[0]['realname'];
		}
		return $ret;
	}

	public function history($username)
	{	
		//config
		$days = array();
		$ret['days'] = 0;
		$ret['morning'] = 0;
		$ret['afternoon'] = 0;
		$ret['evening'] = 0;
		$ret['last_sign'] = '2000-00-00 早上';

		//get data
		$where = array('username' => $username, 'result' => 1);
		if ($logs = $this->db->where($where)
			->get('sign_log')
			->result_array())
		{
			foreach ($logs as $log) 
			{
				$day = substr($log['label'], 0, 10);
				if ( ! in_array($day, $days))
				{
					$ret['days']++;
					array_push($days, $day);
				}
				$temp = substr($log['label'], 11);
				if ($this->later($log['label'], $ret['last_sign']))
				{
					$ret['last_sign'] = $log['label'];
				}
				if ($temp == '早上')
				{
					$ret['morning']++;
				}
				else if ($temp == '下午')
				{
					$ret['afternoon']++;
				}
				else if ($temp == '晚上') 
				{
					$ret['evening']++;
				}
			}
		}
		return $ret;
	}

	public function log_of_user($username)
	{
		//set page_size & page
		$page_size = 10;
		if ( ! $page = $this->input->get('page'))
		{
			$page = 1;
		}
		$ret['page'] = $page;
		$ret['page_size'] = $page_size;

		//get total & max_page
       	$where = array(
       		'username' => $username,
       		'result' => 1
       		);
       	$ret['total'] = $this->db->where($where)->count_all_results('sign_log');
		$ret['max_page'] = (int)(($ret['total'] - 1) / $ret['page_size']) + 1;

		//get logs
		$ret['logs'] = $this->db->limit($page_size, ($page - 1) * $page_size)
			->where($where)
			->order_by('date', 'DESC')
			->get('sign_log')
			->result_array();

		return $ret;
	}

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
		$username = $this->user->check_user($token);

		//check label
		if ( ! $label = $this->is_signable())
		{
			throw new Exception("当前非合法签到时间");
		}

		//check apply again
		$where = array(
			"username" => $username,
			"label" => $label
			);
		if ($this->db->where($where)
			->get('sign_application')
			->result_array())
		{
			throw new Exception("请勿重复提交 ".$label." 的签到申请，请联系签到负责人。");
		}

		//check sign again
		$where = array(
			'username' => $username,
			'label' => $label
			);
		if ($log = $this->db->where($where)
			->get('sign_log')
			->result_array())
		{
			$log = $log[0];
			if ($log['result'] == 1)
			{
				$msg = "通过";
			}
			else if ($log['result'] == 0)
			{
				$msg = "被无情拒绝";
			}
			else
			{
				$msg = "未知结果，请联系管理员。";
			}
			throw new Exception($label." 已经签到，结果为：".$msg);
		}


		//add to application list
		$data = array(
			"username" => $username,
			"label" => $label,
			"date" => date('Y-m-d H:i:s', time())
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
		$level_limit = 9;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);

		//application list
		$results = $this->db->order_by('date', 'DESC')
			->get('sign_application')
			->result_array();
		foreach ($results as $key => $application) 
		{
			$where = array('username' => $application['username']);
			$results[$key]['realname'] = $this->db->select('realname')
				->where($where)
				->get('user_base')
				->result_array()[0]['realname'];
		}
		return $results;
	}

	/**
	 * 处理签到申请
	 */
	public function handle_application($form)
	{
		//config
		$level_limit = 9;

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
		$data = $result[0];

		//just cancel application ?
		$data['result'] = $form['result'];
		$where = array('id' => $form['id']);
		$this->db->delete('sign_application', $where);
		if ($data['result'] == -1)
		{
			throw new Exception("已删除该申请", 1);
		}

		//handle
		$this->db->insert('sign_log', $data);
		if ($form['result'] == 1)
		{
			$this->update_visit($data);
			throw new Exception("已通过", 1);
		}
		if ($form['result'] == 0)
		{
			throw new Exception("已拒绝", 1);
		}
		throw new Exception("处理结果只能为 0 or 1", 0);
		
	}

	/**
	 * 一键通过所有签到申请
	 */
	public function pass_all()
	{
		//config
		$level_limit = 10;

		//check token
		$token = get_token();
		$this->load->model('User_model', 'user');
		$username = $this->user->check_user($token, $level_limit);

		//get all id
		$applications = $this->db->get('sign_application')
			->result_array();
		if ($applications)
		{
			foreach ($applications as $application) 
			{
				$application['result'] = 1;
				$this->db->insert('sign_log', $application);
				$this->update_visit($application);
				$where = array('id' => $application['id']);
				$this->db->delete('sign_application', $where);
			}
		}

		//finish
		throw new Exception("全部通过", 1);
		
	}

	/**
	 * 仪表盘
	 */
	/*public function dashboard()
	{
		//configs
		$ret = array();

		//count today
		$result = $this->db->select(array('username', 'date', 'label'))
			->where('result', 1)
			->like('label', date('Y-m-d', time()))
			->order_by('date', 'DESC')
			->get('sign_log')
			->result_array();
		$ret['today'] = sizeof($result);
		$temp = array(
			'morning' => '早上',
			'afternoon' => '下午',
			'evening' => '晚上'
			);
		foreach ($temp as $key => $value)
		{
			$ret[$key] = sizeof($this->db->select(array('username'))
				->where('result', 1)
				->like('label', date('Y-m-d', time())." ".$value)
				->get('sign_log')
				->result_array());			
		}
		$ret['today_log'] = $result;
		$ret['3day'] = $this->count(3);
		$ret['7day'] = $this->count(7);


		//config
		$fields = array(
			'id', 
			'oj',
			'link',
			'name',
			'start_time',
			'week',
			'access'
			);

		if ( ! $result = $this->db->get('todaycontest_lastfresh')
			->result_array())
		{
			$lastfresh = '1990-01-01 00:00:00';
		}
		else
		{
			$lastfresh = $result[0]['lastfresh'];
		}

		//缓存
		if (time() - strtotime($lastfresh) >= 6 * 60 * 60)
		{
			$url = "http://contests.acmicpc.info/contests.json";
			$content = file_get_contents($url); 
			$data = (array)json_decode($content);
			$this->db->empty_table('todaycontest_base');	
			foreach ($data as $contest) 
			{
				if (strtotime(substr($contest->start_time, 0, 10)) <= strtotime(date('Y-m-d', time())) + 86400 * 2)
				{
					$this->db->insert('todaycontest_base',filter((array)$contest, $fields));
				}
			}
			$this->db->empty_table('todaycontest_lastfresh');
			$temp = array('lastfresh' => date("y-m-d H:i:s"));
			$this->db->insert('todaycontest_lastfresh', $temp);
		}
		
		$data = $this->db->get('todaycontest_base')
			->result_array();

		//return
		$ret['todaycontest'] = $data;
		return $ret;
	}*/

}
