<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
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
	'ACP_MAILRELAY_EXPLAIN' => '<p>Here you can configure the Mailrelay API and its behavior. Consult the <a href="https://alfredoramos.mx/mailrelay-extension-for-phpbb" rel="external nofollow noreferrer noopener" target="_blank"><strong>FAQ</strong></a> for more information. If you require assistance, please visit the <a href="https://alfredoramos.mx/mailrelay-extension-for-phpbb#support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Support</strong></a> section.</p>',

	'ACP_MAILRELAY_API_ACCOUNT' => 'Acount name',
	'ACP_MAILRELAY_API_ACCOUNT_EXPLAIN' => 'The account you use to login to Mailrelay. Normally this is only the main part of your domain name.',

	'ACP_MAILRELAY_API_TOKEN' => 'API token',
	'ACP_MAILRELAY_API_TOKEN_EXPLAIN' => 'Any of the API keys you have generated in Mailrelay <samp>Settings</samp> > <samp>API keys</samp>.',

	'ACP_MAILRELAY_GROUP_ID' => 'Group ID',
	'ACP_MAILRELAY_GROUP_ID_EXPLAIN' => 'The ID of the Mailrelay group where all the emails will be synced to. It must exist in Mailrelay <samp>Subscribers</samp> > <samp>Groups</samp>.',

	'ACP_MAILRELAY_SYNC_PACKET_SIZE' => 'Sync packet size',
	'ACP_MAILRELAY_SYNC_PACKET_SIZE_EXPLAIN' => 'Maximum number of user emails to be processed when synced. A high number could lead to a degradation of the board performance, by using excessive server resources.',

	'ACP_MAILRELAY_SYNC_FREQUENCY' => 'Sync frequency',
	'ACP_MAILRELAY_SYNC_FREQUENCY_EXPLAIN' => 'Time between sync events.',

	'ACP_MAILRELAY_HOUR' => 'hour',
	'ACP_MAILRELAY_DAY' => 'day',
	'ACP_MAILRELAY_WEEK' => 'week',
	'ACP_MAILRELAY_MONTH' => 'month',

	'ACP_MAILRELAY_VALIDATE_INVALID_FIELDS' => 'Invalid values for fields: <samp>%s</samp>',
	'ACP_MAILRELAY_VALIDATE_INVALID_API_DATA' => 'Invalid Mailrelay API data: %s',

	'ACP_MAILRELAY_ERROR_INVALID_API_KEY' => 'The API key was not sent or is invalid.',
	'ACP_MAILRELAY_ERROR_ACCOUNT_NOT_FOUND' => 'Account not found.',
	'ACP_MAILRELAY_ERROR_INTERNAL_ERROR' => 'An internal error happened. Try again later.',
	'ACP_MAILRELAY_ERROR_UNKNOWN' => 'Unknown error.'
]);
