<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\includes;

use GuzzleHttp\Client;

class mailrelay
{
	/** @var \GuzzleHttp\Client */
	protected $client;

	/** @var string */
	private $api_url;

	/** @var string */
	private $api_key;

	/**
	 * Set hostname.
	 *
	 * @param string $hostname
	 *
	 * @throws \InvalidArgumentException if provided hostname is empty.
	 *
	 * @return void
	 */
	public function set_hostname($hostname = '')
	{
		if (empty($hostname))
		{
			throw new \InvalidArgumentException('Mailrelay API hostname cannot be empty.');
		}

		// Allowed domains
		$allowed = [
			'ipzmarketing.com',
			'ip-zone.com'
		];

		// Domain regexp
		$regexp = '#\.(?:' . implode('|', array_map('preg_quote', $allowed)) . ')$#';

		// Add domain if missing
		if (!preg_match($regexp, $hostname))
		{
			$hostname = vsprintf('%1$s.%2$s', [
				trim($hostname, '.'),
				$allowed[0]
			]);
		}

		$this->api_url = sprintf('https://%s/ccm/admin/api/version/2/&type=json', $hostname);
	}

	/**
	 * Set API key.
	 *
	 * @param string $api_key
	 *
	 * @throws \InvalidArgumentException if provided API key is empty.
	 *
	 * @return void
	 */
	public function set_api_key($api_key = '')
	{
		if (empty($api_key))
		{
			throw new \InvalidArgumentException('Mailrelay API key cannot be empty.');
		}

		$this->api_key = $api_key;
	}

	/**
	 * Send request to Mailrelay,
	 *
	 * @param array $data
	 *
	 * @throws \RuntimeException			if API URL or the 'function' or 'apiKey' paramaters are empty.
	 * @throws \InvalidArgumentException	if provided post data is empty.
	 * @throws \ErrorException				if the API request could not be processed.
	 *
	 * @return mixed
	 */
	public function send_request($data = [])
	{
		// API URL can't be empty
		if (empty($this->api_url))
		{
			throw new \RuntimeException('Mailrelay API URL cannot be empty.');
		}

		// Post data can't be empty
		if (empty($data))
		{
			throw new \InvalidArgumentException('Mailrelay post data cannot be empty.');
		}

		// Add API key to post data
		if (!empty($this->api_key))
		{
			$data['apiKey'] = $this->api_key;
		}

		// Minimum required parameters
		$required = ['function', 'apiKey'];

		// Check if required data is present
		if (empty($data['function']) || empty($data['apiKey']))
		{
			throw new \RuntimeException(
				sprintf(
					'Mailrelay API required parameters are missing: %s.',
					implode(', ', $required)
				)
			);
		}

		// Lazy load Guzzle client
		if (!isset($this->client))
		{
			$this->client = new Client;
		}

		/** @var \Guzzle\Http\Message\Response */
		$response = $this->client->request('POST', $this->api_url, [
			'form_params' => $data
		]);

		// JSON string
		$json = $response->getBody()->getContents();

		// JSON object
		$result = json_decode($json);

		// Look for errors
		if ($result->status === 0)
		{
			throw new \ErrorException($result->error);
		}

		// Return API data
		return $result->data;
	}
}
