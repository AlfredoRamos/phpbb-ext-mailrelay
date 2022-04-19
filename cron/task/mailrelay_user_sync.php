<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\cron\task;

use phpbb\cron\task\base as task_base;
use phpbb\config\config;
use phpbb\log\log;
use phpbb\user;
use alfredoramos\mailrelay\includes\helper;
use AlfredoRamos\Mailrelay\Client as Mailrelay;

class mailrelay_user_sync extends task_base
{
	/** @var config */
	protected $config;

	/** @var log */
	protected $log;

	/** @var user */
	protected $user;

	/** @var helper */
	protected $helper;

	/** @var Mailrelay */
	protected $mailrelay;

	/**
	 * Cron task constructor.
	 *
	 * @param config	$config
	 * @param log		$log
	 * @param user		$user
	 * @param helper	$helper
	 *
	 * @return void
	 */
	public function __construct(config $config, log $log, user $user, helper $helper)
	{
		$this->config = $config;
		$this->log = $log;
		$this->user = $user;
		$this->helper = $helper;
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
			'account' => $this->config['mailrelay_api_account'],
			'token' => $this->config['mailrelay_api_token']
		];

		return (!empty($api['account']) && !empty($api['token']));
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
			'account' => $this->config['mailrelay_api_account'],
			'token' => $this->config['mailrelay_api_token'],
			'group' => abs((int) $this->config['mailrelay_group_id'])
		];

		if (empty($users) || empty($api['account']) || empty($api['token']))
		{
			return;
		}

		// Setup Mailrelay API
		if (empty($this->mailrelay))
		{
			$this->mailrelay = new Mailrelay([
				'api_account' => $api['account'],
				'api_token' => $api['token']
			]);
		}

		$api['group'] = ($api['group'] === 0) ? 1 : $api['group'];

		// Filter users
		foreach ($users as $key => $value)
		{
			// Check if user already subscribed
			$user_exists = $this->mailrelay->api('subscribers')->list([
				'q' => [
					'banned' => true,
					'bounced' => true,
					'reported_spam' => true,
					'unsubscribed' => true,
					'email_eq' => $value['email']
				]
			]);

			// User exists
			if (!empty($user_exists))
			{
				unset($users[$key]);
				continue;
			}

			// Sync user
			try
			{
				$this->mailrelay->api('subscribers')->sync([
					'status' => 'active',
					'email' => $value['email'],
					'name' => $value['name'],
					'group_ids' => [$api['group']],
					'replace_groups' => true
				]);
			}
			catch (\InvalidArgumentException $ex)
			{
				// Add an entry in the error log
				$this->log->add(
					'critical',
					null,
					null,
					false,
					[$ex->getMessage()]
				);
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

		// Add an entry in the admin log
		if (!empty($users))
		{
			$this->log->add(
				'admin',
				$this->user->data['user_id'],
				$this->user->ip,
				'LOG_MAILRELAY_USER_SYNC',
				false,
				[count($users)]
			);
		}
	}
}
