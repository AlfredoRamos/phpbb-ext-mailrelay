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
	'ACP_MAILRELAY' => 'Mailrelay',
	'LOG_MAILRELAY_DATA' => '<strong>Datos de Mailrelay modificados</strong><br>» %s',
	'LOG_MAILRELAY_USER_SYNC' => '<strong>Sincronización de usuarios a Mailrelay</strong><br>» %d usuarios fueron procesados',
	'LOG_MAILRELAY_USER_SYNC_ERROR' => '<strong>Falló la sincronización de usuarios a Mailrelay</strong><br>» %s'
]);
