<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\acp;

class main_module
{
	/** @var string */
	public $u_action;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \alfredoramos\mailrelay\controller\acp */
	protected $acp_controller;

	/**
	 * ACP module constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		global $phpbb_container;

		$this->template = $phpbb_container->get('template');
		$this->language = $phpbb_container->get('language');
		$this->acp_controller = $phpbb_container->get('alfredoramos.mailrelay.controller.acp');
	}

	/**
	 * Main module method.
	 *
	 * @param string $id
	 * @param string $mode
	 *
	 * @return void
	 */
	public function main($id, $mode)
	{
		// Load translations
		$this->language->add_lang('acp/settings', 'alfredoramos/mailrelay');

		// Form token
		add_form_key('alfredoramos_mailrelay');

		switch ($mode)
		{
			case 'settings':
				$this->tpl_name = 'acp_mailrelay_settings';
				$this->page_title = sprintf(
					'%s - %s',
					$this->language->lang('SETTINGS'),
					$this->language->lang('ACP_MAILRELAY')
				);
				$this->acp_controller->settings_mode($this->u_action);
			break;

			default:
				trigger_error(
					$this->language->lang('NO_MODE') .
					adm_back_link($this->u_action),
					E_USER_WARNING
				);
			break;
		}

		// Assign global template variable
		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
			'IN_MAILRELAY_SETTINGS' => true
		]);
	}
}
