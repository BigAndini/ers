<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array (
    'dkim' => array (
        'params' => array(
            'd'    => 'inbaz.org', // domain
            'h'    => 'from:to:subject', // headers to sign
            's'    => 'jan2014', // domain key selector
        ),
        'private_key' => 'your private key' // add this in your local.php
    )
);
