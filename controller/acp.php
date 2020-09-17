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
use alfredoramos\mailrelay\includes\mailrelay;

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

	/** @var \alfredoramos\mailrelay\includes\mailrelay */
	protected $mailrelay;

	/**
	 * Controller constructor.
	 *
	 * @param \phpbb\config\config							$config
	 * @param \phpbb\template\template						$template
	 * @param \phpbb\request\request						$request
	 * @param \phpbb\language\language						$language
	 * @param \phpbb\user									$user
	 * @param \phpbb\log\log								$log
	 * @param \alfredoramos\mailrelay\includes\helper		$helper
	 * @param \alfredoramos\mailrelay\includes\mailrelay	$mailrelay
	 *
	 * @return void
	 */
	public function __construct(config $config, template $template, request $request, language $language, user $user, log $log, helper $helper, mailrelay $mailrelay)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->language = $language;
		$this->user = $user;
		$this->log = $log;
		$this->helper = $helper;
		$this->mailrelay = $mailrelay;
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

		// Allowed values
		$allowed = $this->helper->allowed_values();

		// Validation errors
		$errors = [];

		// Field filters
		$filters = [
			'mailrelay_hostname' => [
				'filter' => FILTER_VALIDATE_DOMAIN,
				'flags' => FILTER_FLAG_HOSTNAME
			],
			'mailrelay_domain' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^(?:' . implode('|', array_map('preg_quote', $allowed['domains'])) . ')$#'
				]
			],
			'mailrelay_api_key' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^\w{40}$#'
				]
			],
			'mailrelay_group_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 1,
					'max_range' => 99999
				]
			],
			'mailrelay_sync_packet_size' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 0,
					'max_range' => 99999
				]
			],
			'mailrelay_sync_frequency_number' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 1,
					'max_range' => 99999
				]
			],
			'mailrelay_sync_frequency_type' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '#^(?:' . implode('|', array_map('preg_quote', $allowed['frequencies'])) . ')$#'
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
				'mailrelay_domain' => $this->request->variable('mailrelay_domain', 'ipzmarketing.com'),
				'mailrelay_api_key' => $this->request->variable('mailrelay_api_key', ''),
				'mailrelay_group_id' => $this->request->variable('mailrelay_group_id', 1),
				'mailrelay_sync_packet_size' => $this->request->variable('mailrelay_sync_packet_size', 150),
				'mailrelay_sync_frequency_number' => $this->request->variable('mailrelay_sync_frequency_number', 1),
				'mailrelay_sync_frequency_type' => $this->request->variable('mailrelay_sync_frequency_type', 'day')
			];

			// Validate Mailrelay domain
			if (!empty($fields['mailrelay_hostname']))
			{
				// Hostname must not include Mailrelay domain
				$fields['mailrelay_hostname'] = str_replace($allowed['domains'], '', $fields['mailrelay_hostname']);

				// Hostname can't start or end with a dot
				$fields['mailrelay_hostname'] = trim($fields['mailrelay_hostname'], '.');
			}

			// Validation check
			if ($this->helper->validate($fields, $filters, $errors))
			{
				$fields['mailrelay_sync_frequency'] = vsprintf('+%1$d %2$s', [
					$fields['mailrelay_sync_frequency_number'],
					$fields['mailrelay_sync_frequency_type']
				]);

				unset(
					$fields['mailrelay_sync_frequency_number'],
					$fields['mailrelay_sync_frequency_type']
				);

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
			'MAILRELAY_GROUP_ID'	=> (int) $this->config['mailrelay_group_id'],
			'MAILRELAY_SYNC_PACKET_SIZE'	=> (int) $this->config['mailrelay_sync_packet_size']
		]);

		// Sync frequency helper
		$sync_frequency = $this->helper->parse_frequency($this->config['mailrelay_sync_frequency']);
		$this->template->assign_block_vars('MAILRELAY_SYNC_INTERVAL', [
			'NUMBER' => $sync_frequency[0],
			'TYPE' => $sync_frequency[1]
		]);

		// Assign allowed Mailrelay domains
		foreach ($allowed['domains'] as $domain)
		{
			$this->template->assign_block_vars('MAILRELAY_DOMAINS', [
				'KEY' => $domain,
				'ENABLED' => ($domain === $this->config['mailrelay_domain'])
			]);
		}

		// Assign allowed frequency values
		foreach ($allowed['frequencies'] as $type)
		{
			$this->template->assign_block_vars('MAILRELAY_SYNC_FREQUENCY_TYPES', [
				'KEY' => $type,
				'NAME' => $this->language->lang(sprintf('ACP_MAILRELAY_%s', strtoupper($type))),
				'ENABLED' => ($sync_frequency[1] === $type)
			]);
		}

		// Assign validation errors
		foreach ($errors as $error)
		{
			$this->template->assign_block_vars('VALIDATION_ERRORS', [
				'MESSAGE' => $error['message']
			]);
		}
	}
}
