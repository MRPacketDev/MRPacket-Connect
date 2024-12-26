<?php

/**
 * Project settings. 
 *
 * @author	   MRPacket
 * @copyright  (c) 2024 MRPacket
 * @license    all rights reserved
 */
return array(
	'build'						=> '1.0', //Don't touch! (will be used internally for User Agent setting in calls)
	'mrpacket_server_domain'	=> ENVIRONMENT == 'DEV' || ENVIRONMENT == 'PREVIEW' ? 'https://mrpacket-preview.prowect.com' : 'https://www.mrpacket.de'
);
