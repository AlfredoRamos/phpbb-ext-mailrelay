<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\migrations\v10x;

use phpbb\db\migration\migration;

class m00_configuration extends migration
{
	/**
	 * Add Mailrelay configuration.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			[
				'config.add',
				['mailrelay_api_account', '']
			],
			[
				'config.add',
				['mailrelay_api_token', '']
			],
			[
				'config.add',
				['mailrelay_group_id', 1]
			],
			[
				'config.add',
				['mailrelay_sync_packet_size', 150]
			],
			[
				'config.add',
				['mailrelay_sync_frequency', '+1 hour']
			],
			[
				'config.add',
				['mailrelay_last_sync', 0]
			],
			[
				'config.add',
				['mailrelay_last_user_sync', 0]
			]
		];
	}
}
