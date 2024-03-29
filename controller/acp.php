<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
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
use AlfredoRamos\Mailrelay\Client as Mailrelay;

class acp
{
	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var request */
	protected $request;

	/** @var language */
	protected $language;

	/** @var user */
	protected $user;

	/** @var log */
	protected $log;

	/** @var helper */
	protected $helper;

	/** @var Mailrelay */
	protected $mailrelay;

	/**
	 * Controller constructor.
	 *
	 * @param config	$config
	 * @param template	$template
	 * @param request	$request
	 * @param language	$language
	 * @param user		$user
	 * @param log		$log
	 * @param helper	$helper
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

		// Allowed values
		$allowed = $this->helper->allowed_values();

		// Validation errors
		$errors = [];

		// Field filters
		$filters = [
			'mailrelay_api_account' => [
				'filter' => FILTER_VALIDATE_DOMAIN,
				'flags' => FILTER_FLAG_HOSTNAME
			],
			'mailrelay_api_token' => [
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
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
			}

			// Form data
			$fields = [
				'mailrelay_api_account' => $this->request->variable('mailrelay_api_account', ''),
				'mailrelay_api_token' => $this->request->variable('mailrelay_api_token', ''),
				'mailrelay_group_id' => $this->request->variable('mailrelay_group_id', 1),
				'mailrelay_sync_packet_size' => $this->request->variable('mailrelay_sync_packet_size', 150),
				'mailrelay_sync_frequency_number' => $this->request->variable('mailrelay_sync_frequency_number', 1),
				'mailrelay_sync_frequency_type' => $this->request->variable('mailrelay_sync_frequency_type', 'day')
			];

			// Validation check
			if ($this->helper->validate($fields, $filters, $errors))
			{
				// Setup Mailrelay API
				if (empty($this->mailrelay))
				{
					$this->mailrelay = new Mailrelay([
						'api_account' => $fields['mailrelay_api_account'],
						'api_token' => $fields['mailrelay_api_token']
					]);
				}

				// Validate API data
				try
				{
					$info = $this->mailrelay->api('ping')->info();
				}
				catch (\Exception $ex)
				{
					$info = ['status' => $ex->getCode()];
				}

				// Add API errors
				if ($info['status'] !== 204)
				{
					$err = 'UNKNOWN';

					switch ($info['status'])
					{
						case 401:
							$err = 'INVALID_API_KEY';
						break;

						case 404:
							$err = 'ACCOUNT_NOT_FOUND';
						break;

						case 500:
							$err = 'INTERNAL_ERROR';
						break;
					}

					$errors[]['message'] = $this->language->lang(
						'ACP_MAILRELAY_VALIDATE_INVALID_API_DATA',
						$this->language->lang(sprintf('ACP_MAILRELAY_ERROR_%s', strtoupper($err)))
					);
				}

				// API validation
				if ($info['status'] === 204)
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
					trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($u_action));
				}
			}
		}

		// Assign template variables
		$this->template->assign_vars([
			'MAILRELAY_API_ACCOUNT'	=> $this->config['mailrelay_api_account'],
			'MAILRELAY_API_TOKEN'	=> $this->config['mailrelay_api_token'],
			'MAILRELAY_GROUP_ID'	=> (int) $this->config['mailrelay_group_id'],
			'MAILRELAY_SYNC_PACKET_SIZE'	=> (int) $this->config['mailrelay_sync_packet_size']
		]);

		// Sync frequency helper
		$sync_frequency = $this->helper->parse_frequency($this->config['mailrelay_sync_frequency']);
		$this->template->assign_block_vars('MAILRELAY_SYNC_INTERVAL', [
			'NUMBER' => $sync_frequency[0],
			'TYPE' => $sync_frequency[1]
		]);

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
