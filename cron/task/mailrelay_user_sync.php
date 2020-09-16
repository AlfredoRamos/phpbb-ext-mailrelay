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
		return (
			!empty($this->config['mailrelay_hostname']) &&
			!empty($this->config['mailrelay_api_key'])
		);
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

		if (empty($users) || empty($this->config['mailrelay_hostname']) || empty($this->config['mailrelay_api_key']))
		{
			return;
		}

		// Setup Mailrelay API
		$this->mailrelay->set_hostname($this->config['mailrelay_hostname']);
		$this->mailrelay->set_api_key($this->config['mailrelay_api_key']);
		$group_id = abs((int) $this->config['mailrelay_group_id']);
		$group_id = ($group_id === 0) ? 1 : $group_id;
		$subscriber_id = -1;

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
				$subscriber_id = $this->mailrelay->send_request([
					'function' => 'addSubscriber',
					'email' => $value['email'],
					'name' => $value['name'],
					'groups' => [$group_id]
				]);
			}
			catch (\Exception $ex)
			{
				// TODO: add error log
			}
		}

		// No users were added
		if ($subscriber_id <= 0)
		{
			return;
		}

		// Get last user ID
		$last_user_id = array_pop($users);
		$last_user_id = (int) $last_user_id['id'];

		// Update last sync
		if (!empty($last_user_id))
		{
			$this->config->set('mailrelay_last_sync', time());
			$this->config->set('mailrelay_last_user_sync', $last_user_id);
		}
	}
}
