<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\migrations\v10x;

use phpbb\db\migration\migration;

class m01_modules extends migration
{
	/**
	 * Add Mailrelay ACP settings.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			[
				'module.add',
				[
					'acp',
					'ACP_CAT_DOT_MODS',
					'ACP_MAILRELAY'
				]
			],
			[
				'module.add',
				[
					'acp',
					'ACP_MAILRELAY',
					[
						'module_basename' => '\alfredoramos\mailrelay\acp\main_module',
						'modes' => ['settings']
					]
				]
			]
		];
	}
}
