<?php

namespace MRPacket\Connect;

/**
 * @author	   MRPacket
 * @copyright  (c) 2024 MRPacket
 * @license    all rights reserved
 */
class ContractPacket
{
    public $receiver = array(
        'company'        => null,
        'firstname'    => null,
        'lastname'        => null,
        'street'        => null,
        'street_number'    => null,
        'zip'    => null,
        'city'            => null,
        'phone_nr'         => null,
        'email'            => null,
        'country'        => null,
    );

    public $shipper = array(
        'street'        => null,
        'street_number' => null,
        'zip'    => null,
        'city'    => null,
        'country' => null
    );

    public $packet = array(
        'length'    => null,
        'width'        => null,
        'height'    => null,
        'weight'    => null,
        'meta' => null
    );
}
