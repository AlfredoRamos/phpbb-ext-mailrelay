<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\acp;

class main_info
{
	/**
	 * Setup ACP module.
	 *
	 * @return array
	 */
	public function module()
	{
		return [
			'filename'	=> '\alfredoramos\mailrelay\acp\main_module',
			'title'		=> 'ACP_MAILRELAY',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'SETTINGS',
					'auth'	=> 'ext_alfredoramos/mailrelay && acl_a_board',
					'cat'	=> ['ACP_MAILRELAY']
				]
			]
		];
	}
}
