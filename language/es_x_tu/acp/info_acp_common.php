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
	'ACP_MAILRELAY' => 'Mailrelay',
	'LOG_MAILRELAY_DATA' => '<strong>Mailrelay data changed</strong><br>» %s',
	'LOG_MAILRELAY_USER_SYNC' => '<strong>Mailrelay user sync</strong><br>» %d users were processed',
	'LOG_MAILRELAY_USER_SYNC_ERROR' => '<strong>Mailrelay user sync failed</strong><br>» %s'
]);
