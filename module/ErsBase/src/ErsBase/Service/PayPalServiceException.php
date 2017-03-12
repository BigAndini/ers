<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use PayPal\Exception\PayPalConnectionException;

class PayPalServiceException extends \Exception {

    public function __construct($message, PayPalConnectionException $ex = NULL) {
        $json = ($ex ? json_decode($ex->getData(), true) : NULL);
        if (!$json || !isset($json["name"], $json["message"]))
            parent::__construct($message, 0, $ex);
        else
            parent::__construct($message . " " . $json["name"] . " - " . $json["message"]);
    }

}
