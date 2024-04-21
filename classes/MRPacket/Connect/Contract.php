<?php

namespace MRPacket\Connect;

use MRPacket\Basic\MyCurl;
use MRPacket\Basic\Configloader;
use MRPacket\CrException;
use InvalidArgumentException;

/**
 * @author	   MRPacket
 * @copyright  (c) 2024 MRPacket
 * @license    all rights reserved
 */
class Contract extends Call
{
	protected $token = null;

	public function __construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion, $token)
	{
		parent::__construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion);

		if (empty($token)) {
			throw new InvalidArgumentException("Param 'token' must not be empty. Please call \Connect\Authentication to obtain auth token!");
		} else {
			$this->token = $token;
		}
	}

	public function create(ContractPacket $entry)
	{
		$curl = new MyCurl();

		$status = array(
			'success' 	=> false,
			'data' 		=> null,
			'errors'	=> []
		);

		$input = [];
		$prefix = 'receiver_';
		foreach ($entry->receiver as $fieldname => $value) {
			$input[$prefix . '' . $fieldname] = $value;
		}

		$prefix = 'shipper_';
		foreach ($entry->shipper as $fieldname => $value) {
			$input[$prefix . '' . $fieldname] = $value;
		}

		foreach ($entry->packet as $fieldname => $value) {
			$input[$fieldname] = $value;
		}

		if (empty($input['meta'])) {
			$status['success']	= 0;
			$status['errors'][]	= "Invalid value for required field 'meta'. Field must not be empty.";
			return $status;
		}

		$requestJSON = http_build_query($input);
		if (!$requestJSON) {
			throw new CrException("Failed to encode JSON request: " . $this->getJSONLastError());
		}

		$endpoint = Configloader::load('settings', 'mrpacket_server_domain');
		if (!$endpoint) {
			throw new CrException("Failed to load endpoint via Configloader.");
		}

		$endpoint .= '/api/packet';
		$header 			= $this->buildHttpDefaultHeaders($this->token);
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
		if ($httpcode != 201) {
			throw new CrException("Unexpected status. Server responded with http code $httpcode.", $httpcode);
		}

		$result = json_decode($response['body'], true);
		if (!$result) {
			throw new CrException("Failed to decode JSON response: " . $this->getJSONLastError(), $httpcode);
		}

		$status['success']  = true;
		$status['data']		= $result;
		if (!isset($result['id'])) {
			$status['success']  = false;
			$status['errors'][]	= "Missing field 'id' in server response.";
		}

		return $status;
	}

	public function delete($packetId)
	{
		$curl = new MyCurl();
		$status = array(
			'success' 	=> false,
			'data' 		=> null,
			'errors'	=> array()
		);

		$endpoint = Configloader::load('settings', 'mrpacket_server_domain');
		if (!$endpoint) {
			throw new CrException("Failed to load endpoint via Configloader.");
		}

		$endpoint .= '/api/packet/' . $packetId;
		$header 			= $this->buildHttpDefaultHeaders($this->token);
		$userAgent			= 'Connect b' . $this->build . ' ' . $this->shopFrameWorkName;

		if (ENVIRONMENT == 'DEV') {
			$request = array(
				'header' 		=> implode("\n", $header),
				'body' 			=> '',
				'last_url'		=> $endpoint
			);
			echo "REQUEST: (curl)<pre>" . var_export($request, true) . "</pre>\n";
		}

		$response = $curl->sendCurlRequest($endpoint, null, $header, 'DELETE', $userAgent);

		if (ENVIRONMENT == 'DEV') {
			echo "RESPONSE: (curl)<pre>" . var_export($response, true) . "</pre>";
		}

		$httpcode = $response['http_code'];
		if ($httpcode != 204) {
			throw new CrException("Unexpected status. Server responded with http code $httpcode.", $httpcode);
		}

		if ($result = json_decode($response['body'], true)) {
			$status['success']  = true;
			$status['data']		= $result;
		} else {
			throw new CrException("Failed to decode JSON response: " . $this->getJSONLastError(), $httpcode);
		}

		return $status;
	}
}
