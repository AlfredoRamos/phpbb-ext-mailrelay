<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * @ignore
 */
if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACP_MAILRELAY_EXPLAIN' => '<p>Here you can configure the Mailrelay API and its behavior. Consult the <a href="https://www.phpbb.com/customise/db/extension/mailrelay/faq" rel="external nofollow noreferrer noopener" target="_blank"><strong>FAQ</strong></a> for more information. If you require assistance, please visit the <a href="https://www.phpbb.com/customise/db/extension/mailrelat/support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Support</strong></a> section.</p>',

	'ACP_MAILRELAY_HOSTNAME' => 'Hostname',
	'ACP_MAILRELAY_HOSTNAME_EXPLAIN' => 'The hostname assigned to you by Mailrelay, or any of the ones you have configured in Mailrelay <samp>Settings</samp> > <samp>Tracking and bounce domains</samp>.',

	'ACP_MAILRELAY_API_KEY' => 'API key',
	'ACP_MAILRELAY_API_KEY_EXPLAIN' => 'Any of the API keys you have generated in Mailrelay <samp>Settings</samp> > <samp>API keys</samp>.',

	'ACP_MAILRELAY_AUTO_SYNC' => 'Automatic syncronization',
	'ACP_MAILRELAY_AUTO_SYNC_EXPLAIN' => 'Syncronize user emails to the Mailrelay <samp>Subscribers</samp> list using a cron task. Banned and innactive users are excluded by default.',

	'ACP_MAILRELAY_SYNC_PACKET_SIZE' => 'Syncronization packet size',
	'ACP_MAILRELAY_SYNC_PACKET_SIZE_EXPLAIN' => 'Maximum number of user emails to be processed when synced. A high number could lead to a degradation of the board performance, by using excesive server resources.',

	'ACP_MAILRELAY_VALIDATE_INVALID_FIELDS' => 'Invalid values for fields: <samp>%s</samp>',
]);
