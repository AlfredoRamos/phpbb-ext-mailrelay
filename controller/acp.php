<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\controller;

use phpbb\config\config;
use phpbb\template\template;
use phpbb\request\request;
use phpbb\language\language;
use phpbb\user;
use phpbb\log\log;
use alfredoramos\mailrelay\includes\helper;

class acp
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \alfredoramos\mailrelay\includes\helper */
	protected $helper;

	/**
	 * Controller constructor.
	 *
	 * @param \phpbb\config\config						$config
	 * @param \phpbb\template\template					$template
	 * @param \phpbb\request\request					$request
	 * @param \phpbb\language\language					$language
	 * @param \phpbb\user								$user
	 * @param \phpbb\log\log							$log
	 * @param \alfredoramos\mailrelay\includes\helper	$helper
	 *
	 * @return void
	 */
	public function __construct(config $config, template $template, request $request, language $language, user $user, log $log, helper $helper)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->language = $language;
		$this->user = $user;
		$this->log = $log;
		$this->helper = $helper;
	}

	/**
	 * Settings mode page.
	 *
	 * @param string $u_action
	 *
	 * @return void
	 */
	public function settings_mode($u_action = '')
	{
		if (empty($u_action))
		{
			return;
		}

		// Validation errors
		$errors = [];

		// Field filters
		$filters = [
			'mailrelay_hostname' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^\w+\.(?:ipzmarketing\.com|ip\-zone\.com)$#'
				]
			],
			'mailrelay_api_key' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^\w{40}$#'
				]
			],
			'mailrelay_auto_sync' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 0,
					'max_range' => 1
				]
			],
			'mailrelay_sync_packet_size' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 0,
					'max_range' => 99999
				]
			]
		];

		// Request form data
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('alfredoramos_mailrelay'))
			{
				trigger_error(
					$this->language->lang('FORM_INVALID') .
					adm_back_link($u_action),
					E_USER_WARNING
				);
			}

			// Form data
			$fields = [
				'mailrelay_hostname' => $this->request->variable('mailrelay_hostname', ''),
				'mailrelay_api_key' => $this->request->variable('mailrelay_api_key', ''),
				'mailrelay_auto_sync' => $this->request->variable('mailrelay_auto_sync', 1),
				'mailrelay_sync_packet_size' => $this->request->variable('mailrelay_sync_packet_size', 150)
			];

			// Validation check
			if ($this->helper->validate($fields, $filters, $errors))
			{
				// Save configuration
				foreach ($fields as $key => $value)
				{
					$this->config->set($key, $value);
				}

				// Admin log
				$this->log->add(
					'admin',
					$this->user->data['user_id'],
					$this->user->ip,
					'LOG_MAILRELAY_DATA',
					false,
					[$this->language->lang('SETTINGS')]
				);

				// Confirm dialog
				trigger_error(
					$this->language->lang('CONFIG_UPDATED') .
					adm_back_link($u_action)
				);
			}
		}

		// Assign template variables
		$this->template->assign_vars([
			'MAILRELAY_HOSTNAME'	=> $this->config['mailrelay_hostname'],
			'MAILRELAY_API_KEY'		=> $this->config['mailrelay_api_key'],
			'MAILRELAY_AUTO_SYNC'	=> ((int) $this->config['mailrelay_auto_sync'] === 1)
		]);

		// Assign validation errors
		foreach ($errors as $error)
		{
			$this->template->assign_block_vars('VALIDATION_ERRORS', [
				'MESSAGE' => $error['message']
			]);
		}
	}
}
