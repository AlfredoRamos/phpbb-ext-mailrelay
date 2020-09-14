<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\includes;

use phpbb\language\language;

class helper
{
	/** @var \phpbb\language\language */
	protected $language;

	/**
	 * Helper constructor.
	 *
	 * @return void
	 */
	public function __construct(language $language)
	{
		$this->language = $language;
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
}
