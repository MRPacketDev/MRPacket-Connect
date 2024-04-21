<?php

namespace MRPacket\Basic;

define("TIMEOUT_CURL_REGULAR", 5);

/**
 * Class for issuing calls via lib cURL.
 *
 * @author	   MRPacket
 * @copyright  (c) 2024 MRPacket
 * @license    all rights reserved
 */
class MyCurl
{
	/**
	 * @param String $endpoint				URL of the resource to be called.
	 * @param String $payload				The data to be transmitted.
	 * @param Array $header					List of headers to be encompassed in the request.
	 * @param String $method			 	(optional)
	 * @param Bool $skipBody				Only check for the response header. (optional)
	 * @param String $userAgent				User agent to expose to the remote server. (optional)
	 * @param Bool $verfiySSLPeer			Do verify peer. (optional)
	 * @param Bool $verfiySSLHost			Do verify host. (optional)
	 * @return array
	 */
	public function sendCurlRequest($endpoint, $payload, $header, $method = null, $userAgent = null)
	{
		$result = array(
			'header' 		=> '',
			'body' 			=> '',
			'curl_error' 	=> '',
			'http_code' 	=> '1000',
			'last_url'		 => ''
		);

		if (empty($endpoint)) {
			return $result;
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		if ($userAgent !== null) {
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		}

		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
		} else if ($method == 'DELETE') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}

		if (!empty($payload)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}

		if ($header != NULL) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, ENVIRONMENT == 'DEV' ? 0 : 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ENVIRONMENT == 'DEV' ? 0 : 2);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');

		$response = curl_exec($ch);
		$error = curl_error($ch);

		if ($error != "") {
			$result['curl_error'] = $error;
			return $result;
		}

		$header_size 			= strlen($response) - curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
		$result['header'] 		= substr($response, 0, $header_size);
		$result['body'] 		= substr($response, $header_size);
		$result['http_code'] 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$result['last_url'] 	= curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		return $result;
	}
}
