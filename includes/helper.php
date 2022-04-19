<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\includes;

use phpbb\db\driver\factory as database;
use phpbb\config\config;
use phpbb\language\language;

class helper
{
	/** @var database */
	protected $db;

	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var array */
	protected $tables;

	/**
	 * Helper constructor.
	 *
	 * @param database	$db
	 * @param config	$config
	 * @param language	$language
	 * @param string	$users_table
	 * @param string	$groups_table
	 * @param string	$banlist_table
	 *
	 * @return void
	 */
	public function __construct(database $db, config $config, language $language, $users_table, $groups_table, $banlist_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->language = $language;

		if (empty($this->tables))
		{
			$this->tables['users'] = $users_table;
			$this->tables['groups'] = $groups_table;
			$this->tables['banlist'] = $banlist_table;
		}
	}

	/**
	 * Get users list to sync.
	 *
	 * The following users are excluded:
	 *
	 *   - Bots
	 *   - Users with empty emails
	 *   - Banned users
	 *   - Users that disable mass emails
	 *
	 * @return array
	 */
	public function get_users()
	{
		$last_user_id = abs((int) $this->config['mailrelay_last_user_sync']);
		$limit = abs((int) $this->config['mailrelay_sync_packet_size']);
		$limit = ($limit === 0 || $limit > 99999) ? 150 : $limit;
		$seconds = (24 * 60 * 60);

		// Exclude bots
		$sql_where = 'u.user_type <> ' . USER_IGNORE;

		// Exclude users with empty email
		$sql_where .= " AND u.user_email <> ''";

		// Exclude users that do not want to receive mass emails
		$sql_where .= ' AND u.user_allow_massemail = 1';

		// Exclude banned users
		$sql_where .= ' AND u.user_id NOT IN (
			SELECT b.ban_userid
			FROM ' . $this->tables['banlist'] . ' AS b
			WHERE b.ban_userid = u.user_id
		)';

		// Exclude users already processed
		if (!empty($last_user_id))
		{
			$sql_where .= ' AND u.user_id > ' . $last_user_id;
		}

		$sql = 'SELECT
				u.user_id AS id,
				u.username AS name,
				u.user_email AS email,
				g.group_name AS group
			FROM ' . $this->tables['users'] . ' AS u
			INNER JOIN ' . $this->tables['groups'] . ' AS g
				ON g.group_id = u.group_id
			WHERE ' . $sql_where . '
			ORDER BY u.user_id ASC';

		$result = $this->db->sql_query_limit($sql, $limit, 0, $seconds);
		$users = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $users;
	}

	/**
	 * Validate form fields with given filters.
	 *
	 * @param array $fields		Pair of field name and value
	 * @param array $filters	Filters that will be passed to filter_var_array()
	 * @param array $errors		Array of message errors
	 *
	 * @return bool
	 */
	public function validate(&$fields = [], &$filters = [], &$errors = [])
	{
		if (empty($fields) || empty($filters))
		{
			return false;
		}

		// Filter fields
		$data = filter_var_array($fields, $filters, false);

		// Invalid fields helper
		$invalid = [];

		// Validate fields
		foreach ($data as $key => $value)
		{
			// Remove and generate error if field did not pass validation
			// Not using empty() because an empty string can be a valid value
			if (!isset($value) || $value === false)
			{
				$invalid[] = $this->language->lang(
					sprintf('ACP_%s', strtoupper($key))
				);
				unset($fields[$key]);
				continue;
			}
		}

		if (!empty($invalid))
		{
			$errors[]['message'] = $this->language->lang(
				'ACP_MAILRELAY_VALIDATE_INVALID_FIELDS',
				implode(', ', $invalid)
			);
		}

		// Validation check
		return empty($errors);
	}

	/**
	 * Remove empty items from an array, recursively.
	 *
	 * @param array		$data
	 * @param integer	$depth
	 *
	 * @return array
	 */
	public function filter_empty_items($data = [], $depth = 0)
	{
		if (empty($data))
		{
			return [];
		}

		$max_depth = 5;
		$depth = abs($depth) + 1;

		// Do not go deeper, return data as is
		if ($depth > $max_depth)
		{
			return $data;
		}

		// Remove empty elements
		foreach ($data as $key => $value)
		{
			if (empty($value))
			{
				unset($data[$key]);
			}

			if (is_array($value) && !empty($value))
			{
				$data[$key] = $this->filter_empty_items($data[$key], $depth);
			}
		}

		// Return a copy
		return $data;
	}

	/**
	 * Parse frequency string.
	 *
	 * It only parses the format: [+N S].
	 *
	 * N being a number, and S being a string ('hour', 'day', 'week', 'month').
	 *
	 * @param string $frequency
	 *
	 * @return array [integer, string]
	 */
	public function parse_frequency($frequency = '')
	{
		if (empty($frequency))
		{
			return [];
		}

		$allowed = $this->allowed_values('frequencies');

		$frequency = explode(' ', $frequency);
		$this->filter_empty_items($frequency);

		// First element is a number
		// Fallback to 1
		$frequency[0] = abs((int) str_replace('+', '', $frequency[0]));
		$frequency[0] = ($frequency[0] < 1) ? 1 : $frequency[0];
		$frequency[0] = ($frequency[0] > 99999) ? 99999 : $frequency[0];

		// Second element is a string
		$frequency[1] = trim($frequency[1]);
		$frequency[1] = !in_array($frequency[1], $allowed) ? $allowed[1] : $frequency[1];

		return $frequency;
	}

	/**
	 * Allowed values for settings.
	 *
	 * @param string $kind
	 *
	 * @return array
	 */
	public function allowed_values($kind = '')
	{
		// Allowed values
		$allowed = [
			'frequencies' => ['hour', 'day', 'week', 'month']
		];

		// Value casting
		$kind = trim($kind);

		// Get specific kind
		if (!empty($kind) && !empty($allowed[$kind]))
		{
			return $allowed[$kind];
		}

		// Return whole array
		return $allowed;
	}
}
