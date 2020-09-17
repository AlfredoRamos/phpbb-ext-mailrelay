<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\cron\task;

use phpbb\cron\task\base as task_base;

class mailrelay_user_sync extends task_base
{
	protected $config;
	protected $language;
	protected $helper;
	protected $mailrelay;

	/**
	 * Cron task constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		global $phpbb_container;
		$this->config = $phpbb_container->get('config');
		$this->language = $phpbb_container->get('language');
		$this->helper = $phpbb_container->get('alfredoramos.mailrelay.helper');
		$this->mailrelay = $phpbb_container->get('alfredoramos.mailrelay.mailrelay');
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		// Get API data
		$api = [
			'hostname' => $this->helper->get_hostname(),
			'key' => $this->config['mailrelay_api_key']
		];

		return (!empty($api['hostname']) && !empty($api['key']));
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		$last_sync = (int) $this->config['mailrelay_last_sync'];
		$sync_frequency = trim($this->config['mailrelay_sync_frequency']);
		$sync_frequency = empty($sync_frequency) ? '+1 day' : $sync_frequency;
		$next_sync = strtotime($sync_frequency, $last_sync);

		return ($next_sync <= time());
	}

	/**
	 * Execute the cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		// Get list of users
		$users = $this->helper->get_users();

		// Get API data
		$api = [
			'hostname' => $this->helper->get_hostname(),
			'key' => $this->config['mailrelay_api_key'],
			'group' => abs((int) $this->config['mailrelay_group_id'])
		];

		if (empty($users) || empty($api['hostname']) || empty($api['key']))
		{
			return;
		}

		// Setup Mailrelay API
		$this->mailrelay->set_hostname($api['hostname']);
		$this->mailrelay->set_api_key($api['key']);
		$api['group'] = ($api['group'] === 0) ? 1 : $api['group'];

		// Filter users
		foreach ($users as $key => $value)
		{
			// Check if user already subscribed
			$user_exists = $this->mailrelay->send_request([
				'function' => 'getSubscribers',
				'email' => $value['email']
			]);

			// User exists not exist
			if (!empty($user_exists))
			{
				unset($users[$key]);
				continue;
			}

			try
			{
				$this->mailrelay->send_request([
					'function' => 'addSubscriber',
					'email' => $value['email'],
					'name' => $value['name'],
					'groups' => [$api['group']]
				]);
			}
			catch (\Exception $ex)
			{
				// TODO: add error log
			}
		}

		// Update last sync
		$this->config->set('mailrelay_last_sync', time());

		// Get last user ID
		$last_user_id = array_pop($users);
		$last_user_id = (int) $last_user_id['id'];

		// Update last user ID
		if (!empty($last_user_id))
		{
			$this->config->set('mailrelay_last_user_sync', $last_user_id);
		}
	}
}
