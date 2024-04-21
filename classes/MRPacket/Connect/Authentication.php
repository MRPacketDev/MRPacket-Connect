<?php

namespace MRPacket\Connect;

use MRPacket\Basic\MyCurl;
use MRPacket\Basic\Configloader;
use MRPacket\CrException;

/**
 * User authentication and token exchange class.
 *
 * @author	   MRPacket
 * @copyright  (c) 2024 MRPacket
 * @license    all rights reserved
 */
class Authentication extends Call
{
	protected $token = null;

	protected function refreshAuthToken($username, $password)
	{
		$curl = new MyCurl();
		$input = array(
			'email'	=> trim($username),
			'password'	=> trim($password)
		);

		$requestJSON = http_build_query($input);
		if (!$requestJSON) {
			throw new CrException("Failed to encode JSON request. Bad data.");
		}

		$endpoint = Configloader::load('settings', 'mrpacket_server_domain');
		if (!$endpoint) {
			throw new CrException("Failed to load endpoint via Configloader.");
		}

		$endpoint .= '/api/login';
		$header 			= $this->buildHttpDefaultHeaders();
		$userAgent			= 'Connect b' . $this->build . ' ' . $this->shopFrameWorkName;

		if (ENVIRONMENT == 'DEV') {
			$request = array(
				'header' 		=> implode("\n", $header),
				'body' 			=> $requestJSON,
				'last_url'		=> $endpoint
			);
			echo "REQUEST: (curl)<pre>" . var_export($request, true) . "</pre>\n";
		}

		$response = $curl->sendCurlRequest($endpoint, $requestJSON, $header, 'POST', $userAgent);

		if (ENVIRONMENT == 'DEV') {
			echo "RESPONSE: (curl)<pre>" . var_export($response, true) . "</pre>";
		}

		$httpcode = $response['http_code'];
		if ($httpcode != 200) {
			throw new CrException("Unexpected status. Server responded with http code $httpcode.", $httpcode);
		}

		$result = json_decode($response['body'], true);
		if (!$result) {
			throw new CrException("Failed to decode JSON response: " . $this->getJSONLastError(), $httpcode);
		}

		$tokenUpdated = false;
		if (isset($result['token'])) {
			if (!empty($result['token'])) {
				$this->token = $result['token'];
				$tokenUpdated = true;
			}
		}

		if (!$tokenUpdated) {
			throw new CrException("Failed to update token. Empty or corrupted data.", $httpcode);
		}

		return $this->token;
	}

	public function getAuthToken($username, $password)
	{
		if ($this->token === null) {
			return $this->refreshAuthToken($username, $password);
		} else {
			return $this->token;
		}
	}
}
