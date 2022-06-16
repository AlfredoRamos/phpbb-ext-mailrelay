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
	'ACP_MAILRELAY_EXPLAIN' => '<p>Aquí puede configurar la API de Mailrelay y su comportamiento. Consulte las <a href="https://alfredoramos.mx/mailrelay-extension-for-phpbb" rel="external nofollow noreferrer noopener" target="_blank"><strong>Preguntas Frecuentes</strong></a> para obtener más información. Si requiere de ayuda, por favor visite la sección de <a href="https://alfredoramos.mx/mailrelay-extension-for-phpbb#support" rel="external nofollow noreferrer noopener" target="_blank"><strong>Soporte</strong></a>.</p>',

	'ACP_MAILRELAY_API_ACCOUNT' => 'Nombre de cuenta',
	'ACP_MAILRELAY_API_ACCOUNT_EXPLAIN' => 'El nombre de cuenta utilizado para iniciar sesión en Mailrelay. Normalmente es unicamente la parte principal de su nombre de dominio.',

	'ACP_MAILRELAY_API_TOKEN' => 'Clave API',
	'ACP_MAILRELAY_API_TOKEN_EXPLAIN' => 'Cualquiera de las claves API que ha generado en Mailrelay <samp>Configuración</samp> > <samp>Claves API</samp>.',

	'ACP_MAILRELAY_GROUP_ID' => 'ID del grupo',
	'ACP_MAILRELAY_GROUP_ID_EXPLAIN' => 'El ID del grupo donde se sincronizarán todos los correos electrónicos. Debe existir en Mailrelay <samp>Suscriptores</samp> > <samp>Grupos</samp>.',

	'ACP_MAILRELAY_SYNC_PACKET_SIZE' => 'Tamaño del paquete de sincronización',
	'ACP_MAILRELAY_SYNC_PACKET_SIZE_EXPLAIN' => 'Número máximo de correos electrónicos de usuario a procesar durante la sincronización. Un número elevado podría provocar una degradación del rendimiento del foro, mediante el uso excesivo de los recursos del servidor.',

	'ACP_MAILRELAY_SYNC_FREQUENCY' => 'Frecuencia de sincronización',
	'ACP_MAILRELAY_SYNC_FREQUENCY_EXPLAIN' => 'Tiempo entre los eventos de sincronización.',

	'ACP_MAILRELAY_HOUR' => 'hora',
	'ACP_MAILRELAY_DAY' => 'día',
	'ACP_MAILRELAY_WEEK' => 'semana',
	'ACP_MAILRELAY_MONTH' => 'mes',

	'ACP_MAILRELAY_VALIDATE_INVALID_FIELDS' => 'Valores inválidos para los campos: <samp>%s</samp>'
]);
